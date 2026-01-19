<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

try {
    // Get karyawan_id from user_id
    $stmt_k = $conn->prepare("SELECT id, nama_lengkap FROM hr_karyawan WHERE user_id = ?");
    $stmt_k->bind_param("i", $user_id);
    $stmt_k->execute();
    $karyawan = $stmt_k->get_result()->fetch_assoc();

    if (!$karyawan) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Data karyawan tidak ditemukan untuk user ini.']);
        exit;
    }
    $karyawan_id = $karyawan['id'];

    $response_data = [];
    $response_data['nama_karyawan'] = $karyawan['nama_lengkap'];

    // Get Sisa Cuti
    $tahun_ini = date('Y');
    $stmt_cuti = $conn->prepare("SELECT sisa_jatah FROM hr_jatah_cuti WHERE karyawan_id = ? AND tahun = ?");
    $stmt_cuti->bind_param("ii", $karyawan_id, $tahun_ini);
    $stmt_cuti->execute();
    $sisa_cuti = $stmt_cuti->get_result()->fetch_assoc();
    $response_data['sisa_cuti'] = $sisa_cuti['sisa_jatah'] ?? 12;

    // Get Kehadiran Bulan Ini
    $bulan_ini = date('m');
    $stmt_hadir = $conn->prepare("SELECT COUNT(id) as total FROM hr_absensi WHERE karyawan_id = ? AND status = 'hadir' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
    $stmt_hadir->bind_param("iii", $karyawan_id, $bulan_ini, $tahun_ini);
    $stmt_hadir->execute();
    $kehadiran = $stmt_hadir->get_result()->fetch_assoc();
    $response_data['kehadiran_bulan_ini'] = $kehadiran['total'] ?? 0;

    // Get Pengajuan Cuti Pending
    $stmt_pending = $conn->prepare("SELECT COUNT(id) as total FROM hr_pengajuan_cuti WHERE karyawan_id = ? AND status = 'pending'");
    $stmt_pending->bind_param("i", $karyawan_id);
    $stmt_pending->execute();
    $pending = $stmt_pending->get_result()->fetch_assoc();
    $response_data['cuti_pending'] = $pending['total'] ?? 0;

    echo json_encode(['success' => true, 'data' => $response_data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}