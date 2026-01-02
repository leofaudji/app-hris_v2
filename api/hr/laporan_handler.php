<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/bootstrap.php';

// Cek autentikasi
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
        $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

        $sql = "SELECT p.*, k.nama_lengkap, k.nip, j.nama_jabatan 
                FROM hr_penggajian p 
                JOIN hr_karyawan k ON p.karyawan_id = k.id 
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id 
                WHERE p.periode_bulan = ? AND p.periode_tahun = ?
                ORDER BY k.nama_lengkap ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $bulan, $tahun);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Hitung total
        $totals = [
            'gaji_pokok' => 0,
            'tunjangan' => 0,
            'potongan' => 0,
            'total_gaji' => 0
        ];

        foreach ($data as $row) {
            $totals['gaji_pokok'] += (float)$row['gaji_pokok'];
            $totals['tunjangan'] += (float)$row['tunjangan'];
            $totals['potongan'] += (float)$row['potongan'];
            $totals['total_gaji'] += (float)$row['total_gaji'];
        }
        
        echo json_encode(['success' => true, 'data' => $data, 'totals' => $totals]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}