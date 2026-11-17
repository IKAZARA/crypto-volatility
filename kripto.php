<?php
// Konfigurasi Database
$db = new mysqli('localhost', 'root', '', 'crypto_db');
if ($db->connect_error) die("Koneksi gagal: " . $db->connect_error);

// Menarik data JSON dari API Indodax
$json_data = file_get_contents('https://indodax.com/api/ticker_all');
$data = json_decode($json_data, true);

if (isset($data['tickers'])) {
    $stmt = $db->prepare("INSERT INTO crypto_pattern (pair, high_price, low_price, current_price, range_val, top_range, lower_range, ptr_percent, plr_percent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($data['tickers'] as $pair => $ticker) {
        // Ekstraksi Variabel Dasar
        $H = (float)$ticker['high'];
        $L = (float)$ticker['low'];
        $C = (float)$ticker['last'];
        
        // Formulasi Rumus Jarak (Range)
        $R  = $H - $L;
        $TR = $H - $C;
        $LR = $C - $L;
        
        // Formulasi Persentase dengan Proteksi Division by Zero
        $PTR = ($R != 0) ? ($TR / $R) * 100 : 0;
        $PLR = ($R != 0) ? ($LR / $R) * 100 : 0;
        
        $stmt->bind_param("sdddddddd", $pair, $H, $L, $C, $R, $TR, $LR, $PTR, $PLR);
        $stmt->execute();
    }
    echo "<h3>Perekaman Data Selesai: " . date('Y-m-d H:i:s') . "</h3>";
    echo "<p>Menunggu siklus 30 detik berikutnya...</p>";
} else {
    echo "<p>Gagal mengambil data dari API.</p>";
}

$db->close();
?>
<!-- Pemicu Refresh Otomatis 30 Detik -->
<meta http-equiv="refresh" content="300">