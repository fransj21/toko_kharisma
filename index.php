<?php
require_once 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$hari_ini = date('Y-m-d');$selected_dompet = isset($_GET['dompet_id']) ?$_GET['dompet_id'] : 'all';
$dompetList =$pdo->query("SELECT * FROM dompet ORDER BY nama_dompet ASC")->fetchAll();

$query_base = "SELECT t.*, d.nama_dompet, k.nama_kategori 
               FROM transaksi t 
               JOIN dompet d ON t.dompet_id = d.id 
               JOIN kategori k ON t.kategori_id = k.id 
               WHERE DATE(t.tanggal) = :hari_ini";

if ($selected_dompet !== 'all') {$query_base .= " AND t.dompet_id = :dompet_id";
}
$query_base .= " ORDER BY t.tanggal DESC";

$stmt = $pdo->prepare($query_base);
if ($selected_dompet !== 'all') {$stmt->execute(['hari_ini' => $hari_ini, 'dompet_id' =>$selected_dompet]);
} else {
    $stmt->execute(['hari_ini' =>$hari_ini]);
}
$transaksiList =$stmt->fetchAll();

$totalOmset  = array_sum(array_column($transaksiList, 'harga_jual'));
$totalModal  = array_sum(array_column($transaksiList, 'harga_modal'));
$totalUntung = array_sum(array_column($transaksiList, 'keuntungan'));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Harian - <?= htmlspecialchars($NAMA_TOKO) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23ffffff' stroke='%23dc2626' stroke-width='8'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23dc2626'>TK</text></svg>">
    
    <!-- SweetAlert2 Library untuk Notifikasi Modern -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 min-h-screen">
    <header class="bg-red-600 text-white shadow-lg border-b-4 border-red-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col lg:flex-row justify-between items-center gap-4">
            <div class="flex items-center space-x-3 w-full lg:w-auto justify-center lg:justify-start">
                <div class="bg-white text-red-600 p-2 rounded-lg font-extrabold text-2xl shadow">TK</div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-wide"><?= htmlspecialchars($NAMA_TOKO) ?></h1>
                    <p class="text-[10px] md:text-xs text-red-100">Sistem Pembukuan Top Up & Multi E-Wallet</p>
                </div>
            </div>

            <!-- Kumpulan Tombol Aksi Header -->
            <div class="flex flex-wrap justify-center gap-2 w-full lg:w-auto">
                <a href="riwayat.php" class="bg-gray-800 hover:bg-gray-900 text-white font-bold px-3 py-2.5 rounded-lg text-sm transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                </a>
                <a href="transaksi.php" class="bg-white text-red-600 hover:bg-red-50 font-bold px-3 py-2.5 rounded-lg text-sm transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-red-600"></i> Transaksi
                </a>
                <button onclick="konfirmasiReset()" class="bg-red-900 hover:bg-black text-white font-bold px-3 py-2.5 rounded-lg text-sm transition shadow-md flex items-center gap-2" title="Reset Semua Data">
                    <i class="fa-solid fa-triangle-exclamation"></i> Reset
                </button>
                <a href="pengaturan.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-2.5 rounded-lg text-sm transition shadow-md flex items-center gap-2" title="Pengaturan">
                    <i class="fa-solid fa-gear"></i>
                </a>
                <a href="javascript:void(0)" onclick="konfirmasiLogout()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold px-3 py-2.5 rounded-lg text-sm transition shadow-md flex items-center gap-2" title="Keluar">
                    <i class="fa-solid fa-power-off text-red-600"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 md:py-8">
        <div class="bg-white p-4 md:p-5 rounded-xl shadow-md border border-red-100 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-3">
                <h2 class="text-lg font-bold text-gray-700"><i class="fa-solid fa-wallet text-red-600 mr-2"></i> Saldo Tersedia (Modal)</h2>
                <a href="topup_saldo.php" class="bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-4 py-2 w-full md:w-auto text-center rounded-lg transition shadow">
                    <i class="fa-solid fa-coins mr-1"></i> Isi Saldo
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                <?php foreach ($dompetList as$d): ?>
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg text-center">
                        <div class="text-[10px] md:text-xs font-bold text-gray-500 uppercase mb-1"><?= $d['nama_dompet'] ?></div>
                        <div class="text-sm md:text-base font-extrabold <?= $d['saldo'] < 10000 ? 'text-red-600' : 'text-gray-800' ?>">
                            <?= formatRupiah((float)$d['saldo']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white p-3 rounded-xl shadow-md border border-gray-200 mb-6 overflow-x-auto">
            <div class="flex space-x-2 min-w-max">
                <a href="index.php" class="px-4 py-2 rounded-lg font-bold text-sm transition <?= $selected_dompet === 'all' ? 'bg-red-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                    <i class="fa-solid fa-border-all mr-1"></i> Semua Dompet
                </a>
                <?php foreach ($dompetList as$d): ?>
                    <a href="index.php?dompet_id=<?= $d['id'] ?>" class="px-4 py-2 rounded-lg font-bold text-sm transition <?= (string)$selected_dompet === (string)$d['id'] ? 'bg-red-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                        <i class="fa-solid fa-wallet mr-1"></i> <?= $d['nama_dompet'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <h2 class="text-lg font-black text-gray-800 mb-3 border-l-4 border-red-600 pl-3">STATISTIK HARI INI (<?= date('d M Y') ?>)</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-5 mb-8">
            <div class="bg-white rounded-xl p-4 md:p-5 shadow border-t-4 border-blue-500">
                <div class="text-[10px] md:text-xs font-bold text-gray-400 uppercase">Total Omset (Penjualan)</div>
                <div class="text-lg md:text-2xl font-extrabold text-gray-800 mt-1"><?= formatRupiah($totalOmset) ?></div>
            </div>
            <div class="bg-white rounded-xl p-4 md:p-5 shadow border-t-4 border-yellow-500">
                <div class="text-[10px] md:text-xs font-bold text-gray-400 uppercase">Total Modal</div>
                <div class="text-lg md:text-2xl font-extrabold text-gray-800 mt-1"><?= formatRupiah($totalModal) ?></div>
            </div>
            <div class="bg-white rounded-xl p-4 md:p-5 shadow border-t-4 border-green-500">
                <div class="text-[10px] md:text-xs font-bold text-gray-400 uppercase">Profit Hari Ini</div>
                <div class="text-lg md:text-2xl font-extrabold text-green-600 mt-1"><?= formatRupiah($totalUntung) ?></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-gray-700 text-sm md:text-lg">
                    <i class="fa-solid fa-list text-red-600 mr-2"></i>
                    Rincian Pembukuan Hari Ini
                </h3>
                <span class="text-[10px] md:text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full font-bold">
                    <?= count($transaksiList) ?> Transaksi
                </span>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse min-w-[750px]">
                    <thead>
                        <tr class="bg-red-600 text-white text-[10px] md:text-xs uppercase font-semibold">
                            <th class="p-3">Waktu (WIB)</th>
                            <th class="p-3">E-Wallet</th>
                            <th class="p-3">Layanan</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3 text-right">Modal</th>
                            <th class="p-3 text-right">Jual</th>
                            <th class="p-3 text-right">Profit</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                        <?php if (empty($transaksiList)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-400 font-semibold">Belum ada transaksi hari ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transaksiList as$row): ?>
                                <tr class="hover:bg-red-50/50 transition">
                                    <td class="p-3 text-gray-500"><?= date('H:i', strtotime($row['tanggal'])) ?></td>
                                    <td class="p-3 font-bold text-gray-700">
                                        <span class="bg-gray-100 text-gray-800 text-[10px] md:text-xs font-semibold px-2 py-1 rounded border border-gray-300">
                                            <?= $row['nama_dompet'] ?>
                                        </span>
                                    </td>
                                    <td class="p-3 font-semibold text-gray-600"><?= $row['nama_kategori'] ?></td>
                                    <td class="p-3 text-gray-600 max-w-[150px] truncate"><?= htmlspecialchars($row['keterangan']) ?></td>
                                    <td class="p-3 text-right text-gray-600"><?= formatRupiah($row['harga_modal']) ?></td>
                                    <td class="p-3 text-right font-bold text-gray-800"><?= formatRupiah($row['harga_jual']) ?></td>
                                    <td class="p-3 text-right font-extrabold text-green-600">+<?= formatRupiah($row['keuntungan']) ?></td>

                                    <!-- Kolom Aksi -->
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="struk.php?id=<?= $row['id'] ?>" target="_blank" class="bg-gray-800 hover:bg-black text-white px-3 py-1.5 rounded text-xs font-bold shadow transition flex items-center justify-center gap-1" title="Cetak Struk">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                            <!-- Tombol Hapus memanggil fungsi JS -->
                                            <a href="javascript:void(0)" onclick="konfirmasiHapus('hapus_transaksi.php?id=<?= $row['id'] ?>&ref=index', '<?= formatRupiah($row['harga_modal']) ?>', '<?=$row['nama_dompet'] ?>')" class="bg-red-600 hover:bg-red-800 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition flex items-center justify-center gap-1" title="Hapus Transaksi">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Kumpulan Script Notifikasi Elegan (SweetAlert2) -->
    <script>
        // Fungsi Hapus Transaksi (Per baris)
        function konfirmasiHapus(url, nominal, dompet) {
            Swal.fire({
                title: 'Yakin hapus transaksi?',
                html: `Saldo modal sebesar <b class="text-red-600">${nominal}</b> akan otomatis dikembalikan ke dompet <b>${dompet}</b>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        // Fungsi Reset Total Pembukuan
        function konfirmasiReset() {
            Swal.fire({
                title: 'RESET SEMUA DATA?',
                html: "Semua riwayat transaksi & top up akan <b>dihapus permanen</b>, dan seluruh saldo E-Wallet akan kembali menjadi <b>Rp 0</b>!<br><br><i class='text-red-500'>Tindakan ini tidak bisa dibatalkan!</i>",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#991b1b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Reset Total!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'reset_transaksi.php';
                }
            });
        }

        // Fungsi Logout
        function konfirmasiLogout() {
            Swal.fire({
                title: 'Akhiri sesi kasir?',
                text: "Anda akan keluar dari aplikasi pembukuan.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        // Tangkap parameter URL untuk nampilin Notifikasi Sukses
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const pesan = urlParams.get('pesan');
            const status = urlParams.get('status');

            if (pesan === 'dihapus') {
                Swal.fire({ title: 'Terhapus!', text: 'Transaksi berhasil dihapus & saldo dikembalikan.', icon: 'success', timer: 2500, showConfirmButton: false });
            } else if (pesan === 'reset_sukses') {
                Swal.fire({ title: 'Toko Direset!', text: 'Semua data pembukuan bersih & saldo kembali Rp 0.', icon: 'success', timer: 3000, showConfirmButton: false });
            } else if (status === 'success') {
                Swal.fire({ title: 'Tersimpan!', text: 'Transaksi baru berhasil dicatat.', icon: 'success', timer: 2000, showConfirmButton: false });
            } else if (status === 'topup_success') {
                Swal.fire({ title: 'Modal Masuk!', text: 'Saldo E-Wallet berhasil ditambahkan.', icon: 'success', timer: 2000, showConfirmButton: false });
            }

            // Bersihkan parameter URL setelah notif muncul (biar rapi)
            if (pesan || status) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        });
    </script>
</body>
</html>