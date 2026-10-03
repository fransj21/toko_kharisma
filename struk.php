<?php
require_once 'koneksi.php';

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT t.*, d.nama_dompet, k.nama_kategori 
                       FROM transaksi t 
                       JOIN dompet d ON t.dompet_id = d.id 
                       JOIN kategori k ON t.kategori_id = k.id 
                       WHERE t.id = ?");
$stmt->execute([$id]);
$trx = $stmt->fetch();

if (!$trx) {
    die("Data transaksi tidak ada.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - Toko Kharisma</title>
    <style>
        /* CSS Khusus Printer Thermal 58mm */
        @page { margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 58mm; 
            max-width: 58mm; 
            font-size: 12px; 
            margin: 0 auto; 
            padding: 10px; 
            color: #000; 
            background: #fff;
        }
        .center { text-align: center; }
        .garis { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; font-size: 12px; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .keterangan { margin: 5px 0; font-size: 12px; word-wrap: break-word; }
        .footer { text-align: center; margin-top: 15px; font-size: 11px; font-weight: bold; }
        
        /* Tombol print disembunyikan saat kertas dicetak */
        @media print {
            .no-print { display: none !important; }
        }

        .btn-print {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #dc2626;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="center">
        <h2 style="margin:0; font-size: 18px;">TOKO KHARISMA</h2>
        <p style="margin:2px 0 0 0; font-size: 10px;">Sistem Top Up & Payment</p>
    </div>
    
    <div class="garis"></div>
    
    <table>
        <tr>
            <td>Tgl</td>
            <td class="right"><?= date('d/m/y H:i', strtotime($trx['tanggal'])) ?></td>
        </tr>
        <tr>
            <td>Layanan</td>
            <td class="right"><?= $trx['nama_kategori'] ?></td>
        </tr>
        <tr>
            <td>Via</td>
            <td class="right"><?= $trx['nama_dompet'] ?></td>
        </tr>
    </table>
    
    <div class="garis"></div>
    
    <div class="keterangan">
        <strong>Detail:</strong><br>
        <?= htmlspecialchars($trx['keterangan']) ?>
    </div>
    
    <div class="garis"></div>
    
    <table>
        <tr>
            <td style="font-size: 14px; font-weight: bold;">TOTAL</td>
            <td class="right" style="font-size: 14px; font-weight: bold;">
                <?= formatRupiah($trx['harga_jual']) ?>
            </td>
        </tr>
    </table>
    
    <div class="garis"></div>
    
    <!-- Footnote Sesuai Request -->
    <div class="footer">
        Terimakasih telah berbelanja di Toko Kharisma Banyak Lagi Rejekinya
    </div>

    <!-- Tombol Pemicu Suara & Print -->
    <button class="no-print btn-print" id="btnCetak">
        Cetak Struk & Putar Suara
    </button>

    <script>
        document.getElementById('btnCetak').addEventListener('click', function() {
            // 1. Siapkan teks untuk diucapkan
            const textToSpeak = "Terimakasih telah berbelanja di Toko Kharisma Banyak Lagi Rejekinya";
            
            // 2. Panggil Web Speech API
            const speech = new SpeechSynthesisUtterance(textToSpeak);
            speech.lang = 'id-ID'; // Menggunakan logat Bahasa Indonesia
            speech.rate = 1.0; // Kecepatan bicara normal
            speech.pitch = 1.1; // Nada suara

            // 3. EVENT: Begitu suara SELESAI diputar, otomatis panggil fungsi print()
            speech.onend = function() {
                window.print();
            };

            // 4. Putar suaranya!
            window.speechSynthesis.speak(speech);
        });
    </script>
</body>
</html>