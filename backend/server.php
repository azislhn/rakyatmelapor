<?php
header('Content-Type: application/json');
// Mengizinkan permintaan dari domain manapun untuk pengembangan
// Dalam produksi, batasi ke domain frontend Anda
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Mengatasi permintaan OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Konfigurasi Database
$servername = getenv('DB_HOST') ?? 'db';
$username = getenv('DB_USER') ?? 'root';
$password = getenv('DB_PASSWORD') ?? 'admin123';
$dbname = getenv('DB_NAME') ?? 'rakyat_melapor';

$conn = null; // Inisialisasi koneksi sebagai null

// Coba buat koneksi database hanya jika semua info tersedia
if ($servername && $username && $password && $dbname) {
    // Suppress error reporting to avoid showing direct connection errors to user
    // We'll catch it with connect_error
    $conn = @new mysqli($servername, $username, $password, $dbname);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        $conn = null; // Set koneksi kembali ke null jika gagal
    } else {
        // Jika koneksi berhasil, pastikan tabel ada
        $create_table_sql = "
        CREATE TABLE IF NOT EXISTS reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identity VARCHAR(255) NOT NULL,
            topic VARCHAR(100) NOT NULL,
            details TEXT NOT NULL,
            report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );";
        if (!$conn->query($create_table_sql)) {
            error_log("Failed to create table: " . $conn->error);
            // Tabel gagal dibuat, mungkin ada masalah lain.
            // Biarkan koneksi tetap ada atau set null jika ingin non-fungsional.
            // Untuk skenario ini, kita anggap koneksi berhasil tapi ada error tabel.
        }
    }
} else {
    error_log("Database environment variables are not fully set. Running without database functionality.");
}

// Hanya proses jika metode permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari permintaan POST
    $identity = $_POST['identity'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $details = $_POST['details'] ?? '';

    // Validasi data input dasar (selalu berfungsi)
    if (empty($identity) || empty($topic) || empty($details)) {
        echo json_encode(['error' => 'Semua kolom harus diisi.']);
        exit();
    }

    // Jika koneksi database tersedia, coba simpan laporan
    if ($conn) {
        $stmt = $conn->prepare("INSERT INTO reports (identity, topic, details) VALUES (?, ?, ?)");
        if ($stmt === false) {
            error_log('Failed to prepare SQL statement: ' . $conn->error);
            echo json_encode(['error' => 'Failed to prepare report for saving.']);
            exit();
        }
        $stmt->bind_param("sss", $identity, $topic, $details);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Laporan berhasil disimpan!']);
        } else {
            error_log('Failed to save report: ' . $stmt->error);
            echo json_encode(['error' => 'Gagal menyimpan laporan: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        // Beri tahu klien bahwa fitur penyimpanan database tidak berfungsi
        echo json_encode(['message' => 'Laporan diterima, tetapi fitur penyimpanan database tidak aktif atau gagal terhubung.', 'status' => 'warning']);
    }
} else {
    // Jika bukan permintaan POST, kirim pesan error atau status
    echo json_encode(['message' => 'Welcome to the reporting backend!', 'database_status' => $conn ? 'connected' : 'not_connected_or_configured']);
}

// Menutup koneksi database jika ada
if ($conn) {
    $conn->close();
}
?>