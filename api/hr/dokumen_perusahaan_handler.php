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
        $sql = "SELECT * FROM hr_dokumen_perusahaan ORDER BY kategori ASC, judul ASC";
        $data = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        // Only admin can modify
        $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
        if (!$is_admin) {
            throw new Exception("Unauthorized access.");
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'upload') {
            $judul = $_POST['judul'];
            $kategori = $_POST['kategori'];

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PROJECT_ROOT . '/uploads/company_docs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                
                if ($fileExt !== 'pdf') {
                    throw new Exception("Hanya file PDF yang diperbolehkan.");
                }

                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $judul) . '.' . $fileExt;
                $targetPath = $uploadDir . $fileName;
                $dbPath = 'uploads/company_docs/' . $fileName;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    $stmt = $conn->prepare("INSERT INTO hr_dokumen_perusahaan (judul, kategori, file_path) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $judul, $kategori, $dbPath);
                    $stmt->execute();
                    echo json_encode(['success' => true, 'message' => 'Dokumen berhasil diupload.']);
                } else {
                    throw new Exception("Gagal mengupload file.");
                }
            } else {
                throw new Exception("Tidak ada file yang diupload atau terjadi error.");
            }
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'];
            // Get path first
            $stmt = $conn->prepare("SELECT file_path FROM hr_dokumen_perusahaan WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if ($res) {
                $fullPath = PROJECT_ROOT . '/' . $res['file_path'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $conn->query("DELETE FROM hr_dokumen_perusahaan WHERE id = $id");
                echo json_encode(['success' => true, 'message' => 'Dokumen dihapus.']);
            } else {
                throw new Exception("Dokumen tidak ditemukan.");
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>