-- Pastikan database yang benar sedang digunakan
USE rakyat_melapor;

-- Buat tabel 'reports' jika belum ada
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identity VARCHAR(255) NOT NULL,
    topic VARCHAR(100) NOT NULL,
    details TEXT NOT NULL,
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Anda bisa menambahkan data dummy jika diperlukan untuk pengujian
-- INSERT INTO reports (identity, topic, details) VALUES
-- ('test@example.com', 'Infrastruktur', 'Jalan berlubang di dekat kantor kelurahan.'),
-- ('John Doe', 'Kebersihan', 'Sampah menumpuk di gang sebelah.');