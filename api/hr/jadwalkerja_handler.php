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
        $sql = "SELECT * FROM hr_jadwal_kerja ORDER BY nama_jadwal ASC";
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            if (empty($_POST['nama_jadwal']) || empty($_POST['jam_masuk']) || empty($_POST['jam_pulang'])) {
                throw new Exception("Semua field wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nama_jadwal = trim($_POST['nama_jadwal']);
            $jam_masuk = $_POST['jam_masuk'];
            $jam_pulang = $_POST['jam_pulang'];
            $is_active = (int)$_POST['is_active'];

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_jadwal_kerja SET nama_jadwal=?, jam_masuk=?, jam_pulang=?, is_active=? WHERE id=?");
                $stmt->bind_param("sssii", $nama_jadwal, $jam_masuk, $jam_pulang, $is_active, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_jadwal_kerja (nama_jadwal, jam_masuk, jam_pulang, is_active) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $nama_jadwal, $jam_masuk, $jam_pulang, $is_active);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data jadwal berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            // Cek apakah jadwal sedang digunakan oleh karyawan
            $check = $conn->prepare("SELECT id FROM hr_karyawan WHERE jadwal_kerja_id = ? LIMIT 1");
            $check->bind_param("i", $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Jadwal tidak dapat dihapus karena sedang digunakan oleh data karyawan.");
            }

            $stmt = $conn->prepare("DELETE FROM hr_jadwal_kerja WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data jadwal berhasil dihapus.']);
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