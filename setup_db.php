<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h3><i class="bi bi-database-fill-gear"></i> Aplikasi RT - Database Setup</h3>
        </div>
        <div class="card-body">
            <ul class="list-group">
<?php

function log_message($message, $is_success = true) {
    $status_class = $is_success ? 'success' : 'danger';
    $icon = $is_success ? 'check-circle-fill' : 'x-circle-fill';
    echo "<li class=\"list-group-item d-flex justify-content-between align-items-center\">{$message} <span class=\"text-{$status_class}\"><i class=\"bi bi-{$icon}\"></i></span></li>";
}

function log_error_and_die($message, $error_details) {
    log_message($message, false);
    echo '</ul></div><div class="card-footer"><div class="alert alert-danger mb-0"><strong>Detail Error:</strong> ' . htmlspecialchars($error_details) . '</div></div></div></div></body></html>';
    die();
}

// --- Database Configuration ---
require_once 'includes/Config.php';
try {
    Config::load(__DIR__ . '/.env');
} catch (\Exception $e) {
    log_error_and_die('Gagal memuat file .env', 'Pastikan file .env ada di direktori root dan dapat dibaca. Error: ' . $e->getMessage());
}

$db_server = Config::get('DB_SERVER');
$db_username = Config::get('DB_USERNAME');
$db_password = Config::get('DB_PASSWORD');
$db_name = Config::get('DB_NAME');

// --- SQL Statements ---
$default_password_hash = password_hash('password', PASSWORD_DEFAULT);

// Baca file SQL

$sql_file_path = __DIR__ . '/database_keuangan.sql';
if (!file_exists($sql_file_path)) {
    log_error_and_die('File SQL tidak ditemukan', 'Pastikan file `database_rt.sql` ada di direktori root.');
}
$sql_template = file_get_contents($sql_file_path);
if ($sql_template === false) {
    log_error_and_die('Gagal membaca file SQL', 'Pastikan file `database_rt.sql` dapat dibaca.');
}

// Ganti placeholder di SQL dengan nilai dinamis
$sql = str_replace('{$default_password_hash}', $default_password_hash, $sql_template);

// --- Execution Logic ---
$conn_setup = new mysqli($db_server, $db_username, $db_password);
if ($conn_setup->connect_error) {
    log_error_and_die("Koneksi ke MySQL server Gagal", $conn_setup->connect_error);
}
log_message("Berhasil terhubung ke MySQL server.");

if ($conn_setup->query("CREATE DATABASE IF NOT EXISTS `" . $db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
    log_message("Database '" . $db_name . "' berhasil dibuat atau sudah ada.");
} else {
    log_error_and_die("Error membuat database", $conn_setup->error);
}
$conn_setup->select_db($db_name);

/*
if ($conn_setup->multi_query($sql)) {
    while ($conn_setup->more_results() && $conn_setup->next_result()) {;}
    log_message("Struktur tabel dan data awal berhasil dibuat.");
} else {
    log_error_and_die("Error saat setup tabel", $conn_setup->error);
}*/

// --- Setup Tabel HR & Payroll ---
$sql_hr = "
CREATE TABLE IF NOT EXISTS `hr_kantor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kantor` varchar(100) NOT NULL,
  `jenis_kantor` enum('Pusat','Cabang','Lainnya') NOT NULL DEFAULT 'Cabang',
  `alamat` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_divisi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_divisi` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_jadwal_kerja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jadwal` varchar(100) NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_jadwal` (`nama_jadwal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_jabatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  `tunjangan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_golongan_gaji` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_golongan` varchar(100) NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_karyawan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nip` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jabatan_id` int(11) DEFAULT NULL,
  `jadwal_kerja_id` int(11) DEFAULT NULL,
  `divisi_id` int(11) DEFAULT NULL,
  `kantor_id` int(11) DEFAULT NULL,
  `golongan_gaji_id` int(11) DEFAULT NULL,
  `tanggal_masuk` date NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `jadwal_kerja_id` (`jadwal_kerja_id`),
  KEY `jabatan_id` (`jabatan_id`),
  KEY `divisi_id` (`divisi_id`),
  KEY `kantor_id` (`kantor_id`),
  KEY `golongan_gaji_id` (`golongan_gaji_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_hr_karyawan_jabatan` FOREIGN KEY (`jabatan_id`) REFERENCES `hr_jabatan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hr_karyawan_jadwal` FOREIGN KEY (`jadwal_kerja_id`) REFERENCES `hr_jadwal_kerja` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hr_karyawan_divisi` FOREIGN KEY (`divisi_id`) REFERENCES `hr_divisi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hr_karyawan_kantor` FOREIGN KEY (`kantor_id`) REFERENCES `hr_kantor` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hr_karyawan_golongan_gaji` FOREIGN KEY (`golongan_gaji_id`) REFERENCES `hr_golongan_gaji` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hr_karyawan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `golongan` varchar(50) DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpa') DEFAULT 'hadir',
  `keterangan` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  CONSTRAINT `fk_hr_absensi_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `hr_karyawan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_penggajian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `periode_bulan` int(2) NOT NULL,
  `periode_tahun` int(4) NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_gaji` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','final') DEFAULT 'draft',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  CONSTRAINT `fk_hr_penggajian_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `hr_karyawan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn_setup->multi_query($sql_hr)) {
    while ($conn_setup->more_results() && $conn_setup->next_result()) {;}
    log_message("Struktur tabel HR & Payroll berhasil dibuat.");
} else {
    log_error_and_die("Error saat setup tabel HR", $conn_setup->error);
}


$sql_hr_komponen = "
CREATE TABLE IF NOT EXISTS `hr_komponen_gaji` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_komponen` varchar(100) NOT NULL,
  `jenis` enum('pendapatan','potongan') NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Apakah komponen ini ditambahkan otomatis saat generate gaji',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_penggajian_komponen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `penggajian_id` int(11) NOT NULL,
  `komponen_id` int(11) NOT NULL,
  `nama_komponen` varchar(100) NOT NULL COMMENT 'Denormalized name for easier reporting',
  `jenis` enum('pendapatan','potongan') NOT NULL,
  `jumlah` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `penggajian_id` (`penggajian_id`),
  KEY `komponen_id` (`komponen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn_setup->multi_query($sql_hr_komponen)) {
    while ($conn_setup->more_results() && $conn_setup->next_result()) {;}
    log_message("Struktur tabel Komponen Gaji berhasil dibuat.");
} else {
    log_error_and_die("Error saat setup tabel Komponen Gaji", $conn_setup->error);
}

$sql_hr_cuti = "
CREATE TABLE IF NOT EXISTS `hr_jenis_cuti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(100) NOT NULL,
  `mengurangi_jatah_cuti` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Ya, 0=Tidak',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_jatah_cuti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `tahun` int(4) NOT NULL,
  `jatah_awal` int(3) NOT NULL DEFAULT 12,
  `sisa_jatah` int(3) NOT NULL DEFAULT 12,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karyawan_tahun` (`karyawan_id`,`tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_pengajuan_cuti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `jenis_cuti_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jumlah_hari` int(3) NOT NULL,
  `keterangan` text,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$sql_hr_pajak = "
CREATE TABLE IF NOT EXISTS `hr_pengaturan_pajak` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn_setup->query($sql_hr_pajak);


if ($conn_setup->multi_query($sql_hr_cuti)) {
    while ($conn_setup->more_results() && $conn_setup->next_result()) {;}
    log_message("Struktur tabel Manajemen Cuti berhasil dibuat.");
} else {
    log_error_and_die("Error saat setup tabel Manajemen Cuti", $conn_setup->error);
}

// --- Populate HR Data & Create Master Tables ---
$sql_hr_data = "
CREATE TABLE IF NOT EXISTS `hr_absensi_golongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_golongan` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_golongan` (`nama_golongan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_absensi_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_status` varchar(50) NOT NULL,
  `badge_class` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_status` (`nama_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `hr_absensi_golongan` (`nama_golongan`) VALUES
('Shift Pagi'), ('Shift Siang'), ('Shift Malam'), ('Non-Shift');

INSERT IGNORE INTO `hr_absensi_status` (`nama_status`, `badge_class`) VALUES
('hadir', 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'),
('izin', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'),
('sakit', 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'),
('alpa', 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200');
";

if (!$conn_setup->multi_query($sql_hr_data)) {
    log_error_and_die("Error saat membuat tabel master HR", $conn_setup->error);
}
while ($conn_setup->more_results() && $conn_setup->next_result()) {;} // Clear results
log_message("Tabel dan data master HR & Payroll berhasil dibuat/diperbarui.");

// --- Update Struktur Tabel (Jika tabel sudah ada) ---
$check_col = $conn_setup->query("SHOW COLUMNS FROM `hr_absensi` LIKE 'golongan'");
if ($check_col && $check_col->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_absensi` ADD COLUMN `golongan` VARCHAR(50) DEFAULT NULL AFTER `tanggal`");
    log_message("Kolom 'golongan' berhasil ditambahkan ke tabel hr_absensi.");
}

$check_col_jadwal = $conn_setup->query("SHOW COLUMNS FROM `hr_karyawan` LIKE 'jadwal_kerja_id'");
if ($check_col_jadwal && $check_col_jadwal->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_karyawan` ADD COLUMN `jadwal_kerja_id` INT(11) DEFAULT NULL AFTER `jabatan_id`, ADD KEY `jadwal_kerja_id` (`jadwal_kerja_id`), ADD CONSTRAINT `fk_hr_karyawan_jadwal` FOREIGN KEY (`jadwal_kerja_id`) REFERENCES `hr_jadwal_kerja` (`id`) ON DELETE SET NULL;");
    log_message("Kolom 'jadwal_kerja_id' berhasil ditambahkan ke tabel hr_karyawan.");
}

$check_col_kantor = $conn_setup->query("SHOW COLUMNS FROM `hr_karyawan` LIKE 'kantor_id'");
if ($check_col_kantor && $check_col_kantor->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_karyawan` ADD COLUMN `kantor_id` INT(11) DEFAULT NULL AFTER `divisi_id`, ADD KEY `kantor_id` (`kantor_id`), ADD CONSTRAINT `fk_hr_karyawan_kantor` FOREIGN KEY (`kantor_id`) REFERENCES `hr_kantor` (`id`) ON DELETE SET NULL;");
    log_message("Kolom 'kantor_id' berhasil ditambahkan ke tabel hr_karyawan.");
}

$check_col_golongan_gaji = $conn_setup->query("SHOW COLUMNS FROM `hr_karyawan` LIKE 'golongan_gaji_id'");
if ($check_col_golongan_gaji && $check_col_golongan_gaji->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_karyawan` ADD COLUMN `golongan_gaji_id` INT(11) DEFAULT NULL AFTER `kantor_id`, ADD KEY `golongan_gaji_id` (`golongan_gaji_id`), ADD CONSTRAINT `fk_hr_karyawan_golongan_gaji` FOREIGN KEY (`golongan_gaji_id`) REFERENCES `hr_golongan_gaji` (`id`) ON DELETE SET NULL;");
    log_message("Kolom 'golongan_gaji_id' berhasil ditambahkan ke tabel hr_karyawan.");
}

$check_col_gg_gaji = $conn_setup->query("SHOW COLUMNS FROM `hr_golongan_gaji` LIKE 'gaji_pokok'");
if ($check_col_gg_gaji && $check_col_gg_gaji->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_golongan_gaji` ADD COLUMN `gaji_pokok` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `nama_golongan`");
    log_message("Kolom 'gaji_pokok' berhasil ditambahkan ke tabel hr_golongan_gaji.");
}

$check_col_jabatan_gaji = $conn_setup->query("SHOW COLUMNS FROM `hr_jabatan` LIKE 'gaji_pokok'");
if ($check_col_jabatan_gaji && $check_col_jabatan_gaji->num_rows > 0) {
    $conn_setup->query("ALTER TABLE `hr_jabatan` DROP COLUMN `gaji_pokok`");
    log_message("Kolom 'gaji_pokok' berhasil dihapus dari tabel hr_jabatan.");
}

$check_col_komponen_nilai = $conn_setup->query("SHOW COLUMNS FROM `hr_komponen_gaji` LIKE 'nilai_default'");
if ($check_col_komponen_nilai && $check_col_komponen_nilai->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_komponen_gaji` ADD COLUMN `nilai_default` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `jenis`");
    log_message("Kolom 'nilai_default' berhasil ditambahkan ke tabel hr_komponen_gaji.");
}

$check_col_komponen_tipe = $conn_setup->query("SHOW COLUMNS FROM `hr_komponen_gaji` LIKE 'tipe_hitung'");
if ($check_col_komponen_tipe && $check_col_komponen_tipe->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_komponen_gaji` ADD COLUMN `tipe_hitung` ENUM('bulanan','harian') NOT NULL DEFAULT 'bulanan' AFTER `jenis`");
    log_message("Kolom 'tipe_hitung' berhasil ditambahkan ke tabel hr_komponen_gaji.");
}

$check_col_komponen_tipe = $conn_setup->query("SHOW COLUMNS FROM `hr_komponen_gaji` LIKE 'tipe_hitung'");
if ($check_col_komponen_tipe && $check_col_komponen_tipe->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_komponen_gaji` ADD COLUMN `tipe_hitung` ENUM('bulanan','harian') NOT NULL DEFAULT 'bulanan' AFTER `jenis`");
    log_message("Kolom 'tipe_hitung' berhasil ditambahkan ke tabel hr_komponen_gaji.");
}

$check_col_karyawan_pajak = $conn_setup->query("SHOW COLUMNS FROM `hr_karyawan` LIKE 'npwp'");
if ($check_col_karyawan_pajak && $check_col_karyawan_pajak->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_karyawan` 
        ADD COLUMN `npwp` VARCHAR(20) DEFAULT NULL AFTER `status`,
        ADD COLUMN `status_ptkp` VARCHAR(5) DEFAULT 'TK/0' AFTER `npwp`,
        ADD COLUMN `ikut_bpjs_kes` TINYINT(1) DEFAULT 0 AFTER `status_ptkp`,
        ADD COLUMN `ikut_bpjs_tk` TINYINT(1) DEFAULT 0 AFTER `ikut_bpjs_kes`
    ");
    log_message("Kolom pajak dan BPJS berhasil ditambahkan ke tabel hr_karyawan.");
}

$check_col_karyawan_user = $conn_setup->query("SHOW COLUMNS FROM `hr_karyawan` LIKE 'user_id'");
if ($check_col_karyawan_user && $check_col_karyawan_user->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_karyawan` ADD COLUMN `user_id` INT(11) DEFAULT NULL AFTER `status`, ADD UNIQUE KEY `user_id` (`user_id`), ADD CONSTRAINT `fk_hr_karyawan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;");
    log_message("Kolom 'user_id' berhasil ditambahkan ke tabel hr_karyawan.");
}

$check_col_karyawan_kontrak = $conn_setup->query("SHOW COLUMNS FROM `hr_karyawan` LIKE 'tanggal_berakhir_kontrak'");
if ($check_col_karyawan_kontrak && $check_col_karyawan_kontrak->num_rows == 0) {
    $conn_setup->query("ALTER TABLE `hr_karyawan` ADD COLUMN `tanggal_berakhir_kontrak` DATE DEFAULT NULL AFTER `tanggal_masuk`");
    log_message("Kolom 'tanggal_berakhir_kontrak' berhasil ditambahkan ke tabel hr_karyawan.");
}

// Insert default settings for Tax & BPJS if not exists
$default_tax_settings = [
    'ptkp_tk0' => '54000000',
    'ptkp_k0' => '58500000',
    'ptkp_k1' => '63000000',
    'ptkp_k2' => '67500000',
    'ptkp_k3' => '72000000',
    'bpjs_kes_perusahaan' => '4.0', // 4%
    'bpjs_kes_karyawan' => '1.0',   // 1%
    'bpjs_tk_jht_perusahaan' => '3.7', // 3.7%
    'bpjs_tk_jht_karyawan' => '2.0',   // 2.0%
    'bpjs_tk_jp_perusahaan' => '2.0',  // 2.0%
    'bpjs_tk_jp_karyawan' => '1.0',    // 1.0%
];

foreach ($default_tax_settings as $key => $val) {
    $conn_setup->query("INSERT IGNORE INTO hr_pengaturan_pajak (setting_key, setting_value) VALUES ('$key', '$val')");
}

$conn_setup->close();

$base_path_setup = dirname($_SERVER['SCRIPT_NAME']);
$login_url = rtrim($base_path_setup, '/') . '/login';
?>
            </ul>
        </div>
        <div class="card-footer">
            <div class="alert alert-success mb-0">
                <h4 class="alert-heading">Setup Selesai!</h4>
                <p>Database telah berhasil dikonfigurasi. User default adalah <strong>admin</strong> dengan password <strong>password</strong> dan role <strong>admin</strong>.</p>
                <hr>
                <p class="mb-0"><strong>TINDAKAN PENTING:</strong> Untuk keamanan, mohon hapus file <strong>setup_db.php</strong> ini dari server Anda, lalu <a href="<?= htmlspecialchars($login_url) ?>" class="alert-link">klik di sini untuk login</a>.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>