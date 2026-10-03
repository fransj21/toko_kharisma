<?php
require_once 'koneksi.php';

// Jika sudah login, tendang ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Cek kecocokan (Karena belum dienkripsi, pakai ==)
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $pesan = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($NAMA_TOKO) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23ffffff' stroke='%23dc2626' stroke-width='8'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23dc2626'>TK</text></svg>">
</head>
<body class="bg-gray-100 font-sans text-gray-800 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-xl border-t-4 border-red-600 w-full max-w-sm">
        <div class="text-center mb-6">
            <div class="bg-red-600 text-white w-16 h-16 mx-auto rounded-xl flex items-center justify-center font-black text-3xl shadow-md mb-3">TK</div>
            <h1 class="text-2xl font-black text-gray-800"><?= htmlspecialchars($NAMA_TOKO) ?></h1>
            <p class="text-sm text-gray-500">Silakan login untuk melanjutkan</p>
        </div>

        <?php if($pesan): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-bold text-center"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Username</label>
                <input type="text" name="username" required class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-lg shadow-md transition">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> MASUK APLIKASI
            </button>
        </form>
    </div>
</body>
</html>