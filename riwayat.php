<?php
require_once 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

$query_transaksi = "SELECT t.*, d.nama_dompet, k.nama_kategori 
          FROM transaksi t 
          JOIN dompet d ON t.dompet_id = d.id 
          JOIN kategori k ON t.kategori_id = k.id 
          WHERE DATE(t.tanggal) >= :mulai AND DATE(t.tanggal) <= :akhir 
          ORDER BY t.tanggal DESC";
$stmt = $pdo->prepare($query_transaksi);
$stmt->execute(['mulai' => $tanggal_mulai, 'akhir' => $tanggal_akhir]);
$transaksiList = $stmt->fetchAll();

$query_topup = "SELECT r.*, d.nama_dompet 
          FROM riwayat_topup r 
          JOIN dompet d ON r.dompet_id = d.id 
          WHERE DATE(r.tanggal) >= :mulai AND DATE(r.tanggal) <= :akhir 
          ORDER BY r.tanggal DESC";
$stmt2 = $pdo->prepare($query_topup);
$stmt2->execute(['mulai' => $tanggal_mulai, 'akhir' => $tanggal_akhir]);
$topupList = $stmt2->fetchAll();

$export_params = "?tanggal_mulai=" . urlencode($tanggal_mulai) . "&tanggal_akhir=" . urlencode($tanggal_akhir);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - Toko Kharisma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23ffffff' stroke='%23dc2626' stroke-width='8'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23dc2626'>TK</text></svg>">
</head>
<body class="bg-gray-50 font-sans text-gray-800 min-h-screen">
    <header class="bg-gray-800 text-white shadow-md border-b-4 border-red-600">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center gap-3">
            <h1 class="text-xl font-bold"><i class="fa-solid fa-clock-rotate-left mr-2"></i> RIWAYAT PEMBUKUAN</h1>
            <a href="index.php" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white p-4 md:p-5 rounded-xl shadow border border-gray-200 mb-6">
            <div class="flex flex-col lg:flex-row justify-between gap-4 items-end">
                <form method="GET" action="riwayat.php" class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>" required class="w-full md:w-auto border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-red-500 text-sm font-bold text-gray-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>" required class="w-full md:w-auto border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-red-500 text-sm font-bold text-gray-700">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2.5 rounded-lg text-sm transition shadow w-full md:w-auto">
                            <i class="fa-solid fa-magnifying-glass"></i> Filter
                        </button>
                        <a href="riwayat.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-4 py-2.5 rounded-lg text-sm transition shadow w-full md:w-auto text-center" title="Reset ke Hari Ini">
                            <i class="fa-solid fa-rotate"></i>
                        </a>
                    </div>
                </form>

                <div class="flex gap-2 w-full lg:w-auto">
                    <a href="export_excel.php<?= $export_params ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2.5 rounded-lg text-sm flex-1 md:flex-none flex items-center justify-center gap-2 shadow transition">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </a>
                    <a href="cetak_pdf.php<?= $export_params ?>" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2.5 rounded-lg text-sm flex-1 md:flex-none flex items-center justify-center gap-2 shadow transition">
                        <i class="fa-solid fa-file-pdf"></i> Cetak PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-4 text-sm font-bold text-gray-700 bg-blue-50 p-3 rounded-lg border-l-4 border-blue-500">
            <i class="fa-solid fa-calendar-check text-blue-600 mr-1"></i> 
            Menampilkan data periode: <span class="text-blue-700"><?= date('d M Y', strtotime($tanggal_mulai)) ?></span> s/d <span class="text-blue-700"><?= date('d M Y', strtotime($tanggal_akhir)) ?></span>
        </div>

        <h2 class="text-lg font-black text-gray-800 mb-3 border-l-4 border-red-600 pl-3 mt-8">1. RIWAYAT PENJUALAN KELUAR</h2>
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-x-auto w-full mb-8">
            <table class="w-full text-left border-collapse min-w-[750px]">
                <thead>
                    <tr class="bg-red-600 text-white text-xs uppercase font-bold border-b border-red-700">
                        <th class="p-4">Waktu (WIB)</th>
                        <th class="p-4">Dompet</th>
                        <th class="p-4">Layanan</th>
                        <th class="p-4">Keterangan</th>
                        <th class="p-4 text-right">Modal</th>
                        <th class="p-4 text-right">Jual</th>
                        <th class="p-4 text-right">Profit</th>
                        <th class="p-4 text-center">Aksi</th> <!-- Tambahan Judul -->
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if(empty($transaksiList)): ?>
                        <tr><td colspan="8" class="text-center py-6 text-gray-500 font-bold">Tidak ada transaksi jualan pada periode ini.</td></tr>
                    <?php else: foreach($transaksiList as $row): ?>
                        <tr class="hover:bg-red-50 transition">
                            <td class="p-4 text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td class="p-4 font-bold text-gray-700"><span class="bg-gray-100 border px-2 py-1 rounded text-xs"><?= $row['nama_dompet'] ?></span></td>
                            <td class="p-4 font-semibold text-gray-600"><?= $row['nama_kategori'] ?></td>
                            <td class="p-4 text-gray-600"><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td class="p-4 text-right"><?= formatRupiah($row['harga_modal']) ?></td>
                            <td class="p-4 text-right font-bold"><?= formatRupiah($row['harga_jual']) ?></td>
                            <td class="p-4 text-right font-bold text-green-600">+<?= formatRupiah($row['keuntungan']) ?></td>
                            
                            <!-- Kolom Aksi -->
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="struk.php?id=<?= $row['id'] ?>" target="_blank" class="bg-gray-800 hover:bg-black text-white px-3 py-1.5 rounded text-xs font-bold shadow transition flex items-center justify-center gap-1" title="Cetak Struk">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <a href="hapus_transaksi.php?id=<?= $row['id'] ?>&ref=riwayat" onclick="return confirm('Yakin hapus transaksi ini? Saldo modal <?= formatRupiah($row['harga_modal']) ?> akan kembali ke <?= $row['nama_dompet'] ?>.')" class="bg-red-600 hover:bg-red-800 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition flex items-center justify-center gap-1" title="Hapus Transaksi">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif;?>
                </tbody>
            </table>
        </div>

        <h2 class="text-lg font-black text-gray-800 mb-3 border-l-4 border-green-600 pl-3">2. RIWAYAT ISI SALDO (SUNTIK MODAL)</h2>
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-x-auto w-full mb-8">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-green-600 text-white text-xs uppercase font-bold border-b border-green-700">
                        <th class="p-4">Waktu (WIB)</th>
                        <th class="p-4">Dompet Tujuan</th>
                        <th class="p-4">Keterangan Sumber</th>
                        <th class="p-4 text-right">Nominal Pengisian Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if(empty($topupList)): ?>
                        <tr><td colspan="4" class="text-center py-6 text-gray-500 font-bold">Tidak ada riwayat isi saldo modal pada periode ini.</td></tr>
                    <?php else: foreach($topupList as $row): ?>
                        <tr class="hover:bg-green-50 transition">
                            <td class="p-4 text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td class="p-4 font-bold text-gray-700">
                                <span class="bg-gray-100 border px-2 py-1 rounded text-xs"><?= $row['nama_dompet'] ?></span>
                            </td>
                            <td class="p-4 text-gray-600 italic">
                                <?= !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-' ?>
                            </td>
                            <td class="p-4 text-right font-extrabold text-green-700">+<?= formatRupiah($row['nominal']) ?></td>
                        </tr>
                    <?php endforeach; endif;?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>