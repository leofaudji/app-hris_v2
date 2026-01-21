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
        // List Riwayat Penggajian
        $bulan = $_GET['bulan'] ?? date('n');
        $tahun = $_GET['tahun'] ?? date('Y');

        $sql = "SELECT p.*, k.nama_lengkap, k.nip, j.nama_jabatan 
                FROM hr_penggajian p 
                JOIN hr_karyawan k ON p.karyawan_id = k.id 
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id 
                WHERE p.periode_bulan = ? AND p.periode_tahun = ?
                ORDER BY k.nama_lengkap ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $bulan, $tahun);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'preview_generate' || $action === 'generate') {
            $bulan = $_POST['bulan'];
            $tahun = $_POST['tahun'];

            // 1. Ambil Data Karyawan Aktif
            $sql_karyawan = "SELECT k.id, k.nama_lengkap, k.nip, gg.gaji_pokok, j.tunjangan as tunjangan_jabatan 
                             FROM hr_karyawan k 
                             LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id
                             LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                             WHERE k.status = 'aktif'";
            $karyawans = $conn->query($sql_karyawan)->fetch_all(MYSQLI_ASSOC);

            // 2. Ambil Komponen Default
            $komponen_defaults = $conn->query("SELECT * FROM hr_komponen_gaji WHERE is_default = 1")->fetch_all(MYSQLI_ASSOC);

            $preview_data = [];

            foreach ($karyawans as $k) {
                // Cek apakah sudah ada gaji final untuk periode ini
                $cek = $conn->query("SELECT id FROM hr_penggajian WHERE karyawan_id = {$k['id']} AND periode_bulan = $bulan AND periode_tahun = $tahun AND status = 'final'");
                if ($cek->num_rows > 0) continue; // Skip jika sudah final

                // A. Hitung Absensi
                $stmt_absen = $conn->prepare("SELECT 
                    COUNT(CASE WHEN status='hadir' THEN 1 END) as hadir,
                    COUNT(CASE WHEN status='sakit' THEN 1 END) as sakit,
                    COUNT(CASE WHEN status='izin' THEN 1 END) as izin,
                    COUNT(CASE WHEN status='alpa' THEN 1 END) as alpa
                    FROM hr_absensi WHERE karyawan_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
                $stmt_absen->bind_param("iii", $k['id'], $bulan, $tahun);
                $stmt_absen->execute();
                $absensi = $stmt_absen->get_result()->fetch_assoc();

                // B. Hitung Lembur (INTEGRASI BARU)
                // Hitung total jam lembur yang disetujui
                $stmt_lembur = $conn->prepare("
                    SELECT SUM(TIME_TO_SEC(TIMEDIFF(jam_selesai, jam_mulai))) / 3600 as total_jam 
                    FROM hr_lembur 
                    WHERE karyawan_id = ? 
                    AND status = 'approved' 
                    AND MONTH(tanggal) = ? 
                    AND YEAR(tanggal) = ?
                ");
                $stmt_lembur->bind_param("iii", $k['id'], $bulan, $tahun);
                $stmt_lembur->execute();
                $total_jam_lembur = $stmt_lembur->get_result()->fetch_assoc()['total_jam'] ?? 0;
                
                // Rumus Depnaker: 1/173 * Gaji Pokok per jam lembur (disederhanakan)
                $rate_lembur = $k['gaji_pokok'] / 173;
                $nominal_lembur = round($total_jam_lembur * $rate_lembur);

                // C. Hitung KPI (INTEGRASI BARU)
                // Ambil skor KPI final untuk periode ini
                $stmt_kpi = $conn->prepare("SELECT total_skor FROM hr_penilaian_kinerja WHERE karyawan_id = ? AND periode_bulan = ? AND periode_tahun = ? AND status = 'final'");
                $stmt_kpi->bind_param("iii", $k['id'], $bulan, $tahun);
                $stmt_kpi->execute();
                $kpi_score = $stmt_kpi->get_result()->fetch_assoc()['total_skor'] ?? 0;

                // D. Susun Komponen
                $komponen_details = [];
                $total_pendapatan = $k['gaji_pokok'] + $k['tunjangan_jabatan'];
                $total_potongan = 0;

                // Tunjangan Jabatan (jika ada)
                if ($k['tunjangan_jabatan'] > 0) {
                    $komponen_details[] = [
                        'id' => 0, // ID dummy
                        'nama_komponen' => 'Tunjangan Jabatan',
                        'jenis' => 'pendapatan',
                        'tipe_hitung' => 'bulanan',
                        'jumlah' => (float)$k['tunjangan_jabatan'],
                        'multiplier' => 1,
                        'nilai_satuan' => 0
                    ];
                }

                // Komponen Default (Transport, Makan, BPJS, dll)
                foreach ($komponen_defaults as $kd) {
                    $jumlah = 0;
                    $multiplier = 1;
                    
                    if ($kd['nama_komponen'] === 'Upah Lembur') {
                        // Skip jika tidak ada lembur, atau gunakan nilai hitungan kita
                        if ($nominal_lembur > 0) {
                            $jumlah = $nominal_lembur;
                            // Kita bisa override tipe hitung untuk display di frontend
                            $kd['tipe_hitung'] = 'harian'; // Trik agar muncul detail (jam x rate)
                            $multiplier = round($total_jam_lembur, 1);
                            $kd['nilai_default'] = $rate_lembur; 
                        } else {
                            continue; 
                        }
                    } elseif ($kd['nama_komponen'] === 'Bonus KPI') {
                        // Logika Bonus: (Skor / 100) * Nilai Default (Max Bonus)
                        if ($kpi_score > 0) {
                            $jumlah = ($kpi_score / 100) * $kd['nilai_default'];
                            // Tampilkan skor sebagai multiplier agar informatif di slip gaji
                            $multiplier = $kpi_score; 
                            $kd['tipe_hitung'] = 'kpi'; // Penanda visual (opsional)
                        } else {
                            $jumlah = 0;
                        }
                    } elseif ($kd['tipe_hitung'] === 'harian') {
                        $jumlah = $kd['nilai_default'] * $absensi['hadir'];
                        $multiplier = $absensi['hadir'];
                    } else {
                        $jumlah = $kd['nilai_default'];
                    }

                    if ($jumlah > 0) {
                        if ($kd['jenis'] === 'pendapatan') $total_pendapatan += $jumlah;
                        else $total_potongan += $jumlah;

                        $komponen_details[] = [
                            'id' => $kd['id'],
                            'nama_komponen' => $kd['nama_komponen'],
                            'jenis' => $kd['jenis'],
                            'tipe_hitung' => $kd['tipe_hitung'],
                            'jumlah' => (float)$jumlah,
                            'multiplier' => $multiplier,
                            'nilai_satuan' => (float)$kd['nilai_default']
                        ];
                    }
                }

                $total_gaji = $total_pendapatan - $total_potongan;

                $preview_data[] = [
                    'karyawan_id' => $k['id'],
                    'nama_lengkap' => $k['nama_lengkap'],
                    'gaji_pokok' => (float)$k['gaji_pokok'],
                    'tunjangan_jabatan' => (float)$k['tunjangan_jabatan'],
                    'absensi' => $absensi,
                    'lembur_jam' => $total_jam_lembur,
                    'komponen_details' => $komponen_details,
                    'total_pendapatan' => $total_pendapatan,
                    'total_potongan' => $total_potongan,
                    'total_gaji' => $total_gaji
                ];
            }

            if ($action === 'preview_generate') {
                echo json_encode(['success' => true, 'data' => $preview_data]);
            } else {
                // Action: Generate (Simpan ke DB)
                // Jika ada data editan dari frontend, gunakan itu
                $final_data = isset($_POST['edited_data']) ? json_decode($_POST['edited_data'], true) : $preview_data;
                
                $conn->begin_transaction();
                try {
                    $stmt_insert = $conn->prepare("INSERT INTO hr_penggajian (karyawan_id, periode_bulan, periode_tahun, gaji_pokok, tunjangan, potongan, total_gaji, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'final')");
                    $stmt_detail = $conn->prepare("INSERT INTO hr_penggajian_komponen (penggajian_id, komponen_id, nama_komponen, jenis, jumlah) VALUES (?, ?, ?, ?, ?)");

                    foreach ($final_data as $d) {
                        // Hitung ulang total tunjangan dan potongan dari komponen untuk akurasi
                        $tunjangan_total = $d['tunjangan_jabatan']; // Start with jabatan
                        $potongan_total = 0;

                        foreach ($d['komponen_details'] as $c) {
                            if ($c['nama_komponen'] === 'Tunjangan Jabatan') continue; // Sudah dihitung
                            if ($c['jenis'] === 'pendapatan') $tunjangan_total += $c['jumlah'];
                            else $potongan_total += $c['jumlah'];
                        }
                        
                        $total_gaji_final = $d['gaji_pokok'] + $tunjangan_total - $potongan_total;

                        $stmt_insert->bind_param("iiidddd", $d['karyawan_id'], $bulan, $tahun, $d['gaji_pokok'], $tunjangan_total, $potongan_total, $total_gaji_final);
                        $stmt_insert->execute();
                        $penggajian_id = $conn->insert_id;

                        foreach ($d['komponen_details'] as $c) {
                            $stmt_detail->bind_param("iissd", $penggajian_id, $c['id'], $c['nama_komponen'], $c['jenis'], $c['jumlah']);
                            $stmt_detail->execute();
                        }
                    }
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Gaji berhasil digenerate.']);
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            }
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'];
            $conn->begin_transaction();
            try {
                $conn->query("DELETE FROM hr_penggajian_komponen WHERE penggajian_id = $id");
                $conn->query("DELETE FROM hr_penggajian WHERE id = $id");
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Data gaji dihapus.']);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }
        elseif ($action === 'save') {
            // Manual Save / Edit (Single)
            // Implementasi sederhana untuk update status atau nilai manual
            // ... (Kode untuk update manual bisa ditambahkan di sini jika diperlukan)
            echo json_encode(['success' => true, 'message' => 'Fitur simpan manual belum diimplementasikan sepenuhnya di handler ini.']);
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>