<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? '';

        if ($action === 'get_pending_count') {
            $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
            if ($is_admin) {
                $result = $conn->query("SELECT COUNT(*) as total FROM hr_request_surat WHERE status = 'pending'")->fetch_assoc();
                echo json_encode(['success' => true, 'total' => $result['total']]);
            } else {
                echo json_encode(['success' => true, 'total' => 0]);
            }
            exit;
        }

        // Hanya admin/HR yang bisa akses list ini
        $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
        if (!$is_admin) throw new Exception("Unauthorized access.");

        $status = $_GET['status'] ?? '';
        
        $sql = "SELECT r.*, k.nama_lengkap, k.nip, j.nama_jabatan 
                FROM hr_request_surat r
                JOIN hr_karyawan k ON r.karyawan_id = k.id
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
        if (!$is_admin) throw new Exception("Unauthorized access.");

        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $admin_note = $_POST['admin_note'] ?? '';
            
            $file_path = null;
            // Jika status completed, cek apakah ada file surat yang diupload
            if ($status === 'completed' && isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PROJECT_ROOT . '/uploads/surat_keluar/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileExt = pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION);
                $fileName = 'SURAT_' . $id . '_' . time() . '.' . $fileExt;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $targetPath)) {
                    $file_path = 'uploads/surat_keluar/' . $fileName;
                } else {
                    throw new Exception("Gagal mengupload file surat.");
                }
            }

            $sql = "UPDATE hr_request_surat SET status = ?, admin_note = ?";
            $params = [$status, $admin_note];
            $types = "ss";

            if ($file_path) {
                $sql .= ", file_path = ?";
                $params[] = $file_path;
                $types .= "s";
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;
            $types .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status request berhasil diperbarui.']);
            } else {
                throw new Exception("Gagal update: " . $stmt->error);
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
