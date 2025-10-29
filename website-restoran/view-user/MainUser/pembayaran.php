<?php
session_start();
echo "DEBUG - Nomor Meja di Session: ";
var_dump($_SESSION['nomor_meja'] ?? 'TIDAK ADA');
require_once '../config/config.php'; // Gunakan koneksi kita

// Jika keranjang kosong atau nomor meja tidak ada, kembali ke menu
if (empty($_SESSION['cart']) || !isset($_SESSION['nomor_meja'])) {
    // Redirect ke index.php DENGAN parameter meja jika ada
    $redirect_url = isset($_SESSION['nomor_meja']) ? 'index.php?table=' . $_SESSION['nomor_meja'] : 'index.php';
    header('Location: ' . $redirect_url);
    exit();
}

// Ambil data keranjang & nomor meja dari session kita
$cart = $_SESSION['cart'];
$nomor_meja = $_SESSION['nomor_meja'];
$totalPrice = 0; // Hitung total dari data session kita

// Hitung total harga DARI SESSION (lebih aman & efisien)
foreach ($cart as $id_menu => $item) {
    if (isset($item['price']) && isset($item['quantity'])) {
        $totalPrice += (float)$item['price'] * (int)$item['quantity'];
    }
}

$totalWithTax = $totalPrice * 1.1; // Total + pajak 10%
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Quatre Restaurant</title> <link rel="stylesheet" href="../CSSUser/pembayaran.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'> </head>
<body>
    <div class="container">
        <div class="header">
             <a href="keranjang.php" class="back-arrow"><i class='bx bx-chevron-left'></i></a> 
            <h1>Pemesanan</h1>
            <div style="width: 24px;"></div>
        </div>

        <form class="form-section" id="paymentForm" action="simpan_order.php" method="POST">
            <h2 class="section-title">Informasi Pemesan</h2>
            
            <div class="form-group">
                <label class="form-label">Nama Lengkap<span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="text" id="fullName" name="nama_pemesan" placeholder="Nama Lengkap" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Ponsel (Opsional)</label>
                <div class="input-wrapper">
                    <input type="tel" id="phone" name="nomor_telepon" placeholder="Nomor Ponsel">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Meja</label>
                <div class="input-wrapper">
                    <input type="number" id="tableNumber" value="<?php echo $nomor_meja; ?>" readonly style="background-color: #eee;"> 
                    </div>
            </div>
             </form> 
    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="total-section">
                <div class="total-label"> Total Pembayaran </div>
                <div class="total-amount" id="totalAmount">Rp <?php echo number_format($totalWithTax, 0, ',', '.'); ?></div>
            </div>
            <button type="submit" form="paymentForm" class="pay-button">Bayar</button> 
        </div>
    </div>

     <script src="../JSUser/pembayaran.js"></script>

    </body>
</html>