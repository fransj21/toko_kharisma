<?php
require_once 'koneksi.php';

$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Riwayat_Toko_Kharisma_" . date('Ymd', strtotime($tanggal_mulai)) . "_sd_" . date('Ymd', strtotime($tanggal_akhir)) . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT t.*, d.nama_dompet, k.nama_kategori 
          FROM transaksi t 
          JOIN dompet d ON t.dompet_id = d.id 
          JOIN kategori k ON t.kategori_id = k.id 
          WHERE DATE(t.tanggal) >= :mulai AND DATE(t.tanggal) <= :akhir 
          ORDER BY t.tanggal DESC";

$stmt = $pdo->prepare($query);
$stmt->execute(['mulai' => $tanggal_mulai, 'akhir' => $tanggal_akhir]);
$data = $stmt->fetchAll();
?>
<table border="1">
    <tr>
        <th>Tanggal & Waktu (WIB)</th>
        <th>Sumber Dompet</th>
        <th>Kategori Layanan</th>
        <th>Keterangan</th>
        <th>Harga Modal</th>
        <th>Harga Jual</th>
        <th>Keuntungan</th>
    </tr>
    <?php foreach($data as $row): ?>
    <tr>
        <td><?= date('Y-m-d H:i:s', strtotime($row['tanggal'])) ?></td>
        <td><?= $row['nama_dompet'] ?></td>
        <td><?= $row['nama_kategori'] ?></td>
        <td><?= $row['keterangan'] ?></td>
        <td><?= $row['harga_modal'] ?></td>
        <td><?= $row['harga_jual'] ?></td>
        <td><?= $row['keuntungan'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>