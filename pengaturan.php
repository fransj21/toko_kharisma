<?php
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';
$tipe_pesan = 'bg-green-100 text-green-700';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_toko'])) {
        $nama_toko_baru = trim($_POST['nama_toko']);
        $pdo->prepare("UPDATE pengaturan SET nama_toko = ? WHERE id = 1")->execute([$nama_toko_baru]);
        $pesan = "Nama Toko berhasil diperbarui!";
        $NAMA_TOKO = $nama_toko_baru; // update variabel lokal agar langsung berubah
    }

    if (isset($_POST['update_user'])) {
        $username_baru = trim($_POST['username']);
        $password_baru = $_POST['password'];
        $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = 1")->execute([$username_baru, $password_baru]);
        $_SESSION['username'] = $username_baru;
        $pesan = "Akun Login berhasil diperbarui!";
    }
}

$user = $pdo->query("SELECT username, password FROM users WHERE id = 1")->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - <?= htmlspecialchars($NAMA_TOKO) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23ffffff' stroke='%23dc2626' stroke-width='8'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23dc2626'>TK</text></svg>">
</head>
<body class="bg-gray-50 font-sans text-gray-800 min-h-screen">
    <header class="bg-gray-800 text-white shadow-md border-b-4 border-red-600">
        <div class="max-w-4xl mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center gap-3">
            <h1 class="text-xl font-bold"><i class="fa-solid fa-gear mr-2"></i> PENGATURAN APLIKASI</h1>
            <a href="index.php" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <?php if($pesan): ?>
            <div class="<?= $tipe_pesan ?> p-4 rounded-lg mb-6 font-bold shadow-sm border border-green-200">
                <i class="fa-solid fa-circle-check mr-1"></i> <?= $pesan ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Edit Toko -->
            <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
                <h2 class="text-lg font-bold text-red-600 mb-4 border-b pb-2"><i class="fa-solid fa-store"></i> Pengaturan Toko</h2>
                <form method="POST">
                    <input type="hidden" name="update_toko" value="1">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Nama Toko di Aplikasi & Struk</label>
                        <input type="text" name="nama_toko" value="<?= htmlspecialchars($NAMA_TOKO) ?>" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-500 outline-none font-bold">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg shadow transition">Simpan Nama Toko</button>
                </form>
            </div>

            <!-- Edit Akun Login -->
            <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
                <h2 class="text-lg font-bold text-red-600 mb-4 border-b pb-2"><i class="fa-solid fa-user-lock"></i> Pengaturan Akun Admin</h2>
                <form method="POST">
                    <input type="hidden" name="update_user" value="1">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Username Login</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Password Baru (Ketik ulang jika tidak ingin diganti)</label>
                        <input type="text" name="password" value="<?= htmlspecialchars($user['password']) ?>" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg shadow transition">Simpan Akun</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>