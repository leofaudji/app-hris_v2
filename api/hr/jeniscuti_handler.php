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
        $sql = "SELECT * FROM hr_jenis_cuti ORDER BY nama_jenis ASC";
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            if (empty($_POST['nama_jenis'])) {
                throw new Exception("Nama Jenis Cuti wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nama_jenis = trim($_POST['nama_jenis']);
            $mengurangi_jatah_cuti = (int)$_POST['mengurangi_jatah_cuti'];
            $is_active = (int)$_POST['is_active'];

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_jenis_cuti SET nama_jenis=?, mengurangi_jatah_cuti=?, is_active=? WHERE id=?");
                $stmt->bind_param("siii", $nama_jenis, $mengurangi_jatah_cuti, $is_active, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_jenis_cuti (nama_jenis, mengurangi_jatah_cuti, is_active) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $nama_jenis, $mengurangi_jatah_cuti, $is_active);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data jenis cuti berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            // Cek apakah jenis cuti sedang digunakan
            $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM hr_pengajuan_cuti WHERE jenis_cuti_id = ?");
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result()->fetch_assoc();

            if ($result_check['count'] > 0) {
                throw new Exception("Jenis cuti tidak dapat dihapus karena sedang digunakan pada data pengajuan cuti.");
            }

            $stmt = $conn->prepare("DELETE FROM hr_jenis_cuti WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data jenis cuti berhasil dihapus.']);
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