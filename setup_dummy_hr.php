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


// 11. Karyawan
// Ambil ID dari tabel master untuk relasi acak
$kantor_ids = $conn->query("SELECT id FROM hr_kantor")->fetch_all(MYSQLI_ASSOC);
$divisi_ids = $conn->query("SELECT id FROM hr_divisi")->fetch_all(MYSQLI_ASSOC);
$jadwal_ids = $conn->query("SELECT id FROM hr_jadwal_kerja")->fetch_all(MYSQLI_ASSOC);
$jabatan_ids = $conn->query("SELECT id FROM hr_jabatan")->fetch_all(MYSQLI_ASSOC);
$golongan_ids = $conn->query("SELECT id FROM hr_golongan_gaji")->fetch_all(MYSQLI_ASSOC);

$karyawans = [
    ['nip' => 'EMP2023001', 'nama' => 'Budi Santoso'],
    ['nip' => 'EMP2023002', 'nama' => 'Siti Aminah'],
    ['nip' => 'EMP2023003', 'nama' => 'Rudi Hartono'],
    ['nip' => 'EMP2023004', 'nama' => 'Dewi Sartika'],
    ['nip' => 'EMP2023005', 'nama' => 'Andi Wijaya'],
    ['nip' => 'EMP2023006', 'nama' => 'Rina Marlina'],
];

foreach ($karyawans as $i => $k) {
    $jabatan_id = $jabatan_ids[$i % count($jabatan_ids)]['id'];
    $jadwal_id = $jadwal_ids[$i % count($jadwal_ids)]['id'];
    $divisi_id = $divisi_ids[$i % count($divisi_ids)]['id'];
    $kantor_id = $kantor_ids[$i % count($kantor_ids)]['id'];
    $golongan_id = $golongan_ids[$i % count($golongan_ids)]['id'];
    $tgl_masuk = date('Y-m-d', strtotime("-" . rand(1, 3) . " years"));
    $tgl_kontrak = date('Y-m-d', strtotime("+" . rand(1, 12) . " months"));

    $stmt = $conn->prepare("INSERT INTO hr_karyawan (nip, nama_lengkap, jabatan_id, jadwal_kerja_id, divisi_id, kantor_id, golongan_gaji_id, tanggal_masuk, tanggal_berakhir_kontrak, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')");
    $stmt->bind_param("ssiiiiiss", $k['nip'], $k['nama'], $jabatan_id, $jadwal_id, $divisi_id, $kantor_id, $golongan_id, $tgl_masuk, $tgl_kontrak);
    $stmt->execute();
}

// Buat user untuk Budi Santoso dan link ke data karyawan
$budi_nip = 'EMP2023001';
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
log_step("Data Karyawan berhasil dibuat (" . count($karyawans) . " orang).");

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

// 13. Penggajian (Bulan Lalu)
// Kita gunakan API handler logic secara manual di sini untuk simulasi
$prev_month = date('n', strtotime("first day of last month"));
$prev_year = date('Y', strtotime("first day of last month"));

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