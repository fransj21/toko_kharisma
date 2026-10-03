<?php
require_once 'koneksi.php';

// Cek apakah user sudah login (keamanan)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $ref = isset($_GET['ref']) ? $_GET['ref'] : 'index'; // Untuk tahu kembalinya ke halaman mana

    try {
        // 1. Cari data transaksinya dulu untuk tahu berapa modal yang harus dibalikin
        $stmtCek = $pdo->prepare("SELECT dompet_id, harga_modal FROM transaksi WHERE id = ?");
        $stmtCek->execute([$id]);
        $trx = $stmtCek->fetch();

        if ($trx) {
            $pdo->beginTransaction();

            // 2. Kembalikan saldo modal ke E-Wallet asalnya
            $stmtRefund = $pdo->prepare("UPDATE dompet SET saldo = saldo + ? WHERE id = ?");
            $stmtRefund->execute([$trx['harga_modal'], $trx['dompet_id']]);

            // 3. Hapus data transaksi dari database
            $stmtDelete = $pdo->prepare("DELETE FROM transaksi WHERE id = ?");
            $stmtDelete->execute([$id]);

            $pdo->commit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal menghapus transaksi: " . $e->getMessage());
    }

    // Arahkan kembali ke halaman sebelumnya
    if ($ref === 'riwayat') {
        header("Location: riwayat.php?pesan=dihapus");
    } else {
        header("Location: index.php?pesan=dihapus");
    }
    exit;
} else {
    die("ID Transaksi tidak ditemukan.");
}
?>