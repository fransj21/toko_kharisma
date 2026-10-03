<?php
require_once 'koneksi.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    // 1. Matikan sementara sistem proteksi relasi antar tabel (Foreign Key)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // 2. Kosongkan tabel transaksi & reset ID ke 1
    $pdo->exec("TRUNCATE TABLE transaksi");
    
    // 3. Kosongkan riwayat isi saldo & reset ID ke 1
    $pdo->exec("TRUNCATE TABLE riwayat_topup");
    
    // 4. Kembalikan semua saldo dompet menjadi Rp 0
    $pdo->exec("UPDATE dompet SET saldo = 0");
    
    // 5. Wajib! Nyalakan kembali sistem proteksi relasinya
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Kembali ke dashboard dengan membawa parameter sukses
    header("Location: index.php?pesan=reset_sukses");
    exit;

} catch (Exception $e) {
    // Jika terjadi error, pastikan proteksi relasi tetap dinyalakan kembali
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    die("Gagal mereset data: " . $e->getMessage());
}
?>