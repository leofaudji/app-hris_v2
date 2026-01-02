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
        $sql = "SELECT * FROM hr_golongan_gaji ORDER BY nama_golongan ASC";
        $result = $conn->query($sql);
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
            $gaji_pokok = !empty($_POST['gaji_pokok']) ? (float)$_POST['gaji_pokok'] : 0;
            $keterangan = $_POST['keterangan'];

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_golongan_gaji SET nama_golongan=?, gaji_pokok=?, keterangan=? WHERE id=?");
                $stmt->bind_param("sdsi", $nama_golongan, $gaji_pokok, $keterangan, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_golongan_gaji (nama_golongan, gaji_pokok, keterangan) VALUES (?, ?, ?)");
                $stmt->bind_param("sds", $nama_golongan, $gaji_pokok, $keterangan);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data golongan gaji berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            // Cek apakah golongan sedang digunakan (jika ada relasi di masa depan)
            // Saat ini belum ada relasi langsung yang didefinisikan, tapi bisa ditambahkan nanti
            // Contoh: $check = $conn->prepare("SELECT id FROM hr_karyawan WHERE golongan_gaji_id = ? LIMIT 1");

            $stmt = $conn->prepare("DELETE FROM hr_golongan_gaji WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data golongan gaji berhasil dihapus.']);
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