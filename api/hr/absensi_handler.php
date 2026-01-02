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
        // Aksi untuk mengambil data master
        if (isset($_GET['action'])) {
            if ($_GET['action'] === 'get_golongan') {
                $result = $conn->query("SELECT nama_golongan FROM hr_absensi_golongan WHERE is_active = 1 ORDER BY nama_golongan");
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['success' => true, 'data' => $data]);
                exit;
            }
            if ($_GET['action'] === 'get_status') {
                $result = $conn->query("SELECT nama_status FROM hr_absensi_status WHERE is_active = 1 ORDER BY id");
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['success' => true, 'data' => $data]);
                exit;
            }
        }

        // Aksi utama: mengambil daftar absensi
        $sql = "SELECT a.*, k.nama_lengkap, k.nip, s.badge_class 
                FROM hr_absensi a 
                JOIN hr_karyawan k ON a.karyawan_id = k.id 
                LEFT JOIN hr_absensi_status s ON a.status = s.nama_status
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($_GET['search'])) {
            $search = "%" . $_GET['search'] . "%";
            $sql .= " AND (k.nama_lengkap LIKE ? OR k.nip LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        if (!empty($_GET['tanggal'])) {
            $sql .= " AND a.tanggal = ?";
            $params[] = $_GET['tanggal'];
            $types .= "s";
        }

        if (!empty($_GET['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }

        $sql .= " ORDER BY a.tanggal DESC, k.nama_lengkap ASC";

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
            // Validasi
            if (empty($_POST['karyawan_id']) || empty($_POST['tanggal'])) {
                throw new Exception("Karyawan dan Tanggal wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $karyawan_id = (int)$_POST['karyawan_id'];
            $tanggal = $_POST['tanggal'];
            $golongan = !empty($_POST['golongan']) ? $_POST['golongan'] : null;
            $jam_masuk = !empty($_POST['jam_masuk']) ? $_POST['jam_masuk'] : null;
            $jam_keluar = !empty($_POST['jam_keluar']) ? $_POST['jam_keluar'] : null;
            $status = $_POST['status'] ?? 'hadir';
            $keterangan = $_POST['keterangan'] ?? '';

            if ($id) {
                // Update
                $stmt = $conn->prepare("UPDATE hr_absensi SET karyawan_id=?, tanggal=?, golongan=?, jam_masuk=?, jam_keluar=?, status=?, keterangan=? WHERE id=?");
                $stmt->bind_param("issssssi", $karyawan_id, $tanggal, $golongan, $jam_masuk, $jam_keluar, $status, $keterangan, $id);
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO hr_absensi (karyawan_id, tanggal, golongan, jam_masuk, jam_keluar, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $karyawan_id, $tanggal, $golongan, $jam_masuk, $jam_keluar, $status, $keterangan);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data absensi berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            $stmt = $conn->prepare("DELETE FROM hr_absensi WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data absensi berhasil dihapus.']);
            } else {
                throw new Exception("Gagal menghapus data: " . $stmt->error);
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}