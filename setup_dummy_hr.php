<?php
require_once 'includes/bootstrap.php';

// Pastikan hanya admin yang bisa menjalankan (opsional, bisa dikomentari saat dev)
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { die("Access Denied"); }

$conn = Database::getInstance()->getConnection();

echo "<!doctype html><html lang='en'><head><meta charset='utf-8'><title>Setup Dummy HR</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='p-5'>";
echo "<div class='card'><div class='card-header bg-primary text-white'><h3>Generating Dummy Data for HR & Payroll</h3></div><div class='card-body'><ul class='list-group'>";

function log_step($msg) {
    echo "<li class='list-group-item'>$msg <span class='float-end text-success'>OK</span></li>";
}

// Bersihkan data transaksional lama untuk memastikan data dummy yang bersih
$conn->query("SET FOREIGN_KEY_CHECKS=0;");
$conn->query("TRUNCATE TABLE hr_penggajian_komponen;");
$conn->query("TRUNCATE TABLE hr_penggajian;");
$conn->query("TRUNCATE TABLE hr_absensi;");
$conn->query("TRUNCATE TABLE hr_pengajuan_cuti;");
$conn->query("TRUNCATE TABLE hr_jatah_cuti;");
$conn->query("TRUNCATE TABLE hr_karyawan;");
$conn->query("DROP TABLE IF EXISTS hr_klaim;");
$conn->query("DROP TABLE IF EXISTS hr_jenis_klaim;");
$conn->query("DROP TABLE IF EXISTS hr_lembur;");
$conn->query("DROP TABLE IF EXISTS hr_dokumen_karyawan;");
$conn->query("DROP TABLE IF EXISTS hr_penilaian_detail;");
$conn->query("DROP TABLE IF EXISTS hr_penilaian_kinerja;");
$conn->query("DROP TABLE IF EXISTS hr_kpi_indicators;");
$conn->query("DROP TABLE IF EXISTS hr_kpi_templates;");
$conn->query("DROP TABLE IF EXISTS hr_libur_nasional;");
$conn->query("DROP TABLE IF EXISTS hr_pengumuman;");
$conn->query("DROP TABLE IF EXISTS hr_pelamar;");
$conn->query("DROP TABLE IF EXISTS hr_offboarding;");
$conn->query("DROP TABLE IF EXISTS hr_evaluasi_probation;");
$conn->query("DROP TABLE IF EXISTS hr_riwayat_gaji;");
$conn->query("DROP TABLE IF EXISTS hr_lowongan;");

// Truncate Master Tables
$conn->query("TRUNCATE TABLE hr_kantor;");
$conn->query("TRUNCATE TABLE hr_divisi;");
$conn->query("TRUNCATE TABLE hr_jadwal_kerja;");
$conn->query("TRUNCATE TABLE hr_jabatan;");
$conn->query("TRUNCATE TABLE hr_golongan_gaji;");
$conn->query("TRUNCATE TABLE hr_komponen_gaji;");
$conn->query("TRUNCATE TABLE hr_jenis_cuti;");
$conn->query("TRUNCATE TABLE hr_absensi_golongan;");
$conn->query("TRUNCATE TABLE hr_absensi_status;");

$conn->query("DELETE FROM users WHERE id > 2;"); // Hapus user dummy selain admin & user
$conn->query("SET FOREIGN_KEY_CHECKS=1;");
log_step("Semua data HR (Master & Transaksi) berhasil dibersihkan.");


// 1. Kantor
$kantors = [
    ['nama_kantor' => 'Kantor Pusat', 'jenis_kantor' => 'Pusat', 'alamat' => 'Jl. Jend. Sudirman No. Kav 1, Jakarta'],
    ['nama_kantor' => 'Cabang Bandung', 'jenis_kantor' => 'Cabang', 'alamat' => 'Jl. Asia Afrika No. 10, Bandung'],
    ['nama_kantor' => 'Cabang Surabaya', 'jenis_kantor' => 'Cabang', 'alamat' => 'Jl. Tunjungan No. 5, Surabaya'],
];
foreach ($kantors as $k) {
    $stmt = $conn->prepare("INSERT INTO hr_kantor (nama_kantor, jenis_kantor, alamat) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $k['nama_kantor'], $k['jenis_kantor'], $k['alamat']);
    $stmt->execute();
}
log_step("Data Kantor berhasil dibuat.");

// 2. Divisi
$divisis = ['IT Development', 'Human Resources', 'Finance & Accounting', 'Marketing', 'Operasional'];
foreach ($divisis as $d) {
    $stmt = $conn->prepare("INSERT INTO hr_divisi (nama_divisi) VALUES (?)");
    $stmt->bind_param("s", $d);
    $stmt->execute();
}
log_step("Data Divisi berhasil dibuat.");

// 3. Jadwal Kerja
$jadwals = [
    ['nama_jadwal' => 'Normal (08-17)', 'jam_masuk' => '08:00:00', 'jam_pulang' => '17:00:00'],
    ['nama_jadwal' => 'Shift Pagi (06-14)', 'jam_masuk' => '06:00:00', 'jam_pulang' => '14:00:00'],
    ['nama_jadwal' => 'Shift Siang (14-22)', 'jam_masuk' => '14:00:00', 'jam_pulang' => '22:00:00'],
];
foreach ($jadwals as $j) {
    $stmt = $conn->prepare("INSERT INTO hr_jadwal_kerja (nama_jadwal, jam_masuk, jam_pulang) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $j['nama_jadwal'], $j['jam_masuk'], $j['jam_pulang']);
    $stmt->execute();
}
log_step("Data Jadwal Kerja berhasil dibuat.");

// 4. Jabatan
$jabatans = [
    ['nama_jabatan' => 'Manager', 'tunjangan' => 2500000],
    ['nama_jabatan' => 'Supervisor', 'tunjangan' => 1500000],
    ['nama_jabatan' => 'Senior Staff', 'tunjangan' => 800000],
    ['nama_jabatan' => 'Staff', 'tunjangan' => 500000],
    ['nama_jabatan' => 'Admin', 'tunjangan' => 300000],
    // Jabatan Level Atas
    ['nama_jabatan' => 'Komisaris Utama', 'tunjangan' => 10000000],
    ['nama_jabatan' => 'Direktur Utama', 'tunjangan' => 8000000],
    ['nama_jabatan' => 'Direktur Keuangan', 'tunjangan' => 7000000],
    ['nama_jabatan' => 'Direktur HR', 'tunjangan' => 7000000],
    // Jabatan Level Bawah
    ['nama_jabatan' => 'Office Boy', 'tunjangan' => 150000],
];
foreach ($jabatans as $j) {
    $stmt = $conn->prepare("INSERT INTO hr_jabatan (nama_jabatan, tunjangan) VALUES (?, ?)");
    $stmt->bind_param("sd", $j['nama_jabatan'], $j['tunjangan']);
    $stmt->execute();
}
log_step("Data Jabatan berhasil dibuat.");

// 5. Golongan Gaji
$golongans = [
    ['nama_golongan' => 'Grade A', 'gaji_pokok' => 12000000, 'keterangan' => 'Executive Level'],
    ['nama_golongan' => 'Grade B', 'gaji_pokok' => 8000000, 'keterangan' => 'Managerial Level'],
    ['nama_golongan' => 'Grade C', 'gaji_pokok' => 6000000, 'keterangan' => 'Senior Level'],
    ['nama_golongan' => 'Grade D', 'gaji_pokok' => 4500000, 'keterangan' => 'Junior Level'],
];
foreach ($golongans as $g) {
    $stmt = $conn->prepare("INSERT INTO hr_golongan_gaji (nama_golongan, gaji_pokok, keterangan) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $g['nama_golongan'], $g['gaji_pokok'], $g['keterangan']);
    $stmt->execute();
}
log_step("Data Golongan Gaji berhasil dibuat.");

// 6. Komponen Gaji
$komponens = [
    ['nama_komponen' => 'Tunjangan Transport', 'jenis' => 'pendapatan', 'tipe_hitung' => 'harian', 'nilai_default' => 25000, 'is_default' => 1],
    ['nama_komponen' => 'Tunjangan Makan', 'jenis' => 'pendapatan', 'tipe_hitung' => 'harian', 'nilai_default' => 30000, 'is_default' => 1],
    ['nama_komponen' => 'BPJS Kesehatan (1%)', 'jenis' => 'potongan', 'tipe_hitung' => 'bulanan', 'nilai_default' => 100000, 'is_default' => 1],
    ['nama_komponen' => 'JHT (2%)', 'jenis' => 'potongan', 'tipe_hitung' => 'bulanan', 'nilai_default' => 200000, 'is_default' => 1],
    ['nama_komponen' => 'Bonus Project', 'jenis' => 'pendapatan', 'tipe_hitung' => 'bulanan', 'nilai_default' => 0, 'is_default' => 0],
    ['nama_komponen' => 'Potongan Kasbon', 'jenis' => 'potongan', 'tipe_hitung' => 'bulanan', 'nilai_default' => 0, 'is_default' => 0],
    ['nama_komponen' => 'Upah Lembur', 'jenis' => 'pendapatan', 'tipe_hitung' => 'lembur', 'nilai_default' => 0, 'is_default' => 1],
    ['nama_komponen' => 'Bonus KPI', 'jenis' => 'pendapatan', 'tipe_hitung' => 'bulanan', 'nilai_default' => 1000000, 'is_default' => 1],
];
foreach ($komponens as $k) {
    $stmt = $conn->prepare("INSERT INTO hr_komponen_gaji (nama_komponen, jenis, tipe_hitung, nilai_default, is_default) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdi", $k['nama_komponen'], $k['jenis'], $k['tipe_hitung'], $k['nilai_default'], $k['is_default']);
    $stmt->execute();
}
log_step("Data Komponen Gaji berhasil dibuat.");

// 7. Jenis Cuti
$jenis_cutis = [
    ['nama_jenis' => 'Cuti Tahunan', 'mengurangi_jatah_cuti' => 1],
    ['nama_jenis' => 'Cuti Sakit', 'mengurangi_jatah_cuti' => 0],
    ['nama_jenis' => 'Cuti Menikah', 'mengurangi_jatah_cuti' => 0],
    ['nama_jenis' => 'Cuti Melahirkan', 'mengurangi_jatah_cuti' => 0],
];
foreach ($jenis_cutis as $jc) {
    $stmt = $conn->prepare("INSERT INTO hr_jenis_cuti (nama_jenis, mengurangi_jatah_cuti) VALUES (?, ?)");
    $stmt->bind_param("si", $jc['nama_jenis'], $jc['mengurangi_jatah_cuti']);
    $stmt->execute();
}
log_step("Data Jenis Cuti berhasil dibuat.");

// 8. Golongan Absensi
$absensi_golongans = ['Shift Pagi', 'Shift Siang', 'Shift Malam', 'Non-Shift'];
foreach ($absensi_golongans as $ag) {
    $stmt = $conn->prepare("INSERT INTO hr_absensi_golongan (nama_golongan) VALUES (?)");
    $stmt->bind_param("s", $ag);
    $stmt->execute();
}
log_step("Data Golongan Absensi berhasil dibuat.");

// 9. Status Absensi
$absensi_statuses = [
    ['nama_status' => 'hadir', 'badge_class' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'],
    ['nama_status' => 'izin', 'badge_class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'],
    ['nama_status' => 'sakit', 'badge_class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'],
    ['nama_status' => 'alpa', 'badge_class' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'],
];
foreach ($absensi_statuses as $as) {
    $stmt = $conn->prepare("INSERT INTO hr_absensi_status (nama_status, badge_class) VALUES (?, ?)");
    $stmt->bind_param("ss", $as['nama_status'], $as['badge_class']);
    $stmt->execute();
}
log_step("Data Status Absensi berhasil dibuat.");

// 10. Role Karyawan (Ensure it exists)
$conn->query("INSERT IGNORE INTO roles (id, name, description) VALUES (4, 'Karyawan', 'Akses untuk portal karyawan self-service')");
$conn->query("INSERT IGNORE INTO role_menus (role_id, menu_key) VALUES (4, 'portal_karyawan')");
$conn->query("INSERT IGNORE INTO role_menus (role_id, menu_key) VALUES (4, 'portal_dashboard')");
$conn->query("INSERT IGNORE INTO role_menus (role_id, menu_key) VALUES (4, 'portal_profil')");
$conn->query("INSERT IGNORE INTO role_menus (role_id, menu_key) VALUES (4, 'portal_absensi')");
$conn->query("INSERT IGNORE INTO role_menus (role_id, menu_key) VALUES (4, 'portal_slip_gaji')");
log_step("Role 'Karyawan' dan akses menunya berhasil dibuat.");


// 10.b Jenis Klaim & Klaim (New Feature)
$sql_klaim_tables = "
CREATE TABLE IF NOT EXISTS `hr_jenis_klaim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(100) NOT NULL,
  `max_plafon` decimal(15,2) DEFAULT 0,
  `keterangan` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_klaim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `jenis_klaim_id` int(11) NOT NULL,
  `tanggal_klaim` date NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` text,
  `bukti_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  KEY `jenis_klaim_id` (`jenis_klaim_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->multi_query($sql_klaim_tables);
while ($conn->more_results() && $conn->next_result()) {;}

$jenis_klaims = [
    ['nama' => 'Kesehatan (Rawat Jalan)', 'plafon' => 5000000],
    ['nama' => 'Transportasi / Bensin', 'plafon' => 1000000],
    ['nama' => 'Kacamata', 'plafon' => 2000000],
    ['nama' => 'Entertainment', 'plafon' => 0], // 0 = Unlimited/Approval based
];
foreach ($jenis_klaims as $jk) {
    $conn->query("INSERT INTO hr_jenis_klaim (nama_jenis, max_plafon) VALUES ('{$jk['nama']}', {$jk['plafon']})");
}
log_step("Tabel dan Data Jenis Klaim berhasil dibuat.");

// 10.c Lembur (New Feature)
$sql_lembur_table = "
CREATE TABLE IF NOT EXISTS `hr_lembur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `keterangan` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($sql_lembur_table);
log_step("Tabel Lembur berhasil dibuat.");

// 10.d Dokumen Karyawan (New Feature)
$sql_dokumen_table = "
CREATE TABLE IF NOT EXISTS `hr_dokumen_karyawan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `jenis_dokumen` varchar(50) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($sql_dokumen_table);
log_step("Tabel Dokumen Karyawan berhasil dibuat.");

// 10.e KPI (New Feature)
$sql_kpi_tables = "
CREATE TABLE IF NOT EXISTS `hr_kpi_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_template` varchar(100) NOT NULL,
  `keterangan` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_kpi_indicators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `indikator` varchar(255) NOT NULL,
  `bobot` int(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_penilaian_kinerja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `periode_bulan` int(2) NOT NULL,
  `periode_tahun` int(4) NOT NULL,
  `tanggal_penilaian` date NOT NULL,
  `penilai_id` int(11) NOT NULL,
  `total_skor` decimal(5,2) DEFAULT 0,
  `catatan` text,
  `status` enum('draft','final') DEFAULT 'draft',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_penilaian_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `penilaian_id` int(11) NOT NULL,
  `indikator_id` int(11) NOT NULL,
  `skor` int(3) NOT NULL,
  `komentar` text,
  PRIMARY KEY (`id`),
  KEY `penilaian_id` (`penilaian_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->multi_query($sql_kpi_tables);
while ($conn->more_results() && $conn->next_result()) {;}
log_step("Tabel KPI berhasil dibuat.");

// Dummy KPI Template
$conn->query("INSERT INTO hr_kpi_templates (nama_template, keterangan) VALUES ('Evaluasi Staff Umum', 'Template standar untuk staff')");
$template_id = $conn->insert_id;
$conn->query("INSERT INTO hr_kpi_indicators (template_id, indikator, bobot) VALUES ($template_id, 'Kedisiplinan & Kehadiran', 20)");
$conn->query("INSERT INTO hr_kpi_indicators (template_id, indikator, bobot) VALUES ($template_id, 'Kualitas Pekerjaan', 30)");
$conn->query("INSERT INTO hr_kpi_indicators (template_id, indikator, bobot) VALUES ($template_id, 'Kerjasama Tim', 20)");
$conn->query("INSERT INTO hr_kpi_indicators (template_id, indikator, bobot) VALUES ($template_id, 'Inisiatif', 15)");
$conn->query("INSERT INTO hr_kpi_indicators (template_id, indikator, bobot) VALUES ($template_id, 'Pencapaian Target', 15)");
log_step("Data Dummy KPI Template berhasil dibuat.");

// 10.f Libur Nasional (New Feature)
$sql_libur_table = "
CREATE TABLE IF NOT EXISTS `hr_libur_nasional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `is_cuti_bersama` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tanggal` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($sql_libur_table);
$current_year = date('Y');
$conn->query("INSERT INTO hr_libur_nasional (tanggal, keterangan) VALUES ('$current_year-01-01', 'Tahun Baru Masehi')");
$conn->query("INSERT INTO hr_libur_nasional (tanggal, keterangan) VALUES ('$current_year-08-17', 'Hari Kemerdekaan RI')");
$conn->query("INSERT INTO hr_libur_nasional (tanggal, keterangan) VALUES ('$current_year-12-25', 'Hari Raya Natal')");
log_step("Tabel dan Data Dummy Libur Nasional berhasil dibuat.");

// 10.g Pengumuman Internal (New Feature)
$sql_pengumuman_table = "
CREATE TABLE IF NOT EXISTS `hr_pengumuman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `lampiran_file` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($sql_pengumuman_table);
$conn->query("INSERT INTO hr_pengumuman (judul, isi, created_by, is_published, created_at) VALUES ('Libur Cuti Bersama Idul Fitri', 'Diberitahukan kepada seluruh karyawan bahwa akan ada libur cuti bersama dalam rangka Hari Raya Idul Fitri. Detail jadwal akan diinformasikan lebih lanjut.', 1, 1, NOW() - INTERVAL 1 DAY)");
$conn->query("INSERT INTO hr_pengumuman (judul, isi, created_by, is_published) VALUES ('Update Kebijakan Work From Home', 'Akan ada pembaruan kebijakan WFH yang akan diumumkan minggu depan.', 1, 0)");
log_step("Tabel dan Data Dummy Pengumuman Internal berhasil dibuat.");

// 10.h Manajemen Rekrutmen (New Feature)
$sql_rekrutmen_tables = "
CREATE TABLE IF NOT EXISTS `hr_lowongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `jabatan_id` int(11) DEFAULT NULL,
  `divisi_id` int(11) DEFAULT NULL,
  `kuota` int(11) DEFAULT 1,
  `deskripsi` text,
  `kualifikasi` text,
  `status` enum('buka','tutup') DEFAULT 'buka',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_pelamar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lowongan_id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `pendidikan_terakhir` varchar(50) DEFAULT NULL,
  `file_cv` varchar(255) DEFAULT NULL,
  `file_spk_signed` VARCHAR(255) DEFAULT NULL,
  `status` enum('applied','screening','interview','offering','hired','rejected') DEFAULT 'applied',
  `catatan` text,
  `tanggal_lamar` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lowongan_id` (`lowongan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->multi_query($sql_rekrutmen_tables);
while ($conn->more_results() && $conn->next_result()) {;}
$conn->query("INSERT INTO hr_lowongan (judul, jabatan_id, divisi_id, kuota, deskripsi, kualifikasi, tanggal_mulai) VALUES ('Staff IT Support', 4, 1, 2, 'Bertanggung jawab atas hardware dan software.', 'Min. D3 Informatika', CURDATE())");
log_step("Tabel dan Data Dummy Rekrutmen berhasil dibuat.");

// Tambahan Data Dummy Rekrutmen
$conn->query("INSERT INTO hr_lowongan (judul, jabatan_id, divisi_id, kuota, deskripsi, kualifikasi, status, tanggal_mulai) VALUES ('Marketing Executive', 4, 4, 3, 'Mencari klien baru dan maintain klien lama.', 'Min. S1 Semua Jurusan, Komunikatif', 'buka', CURDATE())");
$lowongan_it_id = $conn->query("SELECT id FROM hr_lowongan WHERE judul = 'Staff IT Support'")->fetch_assoc()['id'];
$conn->query("INSERT INTO hr_pelamar (lowongan_id, nama_lengkap, email, no_hp, pendidikan_terakhir, status, catatan, tanggal_lamar) VALUES ($lowongan_it_id, 'Doni Pratama', 'doni@example.com', '081234567890', 'S1 Teknik Informatika', 'interview', 'Kandidat potensial, skill teknis bagus.', NOW())");
$conn->query("INSERT INTO hr_pelamar (lowongan_id, nama_lengkap, email, no_hp, pendidikan_terakhir, status, catatan, tanggal_lamar) VALUES ($lowongan_it_id, 'Eka Saputra', 'eka@example.com', '089876543210', 'D3 Manajemen Informatika', 'applied', 'Baru lulus.', NOW())");
log_step("Data Dummy Pelamar berhasil dibuat.");

// 10.i Offboarding (New Feature)
$sql_offboarding_table = "
CREATE TABLE IF NOT EXISTS `hr_offboarding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `tipe` enum('resign','terminate') NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_efektif` date NOT NULL COMMENT 'Last working day',
  `alasan` text,
  `checklist_data` json DEFAULT NULL,
  `status` enum('proses','selesai') NOT NULL DEFAULT 'proses',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karyawan_id` (`karyawan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($sql_offboarding_table);
log_step("Tabel Offboarding berhasil dibuat.");

// 10.j Evaluasi Probation (New Feature)
$sql_evaluasi_probation = "
CREATE TABLE IF NOT EXISTS `hr_evaluasi_probation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `tanggal_evaluasi` date NOT NULL,
  `penilai_id` int(11) NOT NULL,
  `skor_teknis` int(3) NOT NULL COMMENT 'Skor 1-100',
  `skor_budaya` int(3) NOT NULL COMMENT 'Skor 1-100',
  `rekomendasi` enum('angkat_tetap','perpanjang_probation','terminasi') NOT NULL,
  `catatan` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($sql_evaluasi_probation);
log_step("Tabel Evaluasi Probation berhasil dibuat.");

// 10.k Riwayat Gaji (New Feature)
$sql_riwayat_gaji = "
CREATE TABLE IF NOT EXISTS `hr_riwayat_gaji` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `karyawan_id` int(11) NOT NULL,
  `tanggal_perubahan` date NOT NULL,
  `gaji_lama` decimal(15,2) NOT NULL,
  `gaji_baru` decimal(15,2) NOT NULL,
  `keterangan` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$conn->query($sql_riwayat_gaji);
log_step("Tabel Riwayat Gaji berhasil dibuat.");

// 11. Karyawan
// Ambil ID dari tabel master untuk relasi acak
$kantor_ids = $conn->query("SELECT id FROM hr_kantor")->fetch_all(MYSQLI_ASSOC);
$divisi_ids = $conn->query("SELECT id FROM hr_divisi")->fetch_all(MYSQLI_ASSOC);
$jadwal_ids = $conn->query("SELECT id FROM hr_jadwal_kerja")->fetch_all(MYSQLI_ASSOC);
$jabatan_ids = $conn->query("SELECT id FROM hr_jabatan")->fetch_all(MYSQLI_ASSOC);
$golongan_ids = $conn->query("SELECT id FROM hr_golongan_gaji")->fetch_all(MYSQLI_ASSOC);

// Helper untuk mendapatkan ID dari nama
function getIdByName($array, $key, $value) {
    foreach ($array as $item) {
        if ($item[$key] == $value) return $item['id'];
    }
    return null;
}

$jabatan_map = $conn->query("SELECT id, nama_jabatan FROM hr_jabatan")->fetch_all(MYSQLI_ASSOC);
$divisi_map = $conn->query("SELECT id, nama_divisi FROM hr_divisi")->fetch_all(MYSQLI_ASSOC);

// Struktur Organisasi
$structure = [
    ['nip' => 'KOM001', 'nama' => 'Haryono Subianto', 'jabatan' => 'Komisaris Utama', 'divisi' => null, 'atasan_nip' => null],
    ['nip' => 'DIR001', 'nama' => 'Ahmad Zulkifli', 'jabatan' => 'Direktur Utama', 'divisi' => null, 'atasan_nip' => 'KOM001'],
    ['nip' => 'DIR002', 'nama' => 'Kartika Sari', 'jabatan' => 'Direktur Keuangan', 'divisi' => 'Finance & Accounting', 'atasan_nip' => 'DIR001'],
    ['nip' => 'DIR003', 'nama' => 'Bambang Wijoyo', 'jabatan' => 'Direktur HR', 'divisi' => 'Human Resources', 'atasan_nip' => 'DIR001'],
    
    ['nip' => 'MGR001', 'nama' => 'Rina Marlina', 'jabatan' => 'Manager', 'divisi' => 'IT Development', 'atasan_nip' => 'DIR001'],
    ['nip' => 'MGR002', 'nama' => 'Andi Wijaya', 'jabatan' => 'Manager', 'divisi' => 'Marketing', 'atasan_nip' => 'DIR001'],

    ['nip' => 'SPV001', 'nama' => 'Siti Aminah', 'jabatan' => 'Supervisor', 'divisi' => 'Finance & Accounting', 'atasan_nip' => 'DIR002'],
    ['nip' => 'SPV002', 'nama' => 'Rudi Hartono', 'jabatan' => 'Supervisor', 'divisi' => 'Human Resources', 'atasan_nip' => 'DIR003'],

    ['nip' => 'STF001', 'nama' => 'Budi Santoso', 'jabatan' => 'Senior Staff', 'divisi' => 'IT Development', 'atasan_nip' => 'MGR001'],
    ['nip' => 'STF002', 'nama' => 'Dewi Sartika', 'jabatan' => 'Staff', 'divisi' => 'IT Development', 'atasan_nip' => 'MGR001'],
    ['nip' => 'STF003', 'nama' => 'Eko Prasetyo', 'jabatan' => 'Staff', 'divisi' => 'Marketing', 'atasan_nip' => 'MGR002'],
    ['nip' => 'STF004', 'nama' => 'Fitriani', 'jabatan' => 'Staff', 'divisi' => 'Finance & Accounting', 'atasan_nip' => 'SPV001'],
    ['nip' => 'STF005', 'nama' => 'Gunawan', 'jabatan' => 'Staff', 'divisi' => 'Human Resources', 'atasan_nip' => 'SPV002'],

    ['nip' => 'ADM001', 'nama' => 'Hilda', 'jabatan' => 'Admin', 'divisi' => 'Operasional', 'atasan_nip' => 'MGR002'],
    ['nip' => 'OB001', 'nama' => 'Iwan', 'jabatan' => 'Office Boy', 'divisi' => 'Operasional', 'atasan_nip' => 'MGR002'],
];

$employee_ids = []; // Untuk menyimpan relasi nip => id

foreach ($structure as $i => $k) {
    $jabatan_id = getIdByName($jabatan_map, 'nama_jabatan', $k['jabatan']);
    $divisi_id = $k['divisi'] ? getIdByName($divisi_map, 'nama_divisi', $k['divisi']) : null;
    
    // Atasan ID diambil dari array $employee_ids yang sudah di-insert sebelumnya
    $atasan_id = isset($k['atasan_nip']) && isset($employee_ids[$k['atasan_nip']]) ? $employee_ids[$k['atasan_nip']] : null;

    // Data dummy lainnya
    $jadwal_id = $jadwal_ids[0]['id']; // Semua pakai jadwal normal
    $kantor_id = $kantor_ids[$i % count($kantor_ids)]['id'];
    $golongan_id = $golongan_ids[$i % count($golongan_ids)]['id'];
    $tgl_masuk = date('Y-m-d', strtotime("-" . rand(1, 5) . " years"));
    $tgl_kontrak = date('Y-m-d', strtotime("+" . rand(3, 24) . " months"));

    $stmt = $conn->prepare("INSERT INTO hr_karyawan (nip, nama_lengkap, jabatan_id, atasan_id, jadwal_kerja_id, divisi_id, kantor_id, golongan_gaji_id, tanggal_masuk, tanggal_berakhir_kontrak, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')");
    $stmt->bind_param("ssiiiiisss", $k['nip'], $k['nama'], $jabatan_id, $atasan_id, $jadwal_id, $divisi_id, $kantor_id, $golongan_id, $tgl_masuk, $tgl_kontrak);
    
    if ($stmt->execute()) {
        $employee_ids[$k['nip']] = $conn->insert_id;
        // Insert initial job history
        if ($jabatan_id) {
            $stmt_hist = $conn->prepare("INSERT INTO hr_riwayat_jabatan (karyawan_id, jabatan_id, tanggal_mulai) VALUES (?, ?, ?)");
            $stmt_hist->bind_param("iis", $employee_ids[$k['nip']], $jabatan_id, $tgl_masuk);
            $stmt_hist->execute();
        }

        // Tambahkan Dummy Riwayat Jabatan & Gaji untuk beberapa karyawan (Budi & Siti)
        if ($k['nip'] === 'STF001') { // Budi Santoso
            // Riwayat Jabatan Lama (Staff -> Senior Staff)
            $tgl_promosi = date('Y-m-d', strtotime("-6 months"));
            $jabatan_lama_id = getIdByName($jabatan_map, 'nama_jabatan', 'Staff');
            $conn->query("INSERT INTO hr_riwayat_jabatan (karyawan_id, jabatan_id, tanggal_mulai, tanggal_selesai) VALUES ({$employee_ids[$k['nip']]}, $jabatan_lama_id, '".date('Y-m-d', strtotime("-2 years"))."', '$tgl_promosi')");
            
            // Riwayat Gaji
            $conn->query("INSERT INTO hr_riwayat_gaji (karyawan_id, tanggal_perubahan, gaji_lama, gaji_baru, keterangan) VALUES ({$employee_ids[$k['nip']]}, '$tgl_promosi', 4500000, 6000000, 'Promosi Jabatan')");
            $conn->query("INSERT INTO hr_riwayat_gaji (karyawan_id, tanggal_perubahan, gaji_lama, gaji_baru, keterangan) VALUES ({$employee_ids[$k['nip']]}, '".date('Y-m-d', strtotime("-1 year"))."', 4000000, 4500000, 'Kenaikan Berkala')");
        }
        elseif ($k['nip'] === 'SPV001') { // Siti Aminah
            // Riwayat Gaji
            $conn->query("INSERT INTO hr_riwayat_gaji (karyawan_id, tanggal_perubahan, gaji_lama, gaji_baru, keterangan) VALUES ({$employee_ids[$k['nip']]}, '".date('Y-m-d', strtotime("-3 months"))."', 7500000, 8000000, 'Penyesuaian Kinerja')");
        }


    } else {
        echo "Gagal insert {$k['nama']}: " . $stmt->error . "<br>";
    }
}
log_step("Data Karyawan berjenjang berhasil dibuat (" . count($structure) . " orang).");

// Update struktur tabel untuk mendukung status probation dan kontrak
$conn->query("ALTER TABLE `hr_karyawan` MODIFY COLUMN `status` ENUM('aktif','nonaktif','probation','kontrak') DEFAULT 'aktif'");

// Tambahkan Karyawan Probation (Masa Percobaan Hampir Habis)
$tgl_masuk_probation = date('Y-m-d', strtotime("-80 days")); // Masuk 80 hari lalu, probation 90 hari (sisa 10 hari)
$stmt = $conn->prepare("INSERT INTO hr_karyawan (nip, nama_lengkap, jabatan_id, divisi_id, kantor_id, golongan_gaji_id, tanggal_masuk, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'probation')");
$nip_prob = 'PROB001'; $nama_prob = 'Joko Probation';
$stmt->bind_param("ssiiiis", $nip_prob, $nama_prob, $jabatan_ids[4]['id'], $divisi_ids[0]['id'], $kantor_ids[0]['id'], $golongan_ids[3]['id'], $tgl_masuk_probation);
$stmt->execute(); 
log_step("Data Karyawan Probation berhasil dibuat.");

// Tambahkan data dummy evaluasi probation untuk karyawan probation
$dina_probation_id = $conn->insert_id;
$admin_user_id = 1; // Asumsi admin yang menilai
$conn->query("INSERT INTO hr_evaluasi_probation (karyawan_id, tanggal_evaluasi, penilai_id, skor_teknis, skor_budaya, rekomendasi, catatan) VALUES ($dina_probation_id, '".date('Y-m-d', strtotime('-60 days'))."', $admin_user_id, 75, 80, 'perpanjang_probation', 'Kinerja teknis perlu ditingkatkan sedikit lagi.')");
log_step("Data Dummy Evaluasi Probation berhasil dibuat.");

// Buat user untuk Budi Santoso dan link ke data karyawan
$budi_nip = 'STF001';
$budi_karyawan_id = $conn->query("SELECT id FROM hr_karyawan WHERE nip = '$budi_nip'")->fetch_assoc()['id'];
$budi_username = 'budi';
$budi_password = password_hash('password', PASSWORD_DEFAULT);
$budi_role_id = 4; // Role Karyawan

$stmt_user = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, role_id) VALUES (?, ?, 'Budi Santoso', ?)");
$stmt_user->bind_param("ssi", $budi_username, $budi_password, $budi_role_id);
$stmt_user->execute();
$budi_user_id = $conn->insert_id;
$conn->query("UPDATE hr_karyawan SET user_id = $budi_user_id WHERE id = $budi_karyawan_id");
log_step("User 'budi' (password: password) berhasil dibuat dan di-link ke data karyawan.");

$karyawan_list_for_jatah = $conn->query("SELECT id FROM hr_karyawan")->fetch_all(MYSQLI_ASSOC);
$current_year = date('Y');
foreach ($karyawan_list_for_jatah as $k_jatah) {
    $conn->query("INSERT INTO hr_jatah_cuti (karyawan_id, tahun, jatah_awal, sisa_jatah) VALUES ({$k_jatah['id']}, $current_year, 12, 12)");
}
log_step("Data Jatah Cuti awal tahun berhasil dibuat untuk semua karyawan.");

// 11.b Dummy Dokumen Karyawan
$budi_id = $conn->query("SELECT id FROM hr_karyawan WHERE nip = 'STF001'")->fetch_assoc()['id'];
$siti_id = $conn->query("SELECT id FROM hr_karyawan WHERE nip = 'SPV001'")->fetch_assoc()['id'];
$conn->query("INSERT INTO hr_dokumen_karyawan (karyawan_id, jenis_dokumen, nama_file, path_file) VALUES ($budi_id, 'KTP', 'ktp_budi.pdf', 'dummy/path')");
$conn->query("INSERT INTO hr_dokumen_karyawan (karyawan_id, jenis_dokumen, nama_file, path_file, tanggal_kadaluarsa) VALUES ($budi_id, 'Kontrak Kerja', 'kontrak_budi_2023.pdf', 'dummy/path', '$tgl_kontrak')");
$conn->query("INSERT INTO hr_dokumen_karyawan (karyawan_id, jenis_dokumen, nama_file, path_file) VALUES ($siti_id, 'Ijazah', 'ijazah_s1_siti.pdf', 'dummy/path')");
log_step("Data Dummy Dokumen Karyawan berhasil dibuat.");

// 11.c Dummy Offboarding
$rudi_id = $conn->query("SELECT id FROM hr_karyawan WHERE nip = 'SPV002'")->fetch_assoc()['id'];
$conn->query("INSERT INTO hr_offboarding (karyawan_id, tipe, tanggal_pengajuan, tanggal_efektif, alasan, status) VALUES ($rudi_id, 'resign', '".date('Y-m-d', strtotime('-15 days'))."', '".date('Y-m-d', strtotime('+15 days'))."', 'Mendapat tawaran di perusahaan lain.', 'proses')");

// Tambahan Data Dummy Offboarding (Selesai)
$andi_id = $conn->query("SELECT id FROM hr_karyawan WHERE nip = 'MGR002'")->fetch_assoc()['id'];
$conn->query("INSERT INTO hr_offboarding (karyawan_id, tipe, tanggal_pengajuan, tanggal_efektif, alasan, status) VALUES ($andi_id, 'terminate', '".date('Y-m-d', strtotime('-45 days'))."', '".date('Y-m-d', strtotime('-15 days'))."', 'Pelanggaran kontrak berat.', 'selesai')");
log_step("Data Dummy Offboarding berhasil dibuat.");

// 12. Pengajuan Cuti & Absensi
$karyawan_list = $conn->query("SELECT id FROM hr_karyawan")->fetch_all(MYSQLI_ASSOC);
$jenis_cuti_ids = $conn->query("SELECT id, mengurangi_jatah_cuti FROM hr_jenis_cuti")->fetch_all(MYSQLI_ASSOC);
$jenis_cuti_tahunan_id = 0;
foreach($jenis_cuti_ids as $jc) {
    if ($jc['mengurangi_jatah_cuti'] == 1) {
        $jenis_cuti_tahunan_id = $jc['id'];
        break;
    }
}

// Pengajuan 1: Cuti Tahunan (Approved)
$karyawan_1 = $karyawan_list[0]['id'];
$tgl_mulai_1 = date('Y-m-d', strtotime("-5 days"));
$tgl_selesai_1 = date('Y-m-d', strtotime("-3 days"));
$jumlah_hari_1 = 3;
$conn->query("INSERT INTO hr_pengajuan_cuti (karyawan_id, jenis_cuti_id, tanggal_mulai, tanggal_selesai, jumlah_hari, keterangan, status, approved_at) VALUES ($karyawan_1, $jenis_cuti_tahunan_id, '$tgl_mulai_1', '$tgl_selesai_1', $jumlah_hari_1, 'Liburan keluarga', 'approved', NOW())");
// Update jatah
$conn->query("UPDATE hr_jatah_cuti SET sisa_jatah = sisa_jatah - $jumlah_hari_1 WHERE karyawan_id = $karyawan_1 AND tahun = ".date('Y'));

// Pengajuan 2: Cuti Sakit (Approved)
$karyawan_2 = $karyawan_list[1]['id'];
$tgl_mulai_2 = date('Y-m-d', strtotime("-2 days"));
$tgl_selesai_2 = date('Y-m-d', strtotime("-2 days"));
$jumlah_hari_2 = 1;
$jenis_cuti_sakit_id = $jenis_cuti_ids[1]['id'];
$conn->query("INSERT INTO hr_pengajuan_cuti (karyawan_id, jenis_cuti_id, tanggal_mulai, tanggal_selesai, jumlah_hari, keterangan, status, approved_at) VALUES ($karyawan_2, $jenis_cuti_sakit_id, '$tgl_mulai_2', '$tgl_selesai_2', $jumlah_hari_2, 'Demam, surat dokter terlampir', 'approved', NOW())");

// Pengajuan 3: Cuti Tahunan (Pending)
$karyawan_3 = $karyawan_list[2]['id'];
$tgl_mulai_3 = date('Y-m-d', strtotime("+10 days"));
$tgl_selesai_3 = date('Y-m-d', strtotime("+12 days"));
$jumlah_hari_3 = 3;
$conn->query("INSERT INTO hr_pengajuan_cuti (karyawan_id, jenis_cuti_id, tanggal_mulai, tanggal_selesai, jumlah_hari, keterangan, status) VALUES ($karyawan_3, $jenis_cuti_tahunan_id, '$tgl_mulai_3', '$tgl_selesai_3', $jumlah_hari_3, 'Acara keluarga di luar kota', 'pending')");

log_step("Data Pengajuan Cuti berhasil dibuat.");

// Generate Absensi berdasarkan cuti yang disetujui & hari biasa
$approved_leaves = $conn->query("SELECT * FROM hr_pengajuan_cuti WHERE status = 'approved'")->fetch_all(MYSQLI_ASSOC);
$leave_dates_by_employee = [];
foreach ($approved_leaves as $leave) {
    $start = new DateTime($leave['tanggal_mulai']);
    $end = new DateTime($leave['tanggal_selesai']);
    $end->modify('+1 day');
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($start, $interval, $end);
    foreach ($dateRange as $date) {
        if ($date->format('N') < 6) { // Hanya hari kerja
            $leave_dates_by_employee[$leave['karyawan_id']][$date->format('Y-m-d')] = $leave['keterangan'];
        }
    }
}

// Generate absensi untuk 14 hari terakhir
foreach ($karyawan_list as $k) {
    for ($i = 13; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        if (date('N', strtotime($date)) >= 6) continue; // Skip weekends

        $karyawan_id = $k['id'];
        $status = 'hadir';
        $jam_masuk = null;
        $jam_keluar = null;
        $keterangan = '';
        $golongan_absen = 'Non-Shift';

        // Cek apakah hari ini ada jadwal cuti
        if (isset($leave_dates_by_employee[$karyawan_id][$date])) {
            $status = 'izin'; // Di sistem, cuti dianggap 'izin' di tabel absensi
            $keterangan = 'Cuti Disetujui: ' . $leave_dates_by_employee[$karyawan_id][$date];
        } else {
            $status = 'hadir';
            $jam_masuk = date('H:i:s', strtotime('07:45:00') + rand(0, 1800)); // 07:45 - 08:15
            $jam_keluar = date('H:i:s', strtotime('17:00:00') + rand(0, 3600)); // 17:00 - 18:00
        }

        $stmt = $conn->prepare("INSERT INTO hr_absensi (karyawan_id, tanggal, golongan, jam_masuk, jam_keluar, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $karyawan_id, $date, $golongan_absen, $jam_masuk, $jam_keluar, $status, $keterangan);
        $stmt->execute();
    }
}
log_step("Data Absensi berhasil dibuat (termasuk dari cuti yang disetujui).");

// 12.b Dummy Data Klaim
$jk_ids = $conn->query("SELECT id FROM hr_jenis_klaim")->fetch_all(MYSQLI_ASSOC);
$karyawan_id_sample = $karyawan_list[0]['id']; // Budi
$jk_id_sample = $jk_ids[0]['id']; // Kesehatan

// Klaim Budi (Pending)
$conn->query("INSERT INTO hr_klaim (karyawan_id, jenis_klaim_id, tanggal_klaim, jumlah, keterangan, status) 
              VALUES ($karyawan_id_sample, $jk_id_sample, '".date('Y-m-d', strtotime("-10 days"))."', 150000, 'Obat Flu', 'pending')");

// Klaim Siti (Approved)
$siti_id = $karyawan_list[1]['id'];
$jk_transport_id = $jk_ids[1]['id'];
$conn->query("INSERT INTO hr_klaim (karyawan_id, jenis_klaim_id, tanggal_klaim, jumlah, keterangan, status, approved_by, approved_at) 
              VALUES ($siti_id, $jk_transport_id, '".date('Y-m-d', strtotime("-8 days"))."', 75000, 'Bensin perjalanan dinas', 'approved', 1, NOW())");

// Klaim Rudi (Rejected)
$rudi_id = $karyawan_list[2]['id'];
$conn->query("INSERT INTO hr_klaim (karyawan_id, jenis_klaim_id, tanggal_klaim, jumlah, keterangan, status, approved_by, approved_at) 
              VALUES ($rudi_id, $jk_id_sample, '".date('Y-m-d', strtotime("-5 days"))."', 250000, 'Klaim tanpa struk', 'rejected', 1, NOW())");

log_step("Data Dummy Klaim berhasil dibuat.");

// 12.c Dummy Data Lembur
$tgl_lembur = date('Y-m-d', strtotime("-3 days"));
$conn->query("INSERT INTO hr_lembur (karyawan_id, tanggal, jam_mulai, jam_selesai, keterangan, status) 
              VALUES ($karyawan_id_sample, '$tgl_lembur', '17:00:00', '20:00:00', 'Menyelesaikan laporan bulanan', 'approved')");
$conn->query("INSERT INTO hr_lembur (karyawan_id, tanggal, jam_mulai, jam_selesai, keterangan, status) 
              VALUES ($karyawan_id_sample, CURDATE(), '17:00:00', '19:00:00', 'Meeting dengan klien', 'pending')");
log_step("Data Dummy Lembur berhasil dibuat.");

// 13. Penggajian (Bulan Lalu)
// Kita gunakan API handler logic secara manual di sini untuk simulasi
$prev_month = date('n', strtotime("first day of last month"));
$prev_year = date('Y', strtotime("first day of last month"));

// 12.d Dummy Data Penilaian KPI (untuk bulan lalu)
$admin_user_id = 1;
$template_id_kpi = $conn->query("SELECT id FROM hr_kpi_templates LIMIT 1")->fetch_assoc()['id'];
$indicators_kpi = $conn->query("SELECT id, bobot FROM hr_kpi_indicators WHERE template_id = $template_id_kpi")->fetch_all(MYSQLI_ASSOC);

// Penilaian untuk Budi
$skor_budi = [85, 90, 80, 75, 95]; // Skor untuk 5 indikator
$total_skor_budi = 0;
foreach($indicators_kpi as $idx => $ind) {
    $total_skor_budi += $skor_budi[$idx] * ($ind['bobot'] / 100);
}
$conn->query("INSERT INTO hr_penilaian_kinerja (karyawan_id, template_id, periode_bulan, periode_tahun, tanggal_penilaian, penilai_id, total_skor, catatan, status) VALUES ($budi_id, $template_id_kpi, $prev_month, $prev_year, '$prev_year-$prev_month-28', $admin_user_id, $total_skor_budi, 'Kinerja Budi sangat baik bulan ini.', 'final')");
$penilaian_id_budi = $conn->insert_id;
foreach($indicators_kpi as $idx => $ind) {
    $conn->query("INSERT INTO hr_penilaian_detail (penilaian_id, indikator_id, skor) VALUES ($penilaian_id_budi, {$ind['id']}, {$skor_budi[$idx]})");
}

// Penilaian untuk Siti
$skor_siti = [90, 85, 88, 92, 80];
$total_skor_siti = 0;
foreach($indicators_kpi as $idx => $ind) { $total_skor_siti += $skor_siti[$idx] * ($ind['bobot'] / 100); }
$conn->query("INSERT INTO hr_penilaian_kinerja (karyawan_id, template_id, periode_bulan, periode_tahun, tanggal_penilaian, penilai_id, total_skor, catatan, status) VALUES ($siti_id, $template_id_kpi, $prev_month, $prev_year, '$prev_year-$prev_month-28', $admin_user_id, $total_skor_siti, 'Siti menunjukkan peningkatan yang signifikan.', 'final')");
$penilaian_id_siti = $conn->insert_id;
foreach($indicators_kpi as $idx => $ind) { $conn->query("INSERT INTO hr_penilaian_detail (penilaian_id, indikator_id, skor) VALUES ($penilaian_id_siti, {$ind['id']}, {$skor_siti[$idx]})"); }
log_step("Data Dummy Penilaian KPI berhasil dibuat.");

// Ambil data karyawan lengkap untuk gaji
$sql_karyawan_gaji = "SELECT k.id, gg.gaji_pokok, j.tunjangan FROM hr_karyawan k LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id WHERE k.status = 'aktif'";
$res_karyawan_gaji = $conn->query($sql_karyawan_gaji);

// Ambil komponen default
$sql_komponen_default = "SELECT id, nama_komponen, jenis, tipe_hitung, nilai_default FROM hr_komponen_gaji WHERE is_default = 1";
$res_komponen_default = $conn->query($sql_komponen_default);
$default_components = $res_komponen_default->fetch_all(MYSQLI_ASSOC);

$stmt_insert_payroll = $conn->prepare("INSERT INTO hr_penggajian (karyawan_id, periode_bulan, periode_tahun, gaji_pokok, tunjangan, potongan, total_gaji, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'final')");
$stmt_insert_payroll_detail = $conn->prepare("INSERT INTO hr_penggajian_komponen (penggajian_id, komponen_id, nama_komponen, jenis, jumlah) VALUES (?, ?, ?, ?, ?)");

while ($row = $res_karyawan_gaji->fetch_assoc()) {
    // Get attendance for the previous month
    $stmt_kehadiran = $conn->prepare("SELECT COUNT(id) as jumlah_hadir FROM hr_absensi WHERE karyawan_id = ? AND status = 'hadir' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
    $stmt_kehadiran->bind_param("iii", $row['id'], $prev_month, $prev_year);
    $stmt_kehadiran->execute();
    $jumlah_hari_hadir = $stmt_kehadiran->get_result()->fetch_assoc()['jumlah_hadir'] ?? 0;
    $stmt_kehadiran->close();

    $gaji_pokok = $row['gaji_pokok'] ?? 0;
    $tunjangan_jabatan = $row['tunjangan'] ?? 0;
    $total_tunjangan_komponen = 0;
    $total_potongan_komponen = 0;
    $komponen_details_to_insert = [];

    foreach ($default_components as $comp) {
        $jumlah_komponen = $comp['nilai_default'];
        if ($comp['tipe_hitung'] === 'harian') {
            $jumlah_komponen = $comp['nilai_default'] * $jumlah_hari_hadir;
        }
        if ($comp['jenis'] === 'pendapatan') $total_tunjangan_komponen += $jumlah_komponen;
        else $total_potongan_komponen += $jumlah_komponen;
        $komponen_details_to_insert[] = ['id' => $comp['id'], 'nama' => $comp['nama_komponen'], 'jenis' => $comp['jenis'], 'jumlah' => $jumlah_komponen];
    }
    $total_tunjangan = $tunjangan_jabatan + $total_tunjangan_komponen;
    $total_gaji = $gaji_pokok + $total_tunjangan - $total_potongan_komponen;

    $stmt_insert_payroll->bind_param("iiidddd", $row['id'], $prev_month, $prev_year, $gaji_pokok, $total_tunjangan, $total_potongan_komponen, $total_gaji);
    if ($stmt_insert_payroll->execute()) {
        $penggajian_id = $conn->insert_id;
        if ($tunjangan_jabatan > 0) {
            $jenis = 'pendapatan'; $nama = 'Tunjangan Jabatan'; $kid = 0;
            $stmt_insert_payroll_detail->bind_param("iissd", $penggajian_id, $kid, $nama, $jenis, $tunjangan_jabatan);
            $stmt_insert_payroll_detail->execute();
        }
        foreach ($komponen_details_to_insert as $comp) {
            $stmt_insert_payroll_detail->bind_param("iissd", $penggajian_id, $comp['id'], $comp['nama'], $comp['jenis'], $comp['jumlah']);
            $stmt_insert_payroll_detail->execute();
        }
    }
}
log_step("Data Penggajian berhasil dibuat (Periode $prev_month-$prev_year).");

echo "</ul></div><div class='card-footer'><a href='index.php' class='btn btn-primary'>Kembali ke Dashboard</a></div></div></body></html>";
?>