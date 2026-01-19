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
    $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
    $stmt_k->bind_param("i", $user_id);
    $stmt_k->execute();
    $karyawan = $stmt_k->get_result()->fetch_assoc();

    if (!$karyawan) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Data karyawan tidak ditemukan untuk user ini.']);
        exit;
    }
    $karyawan_id = $karyawan['id'];

    $tahun = (int)($_GET['tahun'] ?? date('Y'));

    $sql = "SELECT * FROM hr_penggajian 
            WHERE karyawan_id = ? AND periode_tahun = ? AND status = 'final'
            ORDER BY periode_bulan DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $karyawan_id, $tahun);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}