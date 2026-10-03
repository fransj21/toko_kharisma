<?php
// TAMPILKAN ERROR (Biar ketahuan kalau ada salah koding/koneksi di hosting)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$dompetList = $pdo->query("SELECT * FROM dompet ORDER BY nama_dompet ASC")->fetchAll();
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dompet_id = $_POST['dompet_id'];
    $nominal = (float)$_POST['nominal'];
    // Tangkap data keterangan (opsional, jika kosong set default string kosong)
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($nominal > 0) {
        try {
            $pdo->beginTransaction();
            $waktu_sekarang = date('Y-m-d H:i:s');

            // 1. Tambahkan saldo ke dompet
            $stmt = $pdo->prepare("UPDATE dompet SET saldo = saldo + ? WHERE id = ?");
            $stmt->execute([$nominal, $dompet_id]);

            // 2. Catat riwayat pengisian saldo (sekarang dengan keterangan)
            $stmtLog = $pdo->prepare("INSERT INTO riwayat_topup (tanggal, dompet_id, nominal, keterangan) VALUES (?, ?, ?, ?)");
            $stmtLog->execute([$waktu_sekarang, $dompet_id, $nominal, $keterangan]);

            $pdo->commit();
            header("Location: index.php?status=topup_success");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $pesan = 'Sistem Error: ' . $e->getMessage();
        }
    } else {
        $pesan = 'Nominal Top Up harus lebih dari 0!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Saldo E-Wallet - Toko Kharisma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23ffffff' stroke='%23dc2626' stroke-width='8'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23dc2626'>TK</text></svg>">
</head>
<body class="bg-gray-50 font-sans text-gray-800 min-h-screen">

    <header class="bg-red-600 text-white shadow-md border-b-4 border-red-800">
        <div class="max-w-xl mx-auto px-4 py-5 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="bg-white text-red-600 p-2 rounded-lg font-bold text-xl shadow">TK</div>
                <h1 class="text-xl font-extrabold tracking-wide">ISI SALDO DOMPET</h1>
            </div>
            <a href="index.php" class="text-white hover:text-red-200 text-sm font-semibold transition">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </a>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg border border-red-100 p-6 md:p-8">
            <h2 class="text-lg font-bold text-gray-700 mb-6 flex items-center gap-2 border-b pb-3">
                <i class="fa-solid fa-wallet text-red-600"></i> Form Tambah Saldo Modal
            </h2>

            <?php if($pesan): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-bold"><?= $pesan ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Pilih Dompet / E-Wallet</label>
                    <select name="dompet_id" required class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih E-Wallet --</option>
                        <?php foreach($dompetList as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= $d['nama_dompet'] ?> (Saldo: <?= formatRupiah((float)$d['saldo']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Input Nominal Modal -->
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Nominal Top Up (Rp)</label>
                    <input type="number" name="nominal" placeholder="Contoh: 500000" required class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500 text-lg font-bold">
                </div>

                <!-- TAMBAHAN: Input Keterangan -->
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Keterangan / Sumber Dana</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Modal dari Rekening BCA" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-[10px] text-gray-400 mt-1 italic">*Boleh dikosongkan</p>
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-lg shadow-md transition duration-200 mt-2">
                    <i class="fa-solid fa-check-circle"></i> Tambahkan Saldo Sekarang
                </button>
            </form>
        </div>
    </main>
</body>
</html>