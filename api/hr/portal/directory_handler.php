<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();

try {
    $search = $_GET['search'] ?? '';
    $divisi_id = $_GET['divisi_id'] ?? '';

    $sql = "SELECT k.id, k.nama_lengkap, k.nip, k.email, k.no_hp, j.nama_jabatan, d.nama_divisi, kt.nama_kantor, u.foto_profil
            FROM hr_karyawan k
            LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
            LEFT JOIN hr_divisi d ON k.divisi_id = d.id
            LEFT JOIN hr_kantor kt ON k.kantor_id = kt.id
            LEFT JOIN users u ON k.user_id = u.id
            WHERE k.status = 'aktif'";

    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (k.nama_lengkap LIKE ? OR k.nip LIKE ? OR j.nama_jabatan LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= "sss";
    }

    if (!empty($divisi_id)) {
        $sql .= " AND k.divisi_id = ?";
        $params[] = $divisi_id;
        $types .= "i";
    }

    $sql .= " ORDER BY k.nama_lengkap ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}