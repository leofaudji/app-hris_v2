<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();

try {
    $data = [];

    // Total Counts
    $data['total_karyawan'] = $conn->query("SELECT COUNT(*) as total FROM hr_karyawan")->fetch_assoc()['total'];
    $data['total_divisi'] = $conn->query("SELECT COUNT(*) as total FROM hr_divisi")->fetch_assoc()['total'];
    $data['total_jabatan'] = $conn->query("SELECT COUNT(*) as total FROM hr_jabatan")->fetch_assoc()['total'];
    $data['total_kantor'] = $conn->query("SELECT COUNT(*) as total FROM hr_kantor")->fetch_assoc()['total'];

    // Distribution by Divisi
    $divisi_res = $conn->query("
        SELECT d.nama_divisi, COUNT(k.id) as total 
        FROM hr_divisi d 
        LEFT JOIN hr_karyawan k ON d.id = k.divisi_id 
        GROUP BY d.id 
        ORDER BY total DESC
    ");
    $data['divisi_distribution'] = $divisi_res->fetch_all(MYSQLI_ASSOC);

    // Distribution by Status
    $status_res = $conn->query("
        SELECT status, COUNT(*) as total 
        FROM hr_karyawan 
        GROUP BY status
    ");
    $data['status_distribution'] = $status_res->fetch_all(MYSQLI_ASSOC);

    // Expiring Contracts (Next 60 days)
    $data['expiring_contracts'] = [];
    try {
        $expiring_sql = "
            SELECT nama_lengkap, nip, tanggal_berakhir_kontrak, 
                   DATEDIFF(tanggal_berakhir_kontrak, CURDATE()) as sisa_hari 
            FROM hr_karyawan 
            WHERE status = 'aktif' 
              AND tanggal_berakhir_kontrak IS NOT NULL 
              AND tanggal_berakhir_kontrak BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
            ORDER BY sisa_hari ASC
            LIMIT 5
        ";
        $expiring_res = $conn->query($expiring_sql);
        $data['expiring_contracts'] = $expiring_res->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $ex) {
        // Ignore error if column doesn't exist yet
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
