<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$divisi_id = !empty($_GET['divisi_id']) ? (int)$_GET['divisi_id'] : null;

try {
    $data = [];

    // Helper for query construction
    $join_sql = "";
    $where_sql = "";
    $params = [$tahun];
    $types = "i";

    if ($divisi_id) {
        $join_sql = "JOIN hr_karyawan k ON p.karyawan_id = k.id";
        $where_sql = "AND k.divisi_id = ?";
        $params[] = $divisi_id;
        $types .= "i";
    }

    // 1. Total Gaji Tahun Ini
    $sql_total_tahun = "SELECT SUM(p.total_gaji) as total FROM hr_penggajian p $join_sql WHERE p.periode_tahun = ? AND p.status = 'final' $where_sql";
    $stmt = $conn->prepare($sql_total_tahun);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $data['total_gaji_tahun_ini'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    // 2. Data Bulan Terakhir (yang ada datanya di tahun ini)
    $sql_last_month = "SELECT p.periode_bulan, SUM(p.total_gaji) as total_gaji, SUM(p.potongan) as total_potongan, COUNT(DISTINCT p.karyawan_id) as jumlah_karyawan,
                              SUM(p.gaji_pokok) as total_gapok, SUM(p.tunjangan) as total_tunjangan
                       FROM hr_penggajian p
                       $join_sql
                       WHERE p.periode_tahun = ? AND p.status = 'final' $where_sql
                       GROUP BY p.periode_bulan 
                       ORDER BY p.periode_bulan DESC LIMIT 1";
    $stmt = $conn->prepare($sql_last_month);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $last_month_data = $stmt->get_result()->fetch_assoc();

    if ($last_month_data) {
        $data['gaji_bulan_terakhir'] = $last_month_data['total_gaji'];
        $data['total_potongan_bulan_terakhir'] = $last_month_data['total_potongan'];
        $data['jumlah_karyawan_digaji'] = $last_month_data['jumlah_karyawan'];
        $bulan_nama = date('F', mktime(0, 0, 0, $last_month_data['periode_bulan'], 10));
        $data['periode_terakhir'] = $bulan_nama . ' ' . $tahun;
        
        $data['komposisi_gaji'] = [
            'gaji_pokok' => $last_month_data['total_gapok'],
            'tunjangan' => $last_month_data['total_tunjangan'],
            'potongan' => $last_month_data['total_potongan']
        ];
    } else {
        $data['gaji_bulan_terakhir'] = 0;
        $data['total_potongan_bulan_terakhir'] = 0;
        $data['jumlah_karyawan_digaji'] = 0;
        $data['periode_terakhir'] = '-';
        $data['komposisi_gaji'] = ['gaji_pokok' => 0, 'tunjangan' => 0, 'potongan' => 0];
    }

    // 3. Trend Gaji per Bulan (Tahun Ini)
    $sql_trend = "SELECT p.periode_bulan, SUM(p.total_gaji) as total 
                  FROM hr_penggajian p
                  $join_sql
                  WHERE p.periode_tahun = ? AND p.status = 'final' $where_sql
                  GROUP BY p.periode_bulan 
                  ORDER BY p.periode_bulan ASC";
    $stmt = $conn->prepare($sql_trend);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $trend_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $trend_data = [];
    // Initialize all months with 0
    for ($m = 1; $m <= 12; $m++) {
        $month_name = date('M', mktime(0, 0, 0, $m, 10));
        $trend_data[$m] = ['bulan' => $month_name, 'total' => 0];
    }
    // Fill with actual data
    foreach ($trend_result as $row) {
        $m = (int)$row['periode_bulan'];
        $trend_data[$m]['total'] = (float)$row['total'];
    }
    $data['trend_gaji'] = array_values($trend_data);

    // 4. Perbandingan Gaji per Divisi (Tahun Ini)
    // Note: Kita tidak menggunakan filter divisi di sini agar user tetap bisa melihat perbandingan global
    $sql_divisi = "SELECT d.nama_divisi, SUM(p.total_gaji) as total, AVG(p.total_gaji) as rata_rata, COUNT(DISTINCT p.karyawan_id) as jumlah_karyawan
                   FROM hr_penggajian p 
                   JOIN hr_karyawan k ON p.karyawan_id = k.id 
                   LEFT JOIN hr_divisi d ON k.divisi_id = d.id 
                   WHERE p.periode_tahun = ? AND p.status = 'final' 
                   GROUP BY d.id 
                   ORDER BY total DESC";
    $stmt_divisi = $conn->prepare($sql_divisi);
    $stmt_divisi->bind_param("i", $tahun);
    $stmt_divisi->execute();
    $data['perbandingan_divisi'] = $stmt_divisi->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
