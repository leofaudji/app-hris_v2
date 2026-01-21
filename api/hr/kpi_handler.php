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

        if ($action === 'list_templates') {
            $sql = "SELECT * FROM hr_kpi_templates ORDER BY nama_template ASC";
            $res = $conn->query($sql);
            echo json_encode(['success' => true, 'data' => $res->fetch_all(MYSQLI_ASSOC)]);
        }
        elseif ($action === 'get_template_detail') {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM hr_kpi_indicators WHERE template_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
        }
        elseif ($action === 'list_appraisals') {
            $bulan = $_GET['bulan'] ?? date('n');
            $tahun = $_GET['tahun'] ?? date('Y');
            
            $sql = "SELECT p.*, k.nama_lengkap, k.nip, t.nama_template, u.nama_lengkap as nama_penilai
                    FROM hr_penilaian_kinerja p
                    JOIN hr_karyawan k ON p.karyawan_id = k.id
                    JOIN hr_kpi_templates t ON p.template_id = t.id
                    LEFT JOIN users u ON p.penilai_id = u.id
                    WHERE p.periode_bulan = ? AND p.periode_tahun = ?
                    ORDER BY p.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $bulan, $tahun);
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
        }
        elseif ($action === 'get_appraisal_detail') {
            $id = $_GET['id'];
            // Get Header
            $stmt = $conn->prepare("SELECT p.*, k.nama_lengkap, t.nama_template FROM hr_penilaian_kinerja p JOIN hr_karyawan k ON p.karyawan_id = k.id JOIN hr_kpi_templates t ON p.template_id = t.id WHERE p.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $header = $stmt->get_result()->fetch_assoc();

            // Get Details
            $stmt2 = $conn->prepare("SELECT d.*, i.indikator, i.bobot FROM hr_penilaian_detail d JOIN hr_kpi_indicators i ON d.indikator_id = i.id WHERE d.penilaian_id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $details = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['success' => true, 'data' => ['header' => $header, 'details' => $details]]);
        }
        elseif ($action === 'get_kpi_trend') {
            $karyawan_id = $_GET['karyawan_id'] ?? 0;
            if (!$karyawan_id) {
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }

            // Get data for the last 12 months
            $sql = "SELECT periode_bulan, periode_tahun, total_skor
                    FROM hr_penilaian_kinerja
                    WHERE karyawan_id = ? AND status = 'final'
                    AND STR_TO_DATE(CONCAT(periode_tahun, '-', periode_bulan, '-01'), '%Y-%m-%d') >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    ORDER BY periode_tahun ASC, periode_bulan ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $karyawan_id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }
        elseif ($action === 'get_kpi_comparison') {
            $bulan = $_GET['bulan'] ?? date('n');
            $tahun = $_GET['tahun'] ?? date('Y');
            $divisi_id = $_GET['divisi_id'] ?? '';

            $where_clause = "p.periode_bulan = ? AND p.periode_tahun = ? AND p.status = 'final'";
            $params = [$bulan, $tahun];
            $types = "ii";

            if (!empty($divisi_id)) {
                $where_clause .= " AND k.divisi_id = ?";
                $params[] = $divisi_id;
                $types .= "i";
            }

            $sql = "SELECT k.nama_lengkap, p.total_skor
                    FROM hr_penilaian_kinerja p
                    JOIN hr_karyawan k ON p.karyawan_id = k.id
                    WHERE $where_clause
                    ORDER BY p.total_skor DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
        }
        elseif ($action === 'get_top_performer') {
            // Find the latest period with final appraisals
            $latest_period_res = $conn->query("
                SELECT periode_tahun, periode_bulan 
                FROM hr_penilaian_kinerja 
                WHERE status = 'final' 
                ORDER BY periode_tahun DESC, periode_bulan DESC 
                LIMIT 1
            ");
            $latest_period = $latest_period_res->fetch_assoc();

            if ($latest_period) {
                $stmt = $conn->prepare("
                    SELECT k.nama_lengkap, p.total_skor, j.nama_jabatan
                    FROM hr_penilaian_kinerja p
                    JOIN hr_karyawan k ON p.karyawan_id = k.id
                    LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                    WHERE p.periode_tahun = ? AND p.periode_bulan = ? AND p.status = 'final'
                    ORDER BY p.total_skor DESC
                    LIMIT 1
                ");
                $stmt->bind_param("ii", $latest_period['periode_tahun'], $latest_period['periode_bulan']);
                $stmt->execute();
                $top_performer = $stmt->get_result()->fetch_assoc();
                $top_performer['periode'] = date('F Y', mktime(0, 0, 0, $latest_period['periode_bulan'], 1, $latest_period['periode_tahun']));
                echo json_encode(['success' => true, 'data' => $top_performer]);
            } else {
                echo json_encode(['success' => true, 'data' => null]); // No data found
            }
        }
        elseif ($action === 'get_bottom_performer') {
            // Find the latest period with final appraisals
            $latest_period_res = $conn->query("
                SELECT periode_tahun, periode_bulan 
                FROM hr_penilaian_kinerja 
                WHERE status = 'final' 
                ORDER BY periode_tahun DESC, periode_bulan DESC 
                LIMIT 1
            ");
            $latest_period = $latest_period_res->fetch_assoc();

            if ($latest_period) {
                $stmt = $conn->prepare("
                    SELECT k.nama_lengkap, p.total_skor, j.nama_jabatan
                    FROM hr_penilaian_kinerja p
                    JOIN hr_karyawan k ON p.karyawan_id = k.id
                    LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                    WHERE p.periode_tahun = ? AND p.periode_bulan = ? AND p.status = 'final'
                    ORDER BY p.total_skor ASC
                    LIMIT 1
                ");
                $stmt->bind_param("ii", $latest_period['periode_tahun'], $latest_period['periode_bulan']);
                $stmt->execute();
                $bottom_performer = $stmt->get_result()->fetch_assoc();
                if ($bottom_performer) {
                    $bottom_performer['periode'] = date('F Y', mktime(0, 0, 0, $latest_period['periode_bulan'], 1, $latest_period['periode_tahun']));
                }
                echo json_encode(['success' => true, 'data' => $bottom_performer]);
            } else {
                echo json_encode(['success' => true, 'data' => null]); // No data found
            }
        }
        elseif ($action === 'get_calendar_widget_data') {
            $bulan = $_GET['bulan'] ?? date('n');
            $tahun = $_GET['tahun'] ?? date('Y');

            $events = [];

            // 1. Get National Holidays
            $stmt_libur = $conn->prepare("SELECT tanggal, keterangan FROM hr_libur_nasional WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
            $stmt_libur->bind_param("ii", $bulan, $tahun);
            $stmt_libur->execute();
            $res_libur = $stmt_libur->get_result();
            while ($row = $res_libur->fetch_assoc()) {
                if (!isset($events[$row['tanggal']])) $events[$row['tanggal']] = [];
                $events[$row['tanggal']][] = ['type' => 'holiday', 'text' => $row['keterangan']];
            }

            // 2. Get Approved Leaves
            $stmt_cuti = $conn->prepare("
                SELECT pc.tanggal_mulai, pc.tanggal_selesai, k.nama_lengkap 
                FROM hr_pengajuan_cuti pc
                JOIN hr_karyawan k ON pc.karyawan_id = k.id
                WHERE pc.status = 'approved' AND (
                    (MONTH(pc.tanggal_mulai) = ? AND YEAR(pc.tanggal_mulai) = ?) OR
                    (MONTH(pc.tanggal_selesai) = ? AND YEAR(pc.tanggal_selesai) = ?)
                )
            ");
            $stmt_cuti->bind_param("iiii", $bulan, $tahun, $bulan, $tahun);
            $stmt_cuti->execute();
            $res_cuti = $stmt_cuti->get_result();
            while ($row = $res_cuti->fetch_assoc()) {
                $start = new DateTime($row['tanggal_mulai']);
                $end = new DateTime($row['tanggal_selesai']);
                $end->modify('+1 day');
                $interval = new DateInterval('P1D');
                $dateRange = new DatePeriod($start, $interval, $end);
                foreach ($dateRange as $date) {
                    if (!isset($events[$date->format('Y-m-d')])) $events[$date->format('Y-m-d')] = [];
                    $events[$date->format('Y-m-d')][] = ['type' => 'leave', 'text' => 'Cuti: ' . $row['nama_lengkap']];
                }
            }
            echo json_encode(['success' => true, 'data' => $events]);
        }

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_template') {
            $nama = $_POST['nama_template'];
            $keterangan = $_POST['keterangan'];
            $indicators = json_decode($_POST['indicators'], true);

            $conn->begin_transaction();
            try {
                if (empty($_POST['id'])) {
                    $stmt = $conn->prepare("INSERT INTO hr_kpi_templates (nama_template, keterangan) VALUES (?, ?)");
                    $stmt->bind_param("ss", $nama, $keterangan);
                    $stmt->execute();
                    $template_id = $conn->insert_id;
                } else {
                    $template_id = $_POST['id'];
                    $stmt = $conn->prepare("UPDATE hr_kpi_templates SET nama_template = ?, keterangan = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $nama, $keterangan, $template_id);
                    $stmt->execute();
                    // Delete old indicators to replace
                    $conn->query("DELETE FROM hr_kpi_indicators WHERE template_id = $template_id");
                }

                $stmt_ind = $conn->prepare("INSERT INTO hr_kpi_indicators (template_id, indikator, bobot) VALUES (?, ?, ?)");
                foreach ($indicators as $ind) {
                    $stmt_ind->bind_param("isi", $template_id, $ind['indikator'], $ind['bobot']);
                    $stmt_ind->execute();
                }

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Template KPI berhasil disimpan.']);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }
        elseif ($action === 'delete_template') {
            $id = $_POST['id'];
            $conn->query("DELETE FROM hr_kpi_indicators WHERE template_id = $id");
            $conn->query("DELETE FROM hr_kpi_templates WHERE id = $id");
            echo json_encode(['success' => true, 'message' => 'Template dihapus.']);
        }
        elseif ($action === 'save_appraisal') {
            $karyawan_id = $_POST['karyawan_id'];
            $template_id = $_POST['template_id'];
            $bulan = $_POST['periode_bulan'];
            $tahun = $_POST['periode_tahun'];
            $tanggal = $_POST['tanggal_penilaian'];
            $catatan = $_POST['catatan'];
            $status = $_POST['status']; // draft / final
            $scores = json_decode($_POST['scores'], true); // Array of {indikator_id, skor, komentar}
            $penilai_id = $_SESSION['user_id'];

            // Calculate Total Score
            $total_skor = 0;
            // Get weights
            $stmt_w = $conn->prepare("SELECT id, bobot FROM hr_kpi_indicators WHERE template_id = ?");
            $stmt_w->bind_param("i", $template_id);
            $stmt_w->execute();
            $weights = [];
            $res_w = $stmt_w->get_result();
            while($row = $res_w->fetch_assoc()) $weights[$row['id']] = $row['bobot'];

            foreach ($scores as $s) {
                $bobot = $weights[$s['indikator_id']] ?? 0;
                // Skor (1-100) * Bobot%
                $total_skor += ($s['skor'] * ($bobot / 100));
            }

            $conn->begin_transaction();
            try {
                $penilaian_id = 0;
                if (empty($_POST['id'])) {
                    $stmt = $conn->prepare("INSERT INTO hr_penilaian_kinerja (karyawan_id, template_id, periode_bulan, periode_tahun, tanggal_penilaian, penilai_id, total_skor, catatan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiiisidss", $karyawan_id, $template_id, $bulan, $tahun, $tanggal, $penilai_id, $total_skor, $catatan, $status);
                    $stmt->execute();
                    $penilaian_id = $conn->insert_id;
                } else {
                    $penilaian_id = $_POST['id'];
                    $stmt = $conn->prepare("UPDATE hr_penilaian_kinerja SET total_skor = ?, catatan = ?, status = ?, tanggal_penilaian = ? WHERE id = ?");
                    $stmt->bind_param("dsssi", $total_skor, $catatan, $status, $tanggal, $penilaian_id);
                    $stmt->execute();
                    $conn->query("DELETE FROM hr_penilaian_detail WHERE penilaian_id = $penilaian_id");
                }

                $stmt_det = $conn->prepare("INSERT INTO hr_penilaian_detail (penilaian_id, indikator_id, skor, komentar) VALUES (?, ?, ?, ?)");
                foreach ($scores as $s) {
                    $stmt_det->bind_param("iiis", $penilaian_id, $s['indikator_id'], $s['skor'], $s['komentar']);
                    $stmt_det->execute();
                }

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Penilaian berhasil disimpan.']);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }
        elseif ($action === 'delete_appraisal') {
            $id = $_POST['id'];
            $conn->query("DELETE FROM hr_penilaian_detail WHERE penilaian_id = $id");
            $conn->query("DELETE FROM hr_penilaian_kinerja WHERE id = $id");
            echo json_encode(['success' => true, 'message' => 'Penilaian dihapus.']);
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>