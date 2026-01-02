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
        $sql = "SELECT * FROM hr_komponen_gaji ORDER BY jenis, nama_komponen ASC";
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            if (empty($_POST['nama_komponen'])) {
                throw new Exception("Nama Komponen wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nama_komponen = trim($_POST['nama_komponen']);
            $jenis = $_POST['jenis'];
            $tipe_hitung = $_POST['tipe_hitung'] ?? 'bulanan';
            $nilai_default = !empty($_POST['nilai_default']) ? (float)$_POST['nilai_default'] : 0;
            $is_default = (int)$_POST['is_default'];

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_komponen_gaji SET nama_komponen=?, jenis=?, tipe_hitung=?, nilai_default=?, is_default=? WHERE id=?");
                $stmt->bind_param("sssdi", $nama_komponen, $jenis, $tipe_hitung, $nilai_default, $is_default, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_komponen_gaji (nama_komponen, jenis, tipe_hitung, nilai_default, is_default) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssdi", $nama_komponen, $jenis, $tipe_hitung, $nilai_default, $is_default);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data komponen gaji berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            // Cek apakah komponen sedang digunakan
            $check = $conn->prepare("SELECT id FROM hr_penggajian_komponen WHERE komponen_id = ? LIMIT 1");
            $check->bind_param("i", $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Komponen tidak dapat dihapus karena sudah digunakan dalam data penggajian.");
            }

            $stmt = $conn->prepare("DELETE FROM hr_komponen_gaji WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data komponen gaji berhasil dihapus.']);
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