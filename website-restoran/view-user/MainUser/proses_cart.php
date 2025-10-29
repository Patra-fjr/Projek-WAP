<?php
// "Detektif" Error PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Inisialisasi keranjang temanmu jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Menggunakan 'cart'
}

// Set header JSON
header('Content-Type: application/json');

// Ambil data JSON dari JavaScript kita
$data = json_decode(file_get_contents('php://input'), true);

// Pastikan ada data dan aksi
if (!$data || !isset($data['aksi'])) {
    // Kirim status keranjang saat ini jika tidak ada aksi (untuk sinkronisasi)
    if (!isset($data['aksi']) || $data['aksi'] != 'get_status'){
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid atau data kosong']);
        exit;
    }
}

$aksi = $data['aksi'] ?? 'get_status'; // Default ke get_status jika aksi kosong
// Gunakan isset untuk keamanan saat mengambil id_menu
$id_menu = isset($data['id_menu']) ? $data['id_menu'] : null;
// Cek flag force_delete dari JS
$force_delete = isset($data['force_delete']) && $data['force_delete'] === true;

// Proses Aksi
if ($aksi == 'tambah' && $id_menu) {
    // ... (Logika 'tambah' tetap sama) ...
    $found = false;
    foreach ($_SESSION['cart'] as $index => &$item) { 
        if (isset($item['id']) && $item['id'] == $id_menu) { 
            $item['quantity'] += isset($data['qty']) ? max(1, (int)$data['qty']) : 1; 
            $found = true;
            break;
        }
    }
    unset($item); 

    if (!$found) {
        if (isset($data['nama']) && isset($data['harga']) && isset($data['gambar'])) {
            $_SESSION['cart'][] = [ 
                'id'       => $id_menu, 
                'name'     => $data['nama'], 
                'price'    => (float)$data['harga'], 
                'quantity' => isset($data['qty']) ? max(1, (int)$data['qty']) : 1, 
                'gambar'   => $data['gambar'] 
            ];
        } else { exit(json_encode(['success' => false, 'message' => 'Data item tidak lengkap saat menambah'])); }
    }

} elseif ($aksi == 'kurang' && $id_menu) {
    $found_index = -1;
    foreach ($_SESSION['cart'] as $index => $item) {
         if (isset($item['id']) && $item['id'] == $id_menu) { 
            $found_index = $index;
            break;
        }
    }

    if ($found_index !== -1) {
        // ==========================================
        // PERBAIKAN LOGIKA UTAMA ADA DI SINI:
        // Cek $force_delete DULU
        // ==========================================
        $force_delete = isset($data['force_delete']) && $data['force_delete'] === true;

        if ($force_delete) {
            // Jika ada perintah paksa hapus (dari tombol Hapus), LANGSUNG HAPUS
            array_splice($_SESSION['cart'], $found_index, 1);
            $_SESSION['cart'] = array_values($_SESSION['cart']); 
        } elseif ($_SESSION['cart'][$found_index]['quantity'] > 1) {
            // Jika BUKAN paksa hapus, DAN kuantitas > 1 (dari tombol Minus), kurangi
            $_SESSION['cart'][$found_index]['quantity']--;
        } else {
             // Jika BUKAN paksa hapus, DAN kuantitas <= 1 (dari tombol Minus), hapus juga
             array_splice($_SESSION['cart'], $found_index, 1); 
             $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
} elseif ($aksi == 'get_status') {
    // Aksi untuk sinkronisasi, tidak melakukan apa-apa pada data
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal: ' . $aksi]); 
    exit;
}

// Hitung total items dan siapkan response keranjang
$totalItems = 0;
$totalHarga = 0; 
$responseKeranjang = []; 
foreach ($_SESSION['cart'] as $cartItem) { 
    if (isset($cartItem['quantity']) && isset($cartItem['price'])) { 
        $qty = (int)$cartItem['quantity'];
        $harga = (float)$cartItem['price']; 
        $totalItems += $qty;
        $totalHarga += $qty * $harga;
        // Buat response keranjang dengan ID menu sebagai key (agar JS lebih mudah update)
        $responseKeranjang[$cartItem['id']] = [ // Gunakan ID sebagai key
            'id'       => $cartItem['id'],
            'name'     => $cartItem['name'] ?? 'Nama Error', // Handle jika nama tidak ada
            'price'    => $harga, 
            'quantity' => $qty,
            'gambar'   => $cartItem['gambar'] ?? 'placeholder.jpg' 
        ];
    }
}

// Kirim response
echo json_encode([
    'success'     => true,
    'total_items' => $totalItems,
    'total_harga' => $totalHarga, 
    'keranjang'   => $responseKeranjang // Kirim detail keranjang dengan ID sbg key
]);
?>