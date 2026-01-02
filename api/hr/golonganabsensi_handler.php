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
        $sql = "SELECT * FROM hr_absensi_golongan WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($_GET['search'])) {
            $search = "%" . $_GET['search'] . "%";
            $sql .= " AND nama_golongan LIKE ?";
            $params[] = $search;
            $types .= "s";
        }

        $sql .= " ORDER BY nama_golongan ASC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            if (empty($_POST['nama_golongan'])) {
                throw new Exception("Nama Golongan wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nama_golongan = trim($_POST['nama_golongan']);
            $is_active = (int)$_POST['is_active'];

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_absensi_golongan SET nama_golongan=?, is_active=? WHERE id=?");
                $stmt->bind_param("sii", $nama_golongan, $is_active, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_absensi_golongan (nama_golongan, is_active) VALUES (?, ?)");
                $stmt->bind_param("si", $nama_golongan, $is_active);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data golongan berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            // Cek apakah golongan sedang digunakan
            $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM hr_absensi WHERE golongan = (SELECT nama_golongan FROM hr_absensi_golongan WHERE id = ?)");
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result()->fetch_assoc();

            if ($result_check['count'] > 0) {
                throw new Exception("Golongan tidak dapat dihapus karena sedang digunakan pada data absensi.");
            }

            $stmt = $conn->prepare("DELETE FROM hr_absensi_golongan WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data golongan berhasil dihapus.']);
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