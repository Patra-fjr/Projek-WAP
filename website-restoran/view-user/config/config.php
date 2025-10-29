<?php
// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = ''; // Sesuaikan password MySQL Anda
$dbname = 'db_resto';

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");
