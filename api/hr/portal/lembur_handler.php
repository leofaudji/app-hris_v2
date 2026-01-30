<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Ambil ID Karyawan dari User ID
    $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
    $stmt_k->bind_param("i", $user_id);
    $stmt_k->execute();
    $karyawan = $stmt_k->get_result()->fetch_assoc();

    if (!$karyawan) {
        throw new Exception("Data karyawan tidak ditemukan.");
    }
    $karyawan_id = $karyawan['id'];

    if ($method === 'GET') {
        // List Riwayat Lembur Saya
        $sql = "SELECT * FROM hr_lembur WHERE karyawan_id = ? ORDER BY tanggal DESC, created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $karyawan_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? 'create';

        if ($action === 'create') {
            $tanggal = $_POST['tanggal'];
            $jam_mulai = $_POST['jam_mulai'];
            $jam_selesai = $_POST['jam_selesai'];
            $keterangan = $_POST['keterangan'];

            if (empty($tanggal) || empty($jam_mulai) || empty($jam_selesai) || empty($keterangan)) {
                throw new Exception("Semua field wajib diisi.");
            }

            $stmt = $conn->prepare("INSERT INTO hr_lembur (karyawan_id, tanggal, jam_mulai, jam_selesai, keterangan, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issss", $karyawan_id, $tanggal, $jam_mulai, $jam_selesai, $keterangan);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pengajuan lembur berhasil dikirim.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            // Pastikan hanya bisa hapus punya sendiri dan status pending
            $stmt = $conn->prepare("DELETE FROM hr_lembur WHERE id = ? AND karyawan_id = ? AND status = 'pending'");
            $stmt->bind_param("ii", $id, $karyawan_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Pengajuan lembur dibatalkan.']);
            } else {
                throw new Exception("Gagal menghapus. Mungkin data tidak ditemukan atau status sudah diproses.");
            }
        }
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}