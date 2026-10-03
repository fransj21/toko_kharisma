<?php
require_once 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$dompetList = $pdo->query("SELECT * FROM dompet ORDER BY nama_dompet ASC")->fetchAll();
$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dompet_id   = $_POST['dompet_id'];
    $kategori_id = $_POST['kategori_id'];
    $keterangan  = trim($_POST['keterangan']);
    $harga_modal = (float)$_POST['harga_modal'];
    $harga_jual  = (float)$_POST['harga_jual'];

    // Rumus Baru: Keuntungan Murni = Jual - Modal
    $keuntungan  = $harga_jual - $harga_modal;
    $total_potongan_saldo = $harga_modal;

    $stmtCek = $pdo->prepare("SELECT saldo, nama_dompet FROM dompet WHERE id = ?");
    $stmtCek->execute([$dompet_id]);
    $dompetInfo = $stmtCek->fetch();

    if ($dompetInfo['saldo'] < $total_potongan_saldo) {
        $pesan = "Transaksi GAGAL! Saldo " . $dompetInfo['nama_dompet'] . " tidak cukup. Sisa saldo saat ini: " . formatRupiah((float)$dompetInfo['saldo']);
    } else {
        try {
            $pdo->beginTransaction();
            $waktu_sekarang = date('Y-m-d H:i:s');
            
            // Insert tanpa kolom biaya_admin
            $stmt = $pdo->prepare("INSERT INTO transaksi (tanggal, dompet_id, kategori_id, keterangan, harga_modal, harga_jual, keuntungan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$waktu_sekarang, $dompet_id, $kategori_id, $keterangan, $harga_modal, $harga_jual, $keuntungan]);
            
            $stmtUpdate = $pdo->prepare("UPDATE dompet SET saldo = saldo - ? WHERE id = ?");
            $stmtUpdate->execute([$total_potongan_saldo, $dompet_id]);

            $pdo->commit();
            header("Location: index.php?status=success");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $pesan = 'Sistem Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi - Toko Kharisma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23ffffff' stroke='%23dc2626' stroke-width='8'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23dc2626'>TK</text></svg>">
</head>
<body class="bg-gray-50 font-sans text-gray-800 min-h-screen">
    <header class="bg-red-600 text-white shadow-md border-b-4 border-red-800">
        <div class="max-w-4xl mx-auto px-4 py-5 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="bg-white text-red-600 p-2 rounded-lg font-bold text-xl shadow">TK</div>
                <h1 class="text-xl md:text-2xl font-extrabold tracking-wide">TOKO KHARISMA</h1>
            </div>
            <a href="index.php" class="bg-white text-red-600 hover:bg-gray-100 px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg border border-red-100 p-6 md:p-8">
            <h2 class="text-lg md:text-xl font-bold text-red-600 mb-6 flex items-center gap-2 border-b pb-3">
                <i class="fa-solid fa-circle-plus"></i> Input Transaksi Top Up Baru
            </h2>

            <?php if($pesan): ?>
                <div class="bg-red-100 border-l-4 border-red-600 text-red-700 p-4 rounded-lg mb-6 text-sm font-bold shadow-sm">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Aplikasi / E-Wallet</label>
                    <select name="dompet_id" required class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Sumber Dompet --</option>
                        <?php foreach($dompetList as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= $d['nama_dompet'] ?> (Sisa Saldo: <?= formatRupiah((float)$d['saldo']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Kategori Layanan</label>
                    <select name="kategori_id" required class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Layanan --</option>
                        <?php foreach($kategoriList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kategori'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Keterangan / Detail</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Pulsa Telkomsel 5rb (08123456789)" required class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Harga Modal (Rp)</label>
                        <input type="number" id="harga_modal" name="harga_modal" placeholder="5000" required class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Harga Jual (Rp)</label>
                        <input type="number" id="harga_jual" name="harga_jual" placeholder="7000" required class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="bg-gray-100 p-4 rounded-lg flex justify-between items-center border border-gray-200 mt-2">
                    <span class="text-sm font-semibold text-gray-600">Estimasi Keuntungan:</span>
                    <span id="preview_keuntungan" class="text-xl font-bold text-gray-500">Rp 0</span>
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-lg shadow-md transition duration-200 mt-4">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Transaksi & Kurangi Saldo
                </button>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalInput = document.getElementById('harga_modal');
            const jualInput  = document.getElementById('harga_jual');
            const preview    = document.getElementById('preview_keuntungan');

            function hitungKeuntungan() {
                const modal = parseFloat(modalInput.value) || 0;
                const jual  = parseFloat(jualInput.value) || 0;
                const untung = jual - modal;

                preview.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(untung);

                if (untung < 0) {
                    preview.className = "text-xl font-bold text-red-600";
                } else if (untung > 0) {
                    preview.className = "text-xl font-bold text-green-600";
                } else {
                    preview.className = "text-xl font-bold text-gray-500";
                }
            }

            modalInput.addEventListener('input', hitungKeuntungan);
            jualInput.addEventListener('input', hitungKeuntungan);
        });
    </script>
</body>
</html>