<?php
require_once 'koneksi.php';

$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

$query = "SELECT t.*, d.nama_dompet, k.nama_kategori 
          FROM transaksi t 
          JOIN dompet d ON t.dompet_id = d.id 
          JOIN kategori k ON t.kategori_id = k.id 
          WHERE DATE(t.tanggal) >= :mulai AND DATE(t.tanggal) <= :akhir 
          ORDER BY t.tanggal DESC";

$stmt = $pdo->prepare($query);
$stmt->execute(['mulai' => $tanggal_mulai, 'akhir' => $tanggal_akhir]);
$data = $stmt->fetchAll();

$periode = date('d M Y', strtotime($tanggal_mulai)) . " s/d " . date('d M Y', strtotime($tanggal_akhir));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak PDF - Toko Kharisma</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; font-size: 13px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .right { text-align: right; }
    </style>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23ffffff' stroke='%23dc2626' stroke-width='8'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23dc2626'>TK</text></svg>">
</head>
<body onload="window.print()">
    <div class="header">
        <h1>Laporan Transaksi - Toko Kharisma</h1>
        <p>Periode: <strong><?= $periode ?></strong></p>
        <p>Waktu Cetak: <?= date('d M Y - H:i:s') ?> WIB</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Tanggal (WIB)</th>
                <th>Dompet</th>
                <th>Layanan</th>
                <th>Keterangan</th>
                <th class="right">Modal</th>
                <th class="right">Jual</th>
                <th class="right">Profit</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data)): ?>
                <tr><td colspan="7" style="text-align:center; padding: 15px;">Tidak ada transaksi di periode ini.</td></tr>
            <?php else: foreach($data as $row): ?>
            <tr>
                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                <td><?= $row['nama_dompet'] ?></td>
                <td><?= $row['nama_kategori'] ?></td>
                <td><?= $row['keterangan'] ?></td>
                <td class="right"><?= number_format($row['harga_modal'],0,',','.') ?></td>
                <td class="right"><?= number_format($row['harga_jual'],0,',','.') ?></td>
                <td class="right"><?= number_format($row['keuntungan'],0,',','.') ?></td>
            </tr>
            <?php endforeach; endif;?>
        </tbody>
    </table>
</body>
</html>