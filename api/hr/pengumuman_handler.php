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
        $action = $_GET['action'] ?? 'list';

        if ($action === 'list') { // For Admin page
            $sql = "SELECT p.*, u.nama_lengkap as created_by_name 
                    FROM hr_pengumuman p 
                    JOIN users u ON p.created_by = u.id 
                    ORDER BY p.created_at DESC";
            $result = $conn->query($sql);
            if ($result) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                throw new Exception("Gagal mengambil data (Tabel mungkin belum dibuat): " . $conn->error);
            }
        } 
        elseif ($action === 'list_published') { // For Employee Portal
            $sql = "SELECT * FROM hr_pengumuman WHERE is_published = 1 ORDER BY created_at DESC LIMIT 5";
            $result = $conn->query($sql);
            if ($result) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                throw new Exception("Gagal mengambil data (Tabel mungkin belum dibuat): " . $conn->error);
            }
        }

    } elseif ($method === 'POST') {
        // Only admin can modify
        $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
        if (!$is_admin) {
            throw new Exception("Unauthorized access.");
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            $id = $_POST['id'] ?? null;
            $judul = $_POST['judul'];
            $isi = $_POST['isi'];
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            $created_by = $_SESSION['user_id'];

            $lampiran_path = null;
            if (isset($_FILES['lampiran_file']) && $_FILES['lampiran_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PROJECT_ROOT . '/uploads/announcements/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileName = time() . '_' . basename($_FILES['lampiran_file']['name']);
                $targetPath = $uploadDir . $fileName;
                $lampiran_path = 'uploads/announcements/' . $fileName;

                if (!move_uploaded_file($_FILES['lampiran_file']['tmp_name'], $targetPath)) {
                    throw new Exception("Gagal mengupload lampiran.");
                }
            }

            if ($id) { // Update
                if ($lampiran_path) {
                    $stmt = $conn->prepare("UPDATE hr_pengumuman SET judul = ?, isi = ?, is_published = ?, lampiran_file = ? WHERE id = ?");
                    $stmt->bind_param("ssisi", $judul, $isi, $is_published, $lampiran_path, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE hr_pengumuman SET judul = ?, isi = ?, is_published = ? WHERE id = ?");
                    $stmt->bind_param("ssii", $judul, $isi, $is_published, $id);
                }
            } else { // Insert
                $stmt = $conn->prepare("INSERT INTO hr_pengumuman (judul, isi, is_published, created_by, lampiran_file) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiis", $judul, $isi, $is_published, $created_by, $lampiran_path);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pengumuman berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan: " . $stmt->error);
            }
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'];
            // Get path first to delete file
            $stmt = $conn->prepare("SELECT lampiran_file FROM hr_pengumuman WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if ($res && $res['lampiran_file']) {
                $fullPath = PROJECT_ROOT . '/' . $res['lampiran_file'];
                if (file_exists($fullPath)) unlink($fullPath);
            }
            
            $conn->query("DELETE FROM hr_pengumuman WHERE id = $id");
            echo json_encode(['success' => true, 'message' => 'Pengumuman dihapus.']);
        }
        elseif ($action === 'toggle_publish') {
            $id = $_POST['id'];
            $status = (int)$_POST['status'];
            $stmt = $conn->prepare("UPDATE hr_pengumuman SET is_published = ? WHERE id = ?");
            $stmt->bind_param("ii", $status, $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status publikasi diperbarui.']);
            } else {
                throw new Exception("Gagal memperbarui status.");
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>