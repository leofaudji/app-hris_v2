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
            $sql = "SELECT o.*, k.nama_lengkap, k.nip, j.nama_jabatan 
                    FROM hr_offboarding o
                    JOIN hr_karyawan k ON o.karyawan_id = k.id
                    LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                    ORDER BY o.status ASC, o.tanggal_efektif ASC";
            $data = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'initiate') {
            $karyawan_id = (int)$_POST['karyawan_id'];
            $tipe = $_POST['tipe'];
            $tanggal_pengajuan = $_POST['tanggal_pengajuan'];
            $tanggal_efektif = $_POST['tanggal_efektif'];
            $alasan = $_POST['alasan'];

            // Cek apakah karyawan sudah dalam proses offboarding
            $stmt_check = $conn->prepare("SELECT id FROM hr_offboarding WHERE karyawan_id = ?");
            $stmt_check->bind_param("i", $karyawan_id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                throw new Exception("Karyawan ini sudah dalam proses offboarding.");
            }

            $stmt = $conn->prepare("INSERT INTO hr_offboarding (karyawan_id, tipe, tanggal_pengajuan, tanggal_efektif, alasan) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $karyawan_id, $tipe, $tanggal_pengajuan, $tanggal_efektif, $alasan);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Proses offboarding berhasil dimulai.']);
            } else {
                throw new Exception("Gagal memulai proses: " . $stmt->error);
            }
        }
        elseif ($action === 'update_checklist') {
            $id = (int)$_POST['id'];
            $checklist_data = $_POST['checklist_data']; // JSON string from frontend

            $stmt = $conn->prepare("UPDATE hr_offboarding SET checklist_data = ? WHERE id = ?");
            $stmt->bind_param("si", $checklist_data, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Checklist berhasil diperbarui.']);
            } else {
                throw new Exception("Gagal memperbarui checklist.");
            }
        }
        elseif ($action === 'finalize') {
            $id = (int)$_POST['id'];

            $conn->begin_transaction();
            try {
                // 1. Get Karyawan ID
                $stmt_get = $conn->prepare("SELECT karyawan_id FROM hr_offboarding WHERE id = ?");
                $stmt_get->bind_param("i", $id);
                $stmt_get->execute();
                $karyawan_id = $stmt_get->get_result()->fetch_assoc()['karyawan_id'];

                if (!$karyawan_id) throw new Exception("Data offboarding tidak ditemukan.");

                // 2. Update status offboarding
                $conn->query("UPDATE hr_offboarding SET status = 'selesai' WHERE id = $id");

                // 3. Update status karyawan
                $conn->query("UPDATE hr_karyawan SET status = 'nonaktif' WHERE id = $karyawan_id");

                // 4. (Optional) Deactivate user login
                $stmt_user = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = (SELECT user_id FROM hr_karyawan WHERE id = ?)");
                $stmt_user->bind_param("i", $karyawan_id);
                $stmt_user->execute();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Proses offboarding telah selesai. Status karyawan diubah menjadi nonaktif.']);

            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }
        elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM hr_offboarding WHERE id = ? AND status = 'proses'");
            $stmt->bind_param("i", $id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Proses offboarding dibatalkan.']);
            } else {
                throw new Exception("Gagal membatalkan. Mungkin proses sudah selesai.");
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>