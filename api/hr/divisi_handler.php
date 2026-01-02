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
        $sql = "SELECT * FROM hr_divisi WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($_GET['search'])) {
            $search = "%" . $_GET['search'] . "%";
            $sql .= " AND nama_divisi LIKE ?";
            $params[] = $search;
            $types .= "s";
        }

        $sql .= " ORDER BY nama_divisi ASC";

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
            if (empty($_POST['nama_divisi'])) {
                throw new Exception("Nama Divisi wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nama_divisi = trim($_POST['nama_divisi']);

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_divisi SET nama_divisi=? WHERE id=?");
                $stmt->bind_param("si", $nama_divisi, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_divisi (nama_divisi) VALUES (?)");
                $stmt->bind_param("s", $nama_divisi);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data divisi berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            // Cek apakah divisi sedang digunakan oleh karyawan
            $check = $conn->prepare("SELECT id FROM hr_karyawan WHERE divisi_id = ? LIMIT 1");
            $check->bind_param("i", $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Divisi tidak dapat dihapus karena sedang digunakan oleh data karyawan.");
            }

            $stmt = $conn->prepare("DELETE FROM hr_divisi WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data divisi berhasil dihapus.']);
            } else {
                throw new Exception("Gagal menghapus data: " . $stmt->error);
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}