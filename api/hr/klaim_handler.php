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

        if ($action === 'list') {
            // Filter logic: Admin sees all, Employee sees own
            $where_clause = "1=1";
            $params = [];
            $types = "";

            // Jika bukan admin, hanya lihat punya sendiri (Role ID 1 = Admin)
            if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
                // Cari ID karyawan berdasarkan user_id login
                $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
                $stmt_k->bind_param("i", $_SESSION['user_id']);
                $stmt_k->execute();
                $res_k = $stmt_k->get_result();
                if ($row_k = $res_k->fetch_assoc()) {
                    $where_clause .= " AND k.id = ?";
                    $params[] = $row_k['id'];
                    $types .= "i";
                } else {
                    // User login tapi tidak terlink ke karyawan
                    echo json_encode(['success' => true, 'data' => []]);
                    exit;
                }
            }

            $sql = "SELECT c.*, k.nama_lengkap, k.nip, jk.nama_jenis as jenis_klaim 
                    FROM hr_klaim c 
                    JOIN hr_karyawan k ON c.karyawan_id = k.id 
                    JOIN hr_jenis_klaim jk ON c.jenis_klaim_id = jk.id 
                    WHERE $where_clause 
                    ORDER BY c.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } 
        elseif ($action === 'get_types') {
            $res = $conn->query("SELECT * FROM hr_jenis_klaim ORDER BY nama_jenis ASC");
            echo json_encode(['success' => true, 'data' => $res->fetch_all(MYSQLI_ASSOC)]);
        }

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            // Submit Klaim Baru
            $karyawan_id = $_POST['karyawan_id'] ?? null;
            
            // Jika user biasa, paksa ID karyawan sendiri
            if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
                $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
                $stmt_k->bind_param("i", $_SESSION['user_id']);
                $stmt_k->execute();
                $karyawan_id = $stmt_k->get_result()->fetch_assoc()['id'] ?? null;
            }

            if (!$karyawan_id) throw new Exception("Data karyawan tidak valid.");

            $jenis_klaim_id = $_POST['jenis_klaim_id'];
            $tanggal = $_POST['tanggal_klaim'];
            $jumlah = (float)$_POST['jumlah'];
            $keterangan = $_POST['keterangan'];
            
            // Handle File Upload (Sederhana)
            $bukti_file = null;
            // Implementasi upload file bisa ditambahkan di sini (move_uploaded_file)
            // Untuk demo, kita skip upload fisik

            $stmt = $conn->prepare("INSERT INTO hr_klaim (karyawan_id, jenis_klaim_id, tanggal_klaim, jumlah, keterangan, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iisds", $karyawan_id, $jenis_klaim_id, $tanggal, $jumlah, $keterangan);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Klaim berhasil diajukan.']);
            } else {
                throw new Exception("Gagal menyimpan: " . $stmt->error);
            }

        } elseif ($action === 'update_status') {
            // Approve/Reject (Hanya Admin/HR)
            if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) throw new Exception("Unauthorized access.");

            $id = $_POST['id'];
            $status = $_POST['status']; // approved, rejected, paid
            $user_id = $_SESSION['user_id'];

            $stmt = $conn->prepare("UPDATE hr_klaim SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->bind_param("sii", $status, $user_id, $id);
            
            if ($stmt->execute()) {
                // Jika status 'paid', bisa ditambahkan logika untuk membuat Jurnal Pengeluaran Kas otomatis di sini
                // menggunakan tabel general_ledger
                
                echo json_encode(['success' => true, 'message' => 'Status klaim diperbarui.']);
            } else {
                throw new Exception("Gagal update: " . $stmt->error);
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>