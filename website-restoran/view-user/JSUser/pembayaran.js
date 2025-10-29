// Variabel global dari temanmu (jika masih diperlukan)
let orderType = 'dine-in';
// const totalPayment = ...; // totalPayment akan diambil dari server

// Fungsi toggle (tidak diubah)
// function toggleOrderType() { /* ... */ } // Bisa dihapus jika tidak dipakai

// Fungsi Proses Pembayaran (FINAL)
async function processPayment() { // Jadikan async
    const fullNameInput = document.getElementById('fullName');
    const phoneInput = document.getElementById('phone');
    const payButton = document.querySelector('.pay-button'); // Ambil tombolnya

    const fullName = fullNameInput ? fullNameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';

    // Validasi input
    if (!fullName) {
        alert('Mohon isi nama lengkap Anda');
        if(fullNameInput) fullNameInput.focus();
        return;
    }

    // Nonaktifkan tombol saat proses
    if(payButton) payButton.disabled = true;
    if(payButton) payButton.textContent = 'Memproses...';


    // Siapkan data untuk dikirim ke server (JSON)
    const orderData = {
        name: fullName, // Kirim sebagai 'name'
        phone: phone,   // Kirim 'phone'
        orderType: orderType // Kirim tipe order jika perlu
        // Nomor meja diambil dari session di PHP
        // Total harga dihitung ulang di PHP
    };

    console.log('Mengirim data order:', orderData); // DEBUG: Cek data yang mau dikirim

    try {
        // Kirim data ke simpan_order.php
        const response = await fetch('simpan_order.php', { // Targetkan file simpan
            method: 'POST',
            // PASTIKAN HEADER DAN BODY BENAR
            headers: {
                'Content-Type': 'application/json' // Kirim sebagai JSON
            },
            body: JSON.stringify(orderData) // Ubah objek JS menjadi string JSON
        });

        // DAPATKAN TEKS MENTAH DULU UNTUK DEBUGGING
        const responseText = await response.text();
        console.log("Response Teks Mentah dari Server:", responseText); // DEBUG

        if (!response.ok) {
            // Tangani error HTTP
            throw new Error(`Server error (${response.status}): ${responseText}`);
        }

        // Coba parse JSON response
        let result = {};
        try {
             result = JSON.parse(responseText); // Parse teks mentah
        } catch (jsonError) {
             console.error("Gagal parse JSON:", jsonError);
             throw new Error("Gagal membaca respons server (bukan JSON valid).");
        }


        if (result.success) {
            // Jika server bilang sukses, baru redirect ke closing.php
            console.log('Pesanan berhasil disimpan, redirecting...');
            window.location.href = 'closing.php';
        } else {
            // Jika server bilang gagal
            alert(`Gagal menyimpan pesanan: ${result.message}`);
             // Aktifkan tombol kembali jika gagal
             if(payButton) payButton.disabled = false;
             if(payButton) payButton.textContent = 'Bayar';
        }
    } catch (error) {
        console.error('Error saat processPayment:', error);
        alert('Terjadi kesalahan saat mengirim pesanan: ' + error.message);
         // Aktifkan tombol kembali jika error
         if(payButton) payButton.disabled = false;
         if(payButton) payButton.textContent = 'Bayar';
    }
}

// Event listener untuk tombol bayar (LEBIH BAIK DARI ONCLICK)
document.addEventListener('DOMContentLoaded', () => {
    const payButton = document.querySelector('.pay-button'); // Cari tombol berdasarkan class
    if (payButton) {
        payButton.addEventListener('click', processPayment);
    }
});