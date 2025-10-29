<?php
session_start();
// Gunakan koneksi dari config.php
require_once '../config/config.php'; 

// Ambil keranjang dari session kita ('cart')
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalPrice = 0;
$cartWithDetails = []; // Kita akan tetap gunakan ini untuk menampung detail

// Ambil detail produk dari database (jika diperlukan, atau bisa langsung dari session jika data lengkap)
// Kita coba ambil langsung dari session karena keranjang_aksi.php sudah menyimpan nama, harga, gambar
foreach ($cart as $index => $cartItem) {
    // Pastikan item memiliki data yang dibutuhkan
    if (isset($cartItem['id']) && isset($cartItem['name']) && isset($cartItem['price']) && isset($cartItem['quantity']) && isset($cartItem['gambar'])) {
        $cartWithDetails[] = [
            'index'    => $index, // Index array untuk proses hapus temanmu
            'id_menu'  => $cartItem['id'], // Gunakan id_menu sebagai identifier
            'name'     => $cartItem['name'],
            'price'    => (int)$cartItem['price'],
            'image'    => $cartItem['gambar'], // Nama file gambar
            'quantity' => $cartItem['quantity']
        ];
        $totalPrice += $cartItem['price'] * $cartItem['quantity'];
    }
}
// Hitung total dengan pajak (jika temanmu pakai)
$totalWithTax = $totalPrice * 1.1; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Quatre Restaurant</title>
    <link rel="stylesheet" href="../CSSUser/keranjang.css"> 
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Keranjang Belanja</h1>
            <a href="index.php" class="back-btn">← Kembali ke Menu</a>
        </div>

        <div class="cart-container">
            <?php if (empty($cartWithDetails)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon"><i class='bx bx-cart-alt bx-lg'></i></div>
                    <h2>Keranjang Belanja Kosong</h2>
                    <p>Silakan tambahkan menu favorit Anda.</p>
                    <a href="index.php" class="back-btn">Mulai Belanja</a>
                </div>
            <?php else: ?>
                <div class="cart-items" id="cart-items-dynamic"> <?php foreach ($cartWithDetails as $item): ?>
                        <div class="cart-item" data-index="<?php echo $item['index']; ?>" data-id-menu="<?php echo $item['id_menu']; ?>">
                            <img src="../assets/image/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                            
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                            </div>
                            <div class="item-controls">
                                <div class="qty-control">
                                    <button class="qty-btn qty-minus" data-index="<?php echo $item['index']; ?>" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>−</button>                                    <span class="qty-display"><?php echo $item['quantity']; ?></span>
                                    <button class="qty-btn qty-plus" data-index="<?php echo $item['index']; ?>">+</button>
                                </div>
                                <div class="subtotal">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></div>
                                <button class="remove-btn" data-index="<?php echo $item['index']; ?>">🗑️ Hapus</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="summarySubtotal">Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Pajak (10%):</span>
                        <span id="summaryTax">Rp <?php echo number_format($totalPrice * 0.1, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="summaryTotal">Rp <?php echo number_format($totalWithTax, 0, ',', '.'); ?></span>
                    </div>
                    <button class="checkout-btn" onclick="checkout()">Bayar Sekarang</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../JSUser/keranjang.js"></script> 
</body>
</html>