    <?php
    // session_start();

    // Hitung total item di cart
    $cartCount = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['quantity'];
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $pageTitle ?? 'Quatre\'s Restaurant'; ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="../CSSUser/navbar.css">
        <?php if (isset($additionalCSS)): ?>
        <?php endif; ?>
    </head>
    <body>
        <!-- Banner Restaurant -->
        <?php if (!isset($hideBanner) || !$hideBanner): ?>
        <div class="banner-restaurant">
            <!-- Cart Icon -->
            <div class="cart-icon">
                <a href="keranjang.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Keranjang</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <img src="../assets/banner.png" alt="Quatre's Restaurant" class="banner-img">
            <div class="banner-overlay">
                <h2>Selamat Datang di</h2>
                <h1>Quatre's Restaurant</h1>
                <p>Nikmati cita rasa Italia autentik dengan pelayanan terbaik</p>
            </div>
        </div>
        <?php endif; ?>

    <script src="../JSUser/banner.js"></script>
    </body>
    </html>