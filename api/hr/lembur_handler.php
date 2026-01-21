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
        $where_clause = "1=1";
        $params = [];
        $types = "";

        // Cek apakah user adalah admin (berdasarkan role name 'admin' ATAU role_id 1)
        $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);

        // Filter logic: Admin sees all, Employee sees own
        if (!$is_admin) {
            $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
            $stmt_k->bind_param("i", $_SESSION['user_id']);
            $stmt_k->execute();
            $res_k = $stmt_k->get_result();
            if ($row_k = $res_k->fetch_assoc()) {
                $where_clause .= " AND l.karyawan_id = ?";
                $params[] = $row_k['id'];
                $types .= "i";
            } else {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
        }

        if (!empty($_GET['bulan'])) {
            $where_clause .= " AND MONTH(l.tanggal) = ?";
            $params[] = $_GET['bulan'];
            $types .= "i";
        }
        if (!empty($_GET['tahun'])) {
            $where_clause .= " AND YEAR(l.tanggal) = ?";
            $params[] = $_GET['tahun'];
            $types .= "i";
        }
        if (!empty($_GET['status'])) {
            $where_clause .= " AND l.status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }

        $sql = "SELECT l.*, k.nama_lengkap, k.nip, 
                TIMEDIFF(l.jam_selesai, l.jam_mulai) as durasi
                FROM hr_lembur l 
                JOIN hr_karyawan k ON l.karyawan_id = k.id 
                WHERE $where_clause 
                ORDER BY l.tanggal DESC, l.jam_mulai DESC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            $karyawan_id = $_POST['karyawan_id'] ?? null;
            
            $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);

            // Jika user biasa, paksa ID karyawan sendiri
            if (!$is_admin) {
                $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
                $stmt_k->bind_param("i", $_SESSION['user_id']);
                $stmt_k->execute();
                $karyawan_id = $stmt_k->get_result()->fetch_assoc()['id'] ?? null;
            }

            if (!$karyawan_id) throw new Exception("Data karyawan tidak valid.");

            $tanggal = $_POST['tanggal'];
            $jam_mulai = $_POST['jam_mulai'];
            $jam_selesai = $_POST['jam_selesai'];
            $keterangan = $_POST['keterangan'];

            if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
                throw new Exception("Jam selesai harus lebih besar dari jam mulai.");
            }

            $stmt = $conn->prepare("INSERT INTO hr_lembur (karyawan_id, tanggal, jam_mulai, jam_selesai, keterangan, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issss", $karyawan_id, $tanggal, $jam_mulai, $jam_selesai, $keterangan);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pengajuan lembur berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan: " . $stmt->error);
            }

        } elseif ($action === 'update_status') {
            $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
            if (!$is_admin) throw new Exception("Unauthorized access.");

            $id = $_POST['id'];
            $status = $_POST['status']; // approved, rejected
            $user_id = $_SESSION['user_id'];

            $stmt = $conn->prepare("UPDATE hr_lembur SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->bind_param("sii", $status, $user_id, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status lembur diperbarui.']);
            } else {
                throw new Exception("Gagal update: " . $stmt->error);
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            // Hanya bisa hapus jika status pending
            $stmt = $conn->prepare("DELETE FROM hr_lembur WHERE id = ? AND status = 'pending'");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) echo json_encode(['success' => true, 'message' => 'Data lembur dihapus.']);
                else throw new Exception("Gagal menghapus. Mungkin data sudah disetujui/ditolak.");
            } else {
                throw new Exception("Gagal menghapus: " . $stmt->error);
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>