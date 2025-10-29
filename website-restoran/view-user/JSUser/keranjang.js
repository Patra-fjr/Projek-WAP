// ==========================================================
// KODE UNTUK HALAMAN keranjang.php (TERINTEGRASI - FINAL v4 - Fokus Stabilitas)
// ==========================================================

// --- Fungsi Global (Didefinisikan di dalam DOMContentLoaded untuk lingkup aman) ---
document.addEventListener('DOMContentLoaded', function() {
    const cartItemsContainer = document.querySelector('.cart-items'); // Kontainer item temanmu
    const summaryContainer = document.querySelector('.cart-summary'); // Kontainer summary temanmu
    const cartContainerElement = document.querySelector('.cart-container'); // Container utama

    // Keluar jika bukan halaman keranjang
    if (!cartItemsContainer) {
        // console.log("Bukan halaman keranjang, script keranjang.js berhenti.");
        return;
    }

    console.log("Menjalankan script keranjang.js (vFinal 4)");

    // Fungsi komunikasi ke backend (proses_cart.php)
    async function updateKeranjangServerJS_Keranjang(data) {
        try {
            // Path dari JSUser/keranjang.js ke MainUser/proses_cart.php
            // Sesuaikan jika struktur folder berbeda
            const response = await fetch('proses_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                console.error("Server response not OK:", response.status, response.statusText);
                const errorText = await response.text();
                console.error("Server error details:", errorText);
                alert(`Terjadi ${response.status} error dari server.`);
                return null;
            }
             try {
                return await response.json();
            } catch (jsonError) {
                console.error("Gagal parse JSON:", jsonError);
                const responseText = await response.text();
                console.error("Response Teks Mentah:", responseText);
                alert("Gagal membaca respons dari server.");
                return null;
            }
        } catch (error) {
            console.error('Error saat menghubungi server:', error);
            alert('Tidak dapat menghubungi server.');
            return null;
        }
    }

    // Fungsi notifikasi (dari temanmu)
    function showNotification_Keranjang(message) {
         const notification = document.createElement('div');
         notification.className = 'toast-notification';
         // Pastikan class toast-notification ada di CSS keranjang.css
         notification.style.animation = 'slideInRight 0.5s ease forwards'; // Pastikan animasi ada
         notification.innerHTML = `<span style="font-size: 24px;">✓</span><div><div style="font-weight: 700;">${message}</div></div>`;
         document.body.appendChild(notification);
         setTimeout(() => {
             notification.style.animation = 'slideOutRight 0.5s ease forwards';
             setTimeout(() => notification.remove(), 500);
         }, 2000);
    }

    // Fungsi update tampilan keranjang (Versi Hapus & Gambar Ulang)
    function updateTampilanKeranjangJS(dataFromServer) {
        if (!dataFromServer || dataFromServer.keranjang === undefined) {
            console.error("Data server tidak valid:", dataFromServer);
            return;
        }
        const { total_items, keranjang } = dataFromServer;

        // Cek elemen penting lagi di dalam fungsi
        const currentCartItemsContainer = document.querySelector('.cart-items');
        const currentSummaryContainer = document.querySelector('.cart-summary');
        const currentCartContainerElement = document.querySelector('.cart-container');

        if (!currentCartItemsContainer || !currentSummaryContainer || !currentCartContainerElement) {
            console.error("Elemen HTML keranjang/summary tidak ditemukan saat update.");
            return;
        }

        // --- Penanganan Keranjang Kosong ---
        if (total_items === 0) {
            currentCartContainerElement.innerHTML = `
                <div class="empty-cart">
                     <div class="empty-cart-icon">🛒</div>
                     <h2>Keranjang Anda Kosong</h2>
                     <p>Silakan tambahkan menu favorit Anda.</p>
                     <a href="index.php" class="back-btn">Kembali ke Menu</a>
                 </div>`;
            const footerElement = document.querySelector('.cart-popup-footer'); // Cari footer pembayaran
             if (footerElement) footerElement.style.display = 'none';
            return;
        } else {
             currentSummaryContainer.style.display = 'block';
             const footerElement = document.querySelector('.cart-popup-footer');
             if (footerElement) footerElement.style.display = 'block';
        }

        // --- Hapus dan Gambar Ulang Item ---
        currentCartItemsContainer.innerHTML = ''; // Kosongkan tampilan item
        let calculatedTotalPrice = 0;

        Object.values(keranjang).forEach((itemData, index) => {
             if (!itemData || !itemData.id /* ... cek data lain ... */) {
                console.warn("Melewati item dengan data tidak lengkap:", itemData);
                return;
            }

            const id_menu = itemData.id;
            const qty = parseInt(itemData.quantity) || 0;
            const harga = parseFloat(itemData.price) || 0;
            const subtotal = qty * harga;
            calculatedTotalPrice += subtotal;

            const itemHTML = `
                <div class="cart-item" data-index="${index}" data-id-menu="${id_menu}">
                    <img src="../assets/image/${itemData.gambar}" alt="${itemData.name}" class="item-image">
                    <div class="item-info">
                        <div class="item-name">${itemData.name}</div>
                        <div class="item-price">Rp ${harga.toLocaleString('id-ID')}</div>
                    </div>
                    <div class="item-controls">
                        <div class="qty-control">
                            <button class="qty-btn qty-minus" data-index="${index}" ${qty <= 1 ? 'disabled' : ''}>−</button>
                            <span class="qty-display">${qty}</span>
                            <button class="qty-btn qty-plus" data-index="${index}">+</button>
                        </div>
                        <div class="subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
                        <button class="remove-btn" data-index="${index}">🗑️ Hapus</button>
                    </div>
                </div>
            `;
            currentCartItemsContainer.innerHTML += itemHTML;
        });

        // --- Update Total di Summary ---
        const tax = calculatedTotalPrice * 0.1;
        const totalWithTax = calculatedTotalPrice * 1.1;

        const subtotalSpan = currentSummaryContainer.querySelector('.summary-row:nth-of-type(1) span:last-of-type');
        const taxSpan = currentSummaryContainer.querySelector('.summary-row:nth-of-type(2) span:last-of-type');
        const totalSpan = currentSummaryContainer.querySelector('.summary-row.total span:last-of-type');
        const checkoutTotalStrong = document.getElementById('checkout-total-price'); // Untuk footer jika ada

        if (subtotalSpan) subtotalSpan.textContent = `Rp ${calculatedTotalPrice.toLocaleString('id-ID')}`;
        if (taxSpan) taxSpan.textContent = `Rp ${tax.toLocaleString('id-ID')}`;
        if (totalSpan) totalSpan.textContent = `Rp ${totalWithTax.toLocaleString('id-ID')}`;
        if (checkoutTotalStrong) checkoutTotalStrong.textContent = `Rp ${totalWithTax.toLocaleString('id-ID')}`;
    }


    // --- Event Listener Utama ---
    cartItemsContainer.addEventListener('click', async (e) => {
        const target = e.target; // Tombol yang diklik
        const itemElement = target.closest('.cart-item'); // Cari elemen .cart-item terdekat

        // Keluar jika klik bukan di dalam item atau tidak punya id_menu
        if (!itemElement || !itemElement.dataset.idMenu) return;

        const id_menu = itemElement.dataset.idMenu;

        // Logika tombol +/-
        if (target.classList.contains('qty-btn')) {
             console.log("Tombol Qty Ditekan:", target.classList.contains('qty-plus') ? '+' : '-'); // DEBUG
            const action = target.classList.contains('qty-plus') ? 'tambah' : 'kurang';
            const response = await updateKeranjangServerJS_Keranjang({ aksi: action, id_menu: id_menu });
            if (response) {
                console.log("Respons update qty:", response); // DEBUG
                updateTampilanKeranjangJS(response);
            } else {
                 console.error("Gagal update qty, tidak ada respons.");
            }
        }

        // Logika tombol Hapus
        if (target.classList.contains('remove-btn')) {
             console.log("Tombol Hapus Ditekan"); // DEBUG
             if (!confirm('Apakah Anda yakin ingin menghapus item ini?')) return;

             const response = await updateKeranjangServerJS_Keranjang({
                 aksi: 'kurang',
                 id_menu: id_menu,
                 force_delete: true // Kirim sinyal hapus
             });

             if (response) {
                 console.log("Respons hapus:", response); // DEBUG
                 showNotification_Keranjang('Item berhasil dihapus');
                 updateTampilanKeranjangJS(response);
             } else {
                  console.error("Gagal hapus, tidak ada respons.");
                  alert('Gagal menghapus item: Tidak ada respons dari server.');
             }
        }
    });

}); // Akhir DOMContentLoaded

// Fungsi checkout (dari temanmu - global)
function checkout() {
    window.location.href = 'pembayaran.php';
}