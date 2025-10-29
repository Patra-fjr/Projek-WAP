<?php
// Detektif Error PHP (Hanya untuk Development!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Pastikan path ke config.php benar dan file tersebut mendefinisikan koneksi $conn
require_once '../config/config.php'; 

header('Content-Type: application/json');

// Inisialisasi respons default
$response = ['success' => false, 'message' => 'Terjadi kesalahan tidak diketahui.'];


// --- 1. Validasi Keamanan Dasar & Input ---
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart']) || !$input || !isset($_SESSION['nomor_meja'])) {
    $response['message'] = 'Request tidak valid (metode POST diperlukan), keranjang kosong, atau nomor meja belum diset.';
    echo json_encode($response);
    exit();
}


// --- 2. Siapkan dan Validasi Data Utama ---
$nama_customer = $input['name'] ?? '';

// PERBAIKAN KRITIS #1: Gunakan trim() untuk menghilangkan spasi tersembunyi pada ID Meja (VARCHAR)
$id_meja = isset($_SESSION['nomor_meja']) ? trim($_SESSION['nomor_meja']) : ''; 

$keranjang = $_SESSION['cart'];

// DEBUG: Log data penting sebelum transaksi
error_log("DEBUG FINAL - ID Meja (string): '" . $id_meja . "'");
error_log("DEBUG FINAL - Panjang ID Meja: " . strlen($id_meja));
error_log("DEBUG FINAL - Keranjang: " . print_r($keranjang, true));


if (empty($nama_customer)) {
    $response['message'] = 'Nama customer tidak boleh kosong.';
    echo json_encode($response);
    exit();
}
if (empty($id_meja)) {
    $response['message'] = 'ID Meja tidak ditemukan di session.';
    echo json_encode($response);
    exit();
}

// Hitung Ulang Total Harga di Server
$total_harga = 0;
foreach ($keranjang as $item) {
    $price = isset($item['price']) ? (float)$item['price'] : 0.0;
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
    $total_harga += $quantity * $price;
}

// PERBAIKAN KRITIS #2: Gunakan uniqid() untuk membuat ID Order yang unik hingga mikrodetik
$id_order_baru = 'ORD' . uniqid('', true); 
$tanggal_sekarang = date("Y-m-d");
$waktu_sekarang = date("H:i:s");
$status_order = 'proses'; 


// --- 3. Proses Transaksi Database ---
// Mengaktifkan laporan error untuk transaksi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Mulai Transaksi untuk menjamin atomisitas
mysqli_begin_transaction($conn);

try {
    // 3a. INSERT ke Tabel `orders` (Menggunakan Prepared Statement)
    $sql_order = "INSERT INTO `orders` (id_order, id_meja, nama_customer, tanggal_order, waktu_order, total_harga, status_order)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql_order);
    
    // Binding: s=string, d=double/float
    mysqli_stmt_bind_param($stmt, 'ssssssd', 
        $id_order_baru, 
        $id_meja, 
        $nama_customer, 
        $tanggal_sekarang, 
        $waktu_sekarang, 
        $total_harga, 
        $status_order
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menyimpan order utama: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    error_log("Order utama berhasil disimpan. ID Order Baru: " . $id_order_baru);


    // 3b. INSERT ke Tabel `detail_orders`
    foreach ($keranjang as $item) {
        $id_menu = $item['id'] ?? null;
        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
        $harga_saat_pesan = isset($item['price']) ? (float)$item['price'] : 0.0;
        $subtotal = $quantity * $harga_saat_pesan;
        
        // Perbaikan ID Detail Order (gunakan uniqid)
        $id_detail_baru = 'DTL' . uniqid('', true); 
        
        if (empty($id_menu) || $quantity <= 0) {
            error_log("ERROR - Item keranjang tidak valid: " . print_r($item, true)); 
            throw new Exception("Data item keranjang tidak valid atau kosong.");
        }
        
        // Catatan: Jika Anda mengubah id_detailorder menjadi AUTO_INCREMENT (INT), 
        // HAPUS '$id_detail_baru,' dan HAPUS '$id_detail_baru' dari query
        $sql_detail = "INSERT INTO detail_orders (id_order, id_menu, quantity, subtotal) 
                       VALUES ('$id_order_baru', '$id_menu', $quantity, $subtotal)";

        if (!mysqli_query($conn, $sql_detail)) {
            $db_error = mysqli_error($conn);
            error_log("Gagal detail order. Query: $sql_detail | Error: " . $db_error); 
            // Pesan error diubah agar bisa membantu deteksi Foreign Key Menu
            throw new Exception("Gagal menyimpan detail pesanan. (Detail: $db_error)");
        }
        error_log("BERHASIL menyimpan detail order $id_detail_baru."); 
    }

   $sql_update_meja = "UPDATE meja SET status_meja = 'tidak tersedia' WHERE id_meja = ?";
    $stmt_meja = mysqli_prepare($conn, $sql_update_meja);
    mysqli_stmt_bind_param($stmt_meja, 's', $id_meja);

    if (!mysqli_stmt_execute($stmt_meja)) {
        // Jika gagal update meja, seluruh transaksi gagal (rollback di catch block)
        $meja_error = mysqli_stmt_error($stmt_meja);
        mysqli_stmt_close($stmt_meja);
        throw new Exception("Gagal mengunci meja: " . $meja_error);
    }
    mysqli_stmt_close($stmt_meja);
    error_log("Meja ID $id_meja berhasil dikunci (status: tidak tersedia).");
    // 3c. COMMIT jika semua berhasil
    mysqli_commit($conn);
    
    // Bersihkan keranjang
    unset($_SESSION['cart']); 
    
    $response['success'] = true;
    $response['message'] = 'Pesanan berhasil disimpan!';
    error_log("SEMUA PROSES SIMPAN ORDER ID $id_order_baru BERHASIL."); 

} catch (Exception $e) {
    // 3d. ROLLBACK jika terjadi kesalahan di mana pun
    mysqli_rollback($conn);
    $response['message'] = "Pesanan gagal diproses. " . $e->getMessage();
    error_log("TRANSACTION FAILED: " . $e->getMessage());
}


// --- 4. Kirim Respons ---
echo json_encode($response);
exit(); 
?>