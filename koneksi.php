<?php
// Tampilkan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Wajib ditaruh paling atas untuk sistem Login
session_start();

// Set zona waktu WIB
date_default_timezone_set('Asia/Jakarta'); 

$host = '127.0.0.1';
$db   = 'toko_kharisma';
$user = 'root';
$pass = '';
$port = '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Koneksi Database Gagal: " . $e->getMessage());
}

// Ambil Nama Toko dari pengaturan
$stmtToko = $pdo->query("SELECT nama_toko FROM pengaturan WHERE id = 1");
$tokoData = $stmtToko->fetch();
$NAMA_TOKO = $tokoData ? $tokoData['nama_toko'] : 'TOKO KHARISMA';

function formatRupiah(float $angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}