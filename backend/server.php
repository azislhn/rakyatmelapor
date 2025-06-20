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
$servername = "db"; // Nama layanan database di docker-compose
$username = "root";
$password = "admin123"; // Password yang diminta
$dbname = "rakyat_melapor"; // Nama database

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    echo json_encode(['error' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit();
}

// Pastikan tabel 'reports' ada (ini bisa juga dilakukan melalui init.sql di Azure MySQL)
$create_table_sql = "
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identity VARCHAR(255) NOT NULL,
    topic VARCHAR(100) NOT NULL,
    details TEXT NOT NULL,
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
if (!$conn->query($create_table_sql)) {
    error_log("Gagal membuat tabel: " . $conn->error);
    echo json_encode(['error' => 'Gagal menginisialisasi tabel database: ' . $conn->error]);
    exit();
}

// Hanya proses jika metode permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari permintaan POST
    $identity = $_POST['identity'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $details = $_POST['details'] ?? '';

    // Validasi data input
    if (empty($identity) || empty($topic) || empty($details)) {
        echo json_encode(['error' => 'Semua kolom harus diisi.']);
        exit();
    }

    // Menyiapkan dan mengikat
    $stmt = $conn->prepare("INSERT INTO reports (identity, topic, details) VALUES (?, ?, ?)");
    if ($stmt === false) {
        echo json_encode(['error' => 'Gagal menyiapkan pernyataan SQL: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("sss", $identity, $topic, $details);

    // Mengeksekusi pernyataan
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Laporan berhasil disimpan!']);
    } else {
        echo json_encode(['error' => 'Gagal menyimpan laporan: ' . $stmt->error]);
    }

    // Menutup pernyataan
    $stmt->close();
} else {
    // Jika bukan permintaan POST, kirim pesan error
    echo json_encode(['error' => 'Metode permintaan tidak didukung.']);
}

// Menutup koneksi database
$conn->close();
?>
