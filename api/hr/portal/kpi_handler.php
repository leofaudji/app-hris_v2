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
    // Ambil ID Karyawan dari User ID
    $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
    $stmt_k->bind_param("i", $user_id);
    $stmt_k->execute();
    $karyawan = $stmt_k->get_result()->fetch_assoc();

    if (!$karyawan) {
        throw new Exception("Data karyawan tidak ditemukan.");
    }
    $karyawan_id = $karyawan['id'];

    $action = $_GET['action'] ?? 'list';

    if ($action === 'list') {
        $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

        // Ambil daftar penilaian yang sudah FINAL
        $sql = "SELECT pk.*, t.nama_template, u.nama_lengkap as nama_penilai
                FROM hr_penilaian_kinerja pk
                JOIN hr_kpi_templates t ON pk.template_id = t.id
                LEFT JOIN users u ON pk.penilai_id = u.id
                WHERE pk.karyawan_id = ? AND pk.status = 'final' AND pk.periode_tahun = ?
                ORDER BY pk.periode_tahun DESC, pk.periode_bulan DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $karyawan_id, $tahun);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'detail') {
        $id = $_GET['id'] ?? 0;
        
        // Verifikasi kepemilikan data (Security Check)
        $check = $conn->prepare("SELECT id FROM hr_penilaian_kinerja WHERE id = ? AND karyawan_id = ? AND status = 'final'");
        $check->bind_param("ii", $id, $karyawan_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            throw new Exception("Data tidak ditemukan atau akses ditolak.");
        }

        // Ambil detail indikator
        $sql = "SELECT pd.*, ki.indikator, ki.bobot 
                FROM hr_penilaian_detail pd
                JOIN hr_kpi_indicators ki ON pd.indikator_id = ki.id
                WHERE pd.penilaian_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $details]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}