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
        $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
        $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
        $divisi_id = !empty($_GET['divisi_id']) ? (int)$_GET['divisi_id'] : null;

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
        } else {
            // Jika tidak ada filter tanggal spesifik, filter berdasarkan bulan & tahun
            $sql .= " AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?";
            $params[] = $bulan;
            $params[] = $tahun;
            $types .= "ii";
        }

        if (!empty($_GET['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }

        if ($divisi_id) {
            $sql .= " AND k.divisi_id = ?";
            $params[] = $divisi_id;
            $types .= "i";
        }

        $sql .= " ORDER BY a.tanggal DESC, k.nama_lengkap ASC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Hitung total karyawan aktif untuk referensi persentase
        $sql_count = "SELECT COUNT(id) as total FROM hr_karyawan WHERE status = 'aktif'";
        if ($divisi_id) {
            $sql_count .= " AND divisi_id = " . $divisi_id;
        }
        $res_count = $conn->query($sql_count);
        $total_karyawan = $res_count->fetch_assoc()['total'];

        // Ambil data untuk grafik harian
        $sql_chart = "SELECT 
                        DAY(a.tanggal) as day,
                        SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN a.status = 'alpa' THEN 1 ELSE 0 END) as alpa
                    FROM hr_absensi a
                    " . ($divisi_id ? "JOIN hr_karyawan k ON a.karyawan_id = k.id" : "") . "
                    WHERE MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?";
        
        if ($divisi_id) {
            $sql_chart .= " AND k.divisi_id = ?";
        }
        
        $sql_chart .= "
                    GROUP BY DAY(tanggal) 
                    ORDER BY day ASC";
        $stmt_chart = $conn->prepare($sql_chart);
        if ($divisi_id) $stmt_chart->bind_param("iii", $bulan, $tahun, $divisi_id); else $stmt_chart->bind_param("ii", $bulan, $tahun);
        $stmt_chart->execute();
        $chart_data = $stmt_chart->get_result()->fetch_all(MYSQLI_ASSOC);

        // Ambil Top 5 Karyawan Rajin
        $sql_top = "SELECT 
                        k.nama_lengkap, 
                        k.nip,
                        COUNT(a.id) as total_hadir,
                        SEC_TO_TIME(AVG(TIME_TO_SEC(a.jam_masuk))) as avg_masuk,
                        SEC_TO_TIME(AVG(TIME_TO_SEC(a.jam_keluar))) as avg_pulang
                    FROM hr_absensi a
                    JOIN hr_karyawan k ON a.karyawan_id = k.id
                    WHERE a.status = 'hadir' 
                      AND MONTH(a.tanggal) = ?
                      AND YEAR(a.tanggal) = ?";
        
        if ($divisi_id) {
            $sql_top .= " AND k.divisi_id = ?";
        }
        
        $sql_top .= "
                    GROUP BY a.karyawan_id
                    ORDER BY total_hadir DESC, avg_masuk ASC
                    LIMIT 5";
        $stmt_top = $conn->prepare($sql_top);
        if ($divisi_id) $stmt_top->bind_param("iii", $bulan, $tahun, $divisi_id); else $stmt_top->bind_param("ii", $bulan, $tahun);
        $stmt_top->execute();
        $top_employees = $stmt_top->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'data' => $data, 
            'total_karyawan' => $total_karyawan, 
            'chart_data' => $chart_data,
            'top_employees' => $top_employees
        ]);

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