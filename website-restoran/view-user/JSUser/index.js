// Variabel global (dari temanmu)
let currentQty = 1;
let currentItemId = null; // Ini akan menyimpan id_menu

// ==========================================================
// BAGIAN BARU: Fungsi Komunikasi ke Backend Kita
// ==========================================================
async function updateKeranjangServer(data) {
    try {
        // Path dari JSUser/index.js ke MainUser/proses_cart.php
        const response = await fetch('proses_cart.php', { // Targetkan proses_cart.php
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
            const responseText = await response.text(); // Ambil teks asli jika JSON gagal
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

// ==========================================================
// BAGIAN BARU: Fungsi Update Badge Keranjang (ditaruh di sini agar bisa dipanggil)
// ==========================================================
function updateCartCount(count) {
    const cartBadge = document.querySelector('.cart-count'); // Selector dari navbar temanmu
    const cartIcon = document.querySelector('.nav-cart-btn'); // Selector dari navbar temanmu

    if (count > 0) {
        if (cartBadge) {
            cartBadge.textContent = count;
        } else if (cartIcon) {
            const badge = document.createElement('span');
            badge.className = 'cart-count'; // Class dari CSS temanmu
            badge.textContent = count;
            cartIcon.appendChild(badge);
        }
    } else {
        if (cartBadge) {
            cartBadge.remove();
        }
    }
}

// ==========================================================
// LOGIKA DARI index.js TEMANMU (Dipertahankan & Disesuaikan)
// ==========================================================
document.addEventListener('DOMContentLoaded', () => { // Bungkus semua dalam DOMContentLoaded

    // Filter menu (dari temanmu - tidak diubah)
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const category = this.getAttribute('data-category');
            filterMenu(category); // Panggil fungsi global
        });
    });

    // BAGIAN BARU: Sinkronisasi keranjang saat halaman load
    async function sinkronkanKeranjangSaatMuat() {
        console.log("Sinkronisasi keranjang...");
        const response = await updateKeranjangServer({ aksi: 'get_status' }); // Ambil status awal
        if(response) {
            updateCartCount(response.total_items); // Update badge di awal
        }
    }
    sinkronkanKeranjangSaatMuat();

}); // Akhir dari DOMContentLoaded


// ==========================================================
// FUNGSI GLOBAL DARI index.js TEMANMU (Diletakkan di luar DOMContentLoaded)
// ==========================================================

// Fungsi filterMenu (dari temanmu - dijadikan global)
window.filterMenu = function(category) {
    const menuCards = document.querySelectorAll('.menu-card');
    menuCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        const displayStyle = (category === 'all' || cardCategory === category) ? 'block' : 'none';
        card.style.display = displayStyle;
    });
}

// Fungsi Modal (dari temanmu - dijadikan global & disesuaikan)
window.openDetail = function(id_menu) { // Terima id_menu
    currentItemId = id_menu; // Simpan id_menu
    currentQty = 1;

    // Gunakan allMenuData dari PHP (yang sudah diadaptasi)
    const item = allMenuData.find(m => m.id == id_menu);
    if (!item) {
        console.error("Item tidak ditemukan di allMenuData:", id_menu);
        return;
    }

    // Tampilkan gambar dengan tag <img>
    const detailImageContainer = document.getElementById('detailImage');
    if (detailImageContainer) {
      detailImageContainer.innerHTML =
        `<img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">`;
    }

    const detailNameEl = document.getElementById('detailName');
    if(detailNameEl) detailNameEl.textContent = item.name;

    const detailDescEl = document.getElementById('detailDesc');
    // if (detailDescEl) detailDescEl.textContent = item.description || ''; // Deskripsi sudah dihapus

    const detailPriceEl = document.getElementById('detailPrice');
    if(detailPriceEl) detailPriceEl.textContent = 'Rp ' + (parseFloat(item.price) || 0).toLocaleString('id-ID');

    const qtyDisplayEl = document.getElementById('qtyDisplay');
    if(qtyDisplayEl) qtyDisplayEl.textContent = currentQty;

    const detailModalEl = document.getElementById('detailModal');
    if(detailModalEl) detailModalEl.style.display = 'block';

    document.body.style.overflow = 'hidden';
}

window.closeModal = function() { // Jadikan global
    const detailModalEl = document.getElementById('detailModal');
     if(detailModalEl) detailModalEl.style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.decreaseQty = function() { // Jadikan global
    if (currentQty > 1) {
        currentQty--;
        const qtyDisplayEl = document.getElementById('qtyDisplay');
         if(qtyDisplayEl) qtyDisplayEl.textContent = currentQty;
    }
}

window.increaseQty = function() { // Jadikan global
    currentQty++;
    const qtyDisplayEl = document.getElementById('qtyDisplay');
    if(qtyDisplayEl) qtyDisplayEl.textContent = currentQty;
}

// --- FUNGSI Add to Cart (INTEGRASI UTAMA) ---
window.addToCart = async function() { // Jadikan global
    // Gunakan allMenuData dari PHP
    const item = allMenuData.find(m => m.id == currentItemId);
    if (!item) return;

    const btn = document.getElementById('addToCartBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Menambahkan...';

    const gambarFilename = item.image.split('/').pop();

    try {
        // Panggil proses_cart.php kita dengan data JSON
        const response = await updateKeranjangServer({
            aksi: 'tambah',    // Aksi untuk PHP kita
            id_menu: item.id,  // Kirim id_menu (dari key 'id')
            nama: item.name,   // Kirim nama
            harga: parseFloat(item.price) || 0, // Kirim harga sebagai angka
            gambar: gambarFilename, // Kirim NAMA FILE gambar
            qty: currentQty    // Kirim kuantitas dari modal
        });

        if (response && response.success) { // Cek flag 'success' dari PHP kita
            updateCartCount(response.total_items); // Update badge keranjang
            closeModal(); // Tutup modal
            showNotification(item.name, currentQty); // Notifikasi temanmu
        } else {
            alert(`Gagal menambahkan: ${response ? response.message : 'Server error'}`);
        }
    } catch (error) {
        console.error('Error saat addToCart:', error);
        alert('Terjadi kesalahan saat menambahkan');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🛒 Tambah ke Keranjang';
    }
}

// Notifikasi (dari temanmu - dijadikan global)
window.showNotification = function(itemName, qty) { // Jadikan global
    const notification = document.createElement('div');
    notification.className = 'toast-notification';
    // Pastikan class toast-notification ada di CSS temanmu
    notification.style.animation = 'slideInRight 0.5s ease forwards';

    notification.innerHTML =
        `<span style="font-size: 24px;">✓</span>
         <div>
             <div style="font-weight: 700; margin-bottom: 5px;">${itemName}</div>
             <div style="font-size: 14px; opacity: 0.8;">${qty} item ditambahkan</div>
         </div>`;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease forwards';
        setTimeout(() => notification.remove(), 500);
    }, 2500);
}

// Close modal event listeners (dari temanmu)
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('detailModal');
        if (modal && modal.style.display === 'block') {
           closeModal();
        }
    }
});
window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    if (modal && event.target == modal) {
        closeModal();
    }
}