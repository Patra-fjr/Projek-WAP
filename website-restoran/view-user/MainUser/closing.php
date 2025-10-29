<?php
session_start();

// Clear cart setelah pembayaran
unset($_SESSION['cart']);
$_SESSION['cart'] = [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran di Kasir - Resto Italia</title>
    <link rel="stylesheet" href="../CSSUser/closing.css">
</head>
<body>
    <div class="container">
        <div class="icon">🍕🍝</div>
        <h1>Silahkan Lanjut Pembayaran di Kasir</h1>
        <p class="message">
            Pesanan Anda telah diterima.<br>
            Mohon menuju kasir untuk menyelesaikan pembayaran.
        </p>

        <div class="countdown-container">
            <div class="countdown-label">Kembali ke menu dalam</div>
            <div class="countdown" id="countdown">10</div>
        </div>

        <div class="loading-bar">
            <div class="loading-bar-fill"></div>
        </div>
    </div>

    <script src="../JSUser/closing.js"></script>
</body>
</html>