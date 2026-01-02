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
        $sql = "SELECT * FROM hr_absensi_status ORDER BY nama_status ASC";
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            if (empty($_POST['nama_status']) || empty($_POST['badge_class'])) {
                throw new Exception("Nama Status dan Kelas Badge wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nama_status = strtolower(trim($_POST['nama_status']));
            $badge_class = trim($_POST['badge_class']);
            $is_active = (int)$_POST['is_active'];

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_absensi_status SET nama_status=?, badge_class=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssii", $nama_status, $badge_class, $is_active, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_absensi_status (nama_status, badge_class, is_active) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $nama_status, $badge_class, $is_active);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data status absensi berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            // Cek apakah status sedang digunakan
            $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM hr_absensi WHERE status = (SELECT nama_status FROM hr_absensi_status WHERE id = ?)");
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result()->fetch_assoc();

            if ($result_check['count'] > 0) {
                throw new Exception("Status tidak dapat dihapus karena sedang digunakan pada data absensi.");
            }

            $stmt = $conn->prepare("DELETE FROM hr_absensi_status WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data status absensi berhasil dihapus.']);
            } else {
                throw new Exception("Gagal menghapus data: " . $stmt->error);
            }
        } else {
            throw new Exception("Aksi tidak valid.");
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}