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
        $action = $_GET['action'] ?? '';

        if ($action === 'expiring_contracts') { // This action is now used by the widget
            // Get contracts expiring in next 30 days or already expired
            $days = 30;
            $sql = "SELECT k.id, k.nip, k.nama_lengkap, k.tanggal_masuk, k.tanggal_berakhir_kontrak, j.nama_jabatan, d.nama_divisi,
                    DATEDIFF(k.tanggal_berakhir_kontrak, CURDATE()) as sisa_hari
                    FROM hr_karyawan k
                    LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                    LEFT JOIN hr_divisi d ON k.divisi_id = d.id
                    WHERE k.status = 'aktif' 
                    AND k.tanggal_berakhir_kontrak IS NOT NULL 
                    AND k.tanggal_berakhir_kontrak <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                    ORDER BY k.tanggal_berakhir_kontrak ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } 
        elseif ($action === 'probation_ending') {
            // Get probation ending in next 30 days (Assuming 3 months probation)
            $days = 30;
            $sql = "SELECT k.id, k.nip, k.nama_lengkap, k.tanggal_masuk, j.nama_jabatan, d.nama_divisi,
                    DATE_ADD(k.tanggal_masuk, INTERVAL 3 MONTH) as tanggal_berakhir_probation,
                    DATEDIFF(DATE_ADD(k.tanggal_masuk, INTERVAL 3 MONTH), CURDATE()) as sisa_hari
                    FROM hr_karyawan k
                    LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                    LEFT JOIN hr_divisi d ON k.divisi_id = d.id
                    WHERE k.status = 'probation'
                    HAVING sisa_hari BETWEEN 0 AND ?
                    ORDER BY sisa_hari ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }
        elseif ($action === 'list_documents') {
            $karyawan_id = $_GET['karyawan_id'] ?? 0;
            $sql = "SELECT * FROM hr_dokumen_karyawan WHERE karyawan_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $karyawan_id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'upload') {
            $karyawan_id = $_POST['karyawan_id'];
            $jenis_dokumen = $_POST['jenis_dokumen'];
            $tanggal_kadaluarsa = !empty($_POST['tanggal_kadaluarsa']) ? $_POST['tanggal_kadaluarsa'] : null;

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PROJECT_ROOT . '/uploads/documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $fileName = $karyawan_id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $jenis_dokumen) . '.' . $fileExt;
                $targetPath = $uploadDir . $fileName;
                $dbPath = 'uploads/documents/' . $fileName;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    $stmt = $conn->prepare("INSERT INTO hr_dokumen_karyawan (karyawan_id, jenis_dokumen, nama_file, path_file, tanggal_kadaluarsa) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issss", $karyawan_id, $jenis_dokumen, $_FILES['file']['name'], $dbPath, $tanggal_kadaluarsa);
                    $stmt->execute();
                    echo json_encode(['success' => true, 'message' => 'Dokumen berhasil diupload.']);
                } else {
                    throw new Exception("Gagal mengupload file.");
                }
            } else {
                throw new Exception("Tidak ada file yang diupload atau terjadi error.");
            }
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'];
            // Get path first
            $stmt = $conn->prepare("SELECT path_file FROM hr_dokumen_karyawan WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if ($res) {
                $fullPath = PROJECT_ROOT . '/' . $res['path_file'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $conn->query("DELETE FROM hr_dokumen_karyawan WHERE id = $id");
                echo json_encode(['success' => true, 'message' => 'Dokumen dihapus.']);
            } else {
                throw new Exception("Dokumen tidak ditemukan.");
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>