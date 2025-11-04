<?php
// komen


// Detektif Error PHP (Hanya untuk Development!)
// Matikan ini di environment production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Pastikan path ini benar & file config menggunakan $conn
require_once '../config/config.php'; 

// Inisialisasi variabel
$tableNumber = null;
$allMenu = []; // Mulai sebagai array kosong
$meja_invalid = false; // Flag untuk notifikasi

// --- 1. Ambil & Validasi Nomor Meja ---
if (isset($_GET['table']) && !empty($_GET['table'])) {
    // Ambil dari URL: Sanitasi & Simpan sebagai STRING
    $tableNumber = htmlspecialchars(trim($_GET['table'])); // Tambahkan trim() di sini juga
    $_SESSION['nomor_meja'] = $tableNumber; 
} elseif (isset($_SESSION['nomor_meja'])) {
    // Ambil dari Session
    $tableNumber = $_SESSION['nomor_meja'];
}


// --- 2. Cek Status Meja di Database (Penting!) ---
if ($tableNumber !== null && $conn) {
    // Gunakan Prepared Statement untuk cek status keamanan
    $stmt_check = mysqli_prepare($conn, "SELECT status_meja FROM meja WHERE id_meja = ?");
    mysqli_stmt_bind_param($stmt_check, 's', $tableNumber);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $meja_data = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);

    // Cek apakah meja tidak ditemukan atau statusnya 'tidak tersedia'
    if (!$meja_data || $meja_data['status_meja'] === 'tidak tersedia') {
        $tableNumber = null; // Batalkan penggunaan $tableNumber
        unset($_SESSION['nomor_meja']); // Hapus dari session
        $meja_invalid = true; // Set flag error
    }
}


// --- 3. Query Menu (Hanya Jika Meja Valid atau Belum Diset) ---
// Query mengambil data menu (tetap dijalankan walaupun meja belum diset)
$query = "
    SELECT
        m.id_menu,
        m.nama_menu,
        m.harga,
        m.gambar,
        m.deskripsi,
        k.nama_kategori
    FROM menu m
    JOIN kategori k ON m.id_kategori = k.id_kategori
    WHERE m.status_menu = 'tersedia'
    ORDER BY k.nama_kategori, m.nama_menu ASC
";

if ($conn) {
    $result = $conn->query($query);

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                $gambar_nama = $row['gambar'] ?? null;
                $gambar_path = ($gambar_nama) ? '../assets/image/' . htmlspecialchars($gambar_nama) : '../assets/image/placeholder.jpg'; 
                
                $allMenu[] = [
                    'id'          => htmlspecialchars($row['id_menu'] ?? ''), 
                    'name'        => htmlspecialchars($row['nama_menu'] ?? 'Nama Error'), 
                    'price'       => $row['harga'] ?? 0, 
                    'image'       => $gambar_path, 
                    'description' => htmlspecialchars($row['deskripsi'] ?? ''), 
                    'category'    => htmlspecialchars($row['nama_kategori'] ?? 'Kategori Error')
                ];
            }
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>Error Query SQL: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>Error: Koneksi Database Gagal.</p>";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant</title>
    <link rel="stylesheet" href="../CSSUser/index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php include 'navbar.php'; // Jika temanmu punya navbar terpisah ?>

    <div class="container">
        
        <?php if ($meja_invalid): ?>
            <div class="alert alert-error">
                <i class='bx bxs-error-circle'></i> Maaf, meja **<?php echo htmlspecialchars($_GET['table'] ?? 'ini'); ?>** sedang ditempati atau tidak tersedia.
            </div>
        <?php elseif ($tableNumber === null): ?>
            <div class="alert alert-warning">
                <i class='bx bxs-info-circle'></i> Silakan scan QR code atau input nomor meja untuk memulai pemesanan.
            </div>
        <?php endif; ?>

        <?php if ($tableNumber !== null): ?>
        <div class="table-info-container">
            <div class="table-info-card">
                 <span class="table-icon"><i class='bx bx-chair bx-lg'></i></span>
                 <div class="table-details">
                    <span class="table-label">Nomor Meja</span>
                    <span class="table-number" id="tableNumber"><?php echo htmlspecialchars($tableNumber); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="filter-section">
            <div class="filter-buttons">
                <button class="filter-btn active" data-category="all">Semua Menu</button>
                <button class="filter-btn" data-category="makanan">Makanan</button>
                <button class="filter-btn" data-category="minuman">Minuman</button>
            </div>
        </div>

        <div class="menu-grid" id="menuGrid">
            <?php if (!empty($allMenu)): ?>
                <?php foreach ($allMenu as $menu): ?>
                    <div class="menu-card"
                         data-category="<?php echo isset($menu['category']) ? strtolower($menu['category']) : 'unknown'; ?>"
                         onclick="openDetail('<?php echo isset($menu['id']) ? $menu['id'] : ''; ?>')">

                        <img src="<?php echo isset($menu['image']) ? $menu['image'] : '../assets/image/placeholder.jpg'; ?>"
                             alt="<?php echo isset($menu['name']) ? htmlspecialchars($menu['name']) : 'Menu Item'; ?>"
                             class="menu-image">

                        <div class="menu-info">
                            <span class="menu-category"><?php echo isset($menu['category']) ? ucfirst($menu['category']) : 'N/A'; ?></span>
                            <h3 class="menu-name"><?php echo isset($menu['name']) ? htmlspecialchars($menu['name']) : 'Nama Menu'; ?></h3>
                            <p class="menu-desc"><?php echo isset($menu['description']) ? htmlspecialchars($menu['description']) : ''; ?></p>
                            <div class="menu-price">Rp <?php echo isset($menu['price']) ? number_format($menu['price'], 0, ',', '.') : '0'; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center;">Menu belum tersedia atau gagal dimuat.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="detailModal" class="modal">
       <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Menu</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <img id="detailImage" src="" alt="Detail Menu" class="detail-image">
                <h3 id="detailName" class="detail-name"></h3>
                <p id="detailDesc" class="detail-desc"></p>
                <div id="detailPrice" class="detail-price"></div>

                <div class="qty-section">
                    <span class="qty-label">Jumlah:</span>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="decreaseQty()">−</button>
                        <span id="qtyDisplay" class="qty-display">1</span>
                        <button class="qty-btn" onclick="increaseQty()">+</button>
                    </div>
                </div>

                <button id="addToCartBtn" class="add-cart-btn" onclick="addToCart()">🛒 Tambah ke Keranjang</button>
            </div>
        </div>
    </div>

    <script>
        // Kirim $allMenu ke JS
        const allMenuData = <?php echo json_encode($allMenu); ?>;
        const tableNumber = <?php echo json_encode($tableNumber); ?>;
    </script>
    <script src="../JSUser/index.js"></script>
</body>
</html>