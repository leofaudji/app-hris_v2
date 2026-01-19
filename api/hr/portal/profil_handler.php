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
    $sql = "SELECT k.*, j.nama_jabatan, d.nama_divisi, kt.nama_kantor, gg.nama_golongan as nama_golongan_gaji
            FROM hr_karyawan k 
            LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
            LEFT JOIN hr_divisi d ON k.divisi_id = d.id
            LEFT JOIN hr_kantor kt ON k.kantor_id = kt.id
            LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id
            WHERE k.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        throw new Exception("Profil karyawan tidak ditemukan.");
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}