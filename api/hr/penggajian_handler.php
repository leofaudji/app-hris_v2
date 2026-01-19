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

        $sql = "SELECT p.*, k.nama_lengkap, k.nip, j.nama_jabatan 
                FROM hr_penggajian p 
                JOIN hr_karyawan k ON p.karyawan_id = k.id 
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id 
                WHERE p.periode_bulan = ? AND p.periode_tahun = ?
                ORDER BY k.nama_lengkap ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $bulan, $tahun);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'preview_generate' || $action === 'generate') {
            $is_preview = ($action === 'preview_generate');
            $bulan = (int)$_POST['bulan'];
            $tahun = (int)$_POST['tahun'];
            
            // Check if we have edited data from preview
            $edited_data = null;
            if (isset($_POST['edited_data'])) {
                $edited_data = json_decode($_POST['edited_data'], true);
            }

            // Ambil semua karyawan aktif beserta info jabatannya
            $sql_karyawan = "SELECT k.id, k.nama_lengkap, k.status_ptkp, k.ikut_bpjs_kes, k.ikut_bpjs_tk, gg.gaji_pokok, j.tunjangan 
                             FROM hr_karyawan k 
                             LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id 
                             LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id
                             WHERE k.status = 'aktif'";
            $res_karyawan = $conn->query($sql_karyawan);
            
            // Ambil komponen gaji default
            $sql_komponen = "SELECT id, nama_komponen, jenis, tipe_hitung, nilai_default FROM hr_komponen_gaji WHERE is_default = 1";
            $res_komponen = $conn->query($sql_komponen);
            $default_components = $res_komponen->fetch_all(MYSQLI_ASSOC);

            // Ambil pengaturan pajak & BPJS
            $tax_settings = [];
            $res_tax = $conn->query("SELECT * FROM hr_pengaturan_pajak");
            while($row = $res_tax->fetch_assoc()) {
                $tax_settings[$row['setting_key']] = $row['setting_value'];
            }

            $preview_data = [];
            $count = 0;

            if (!$is_preview) {
                $stmt_insert = $conn->prepare("INSERT INTO hr_penggajian (karyawan_id, periode_bulan, periode_tahun, gaji_pokok, tunjangan, potongan, total_gaji, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')");
                $stmt_insert_detail = $conn->prepare("INSERT INTO hr_penggajian_komponen (penggajian_id, komponen_id, nama_komponen, jenis, jumlah) VALUES (?, ?, ?, ?, ?)");
            }
            
            // Prepare statement untuk mengambil ringkasan absensi
            $stmt_absensi = $conn->prepare("
                SELECT 
                    SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa,
                    GROUP_CONCAT(CASE WHEN status = 'hadir' THEN DAY(tanggal) END ORDER BY tanggal ASC SEPARATOR ', ') as tanggal_hadir
                FROM hr_absensi 
                WHERE karyawan_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
            ");

            // If we have edited data, use it directly instead of recalculating
            if ($edited_data && !$is_preview) {
                foreach ($edited_data as $data) {
                    // Cek duplikasi (safety check)
                    $check = $conn->prepare("SELECT id FROM hr_penggajian WHERE karyawan_id = ? AND periode_bulan = ? AND periode_tahun = ?");
                    $check->bind_param("iii", $data['karyawan_id'], $bulan, $tahun);
                    $check->execute();
                    
                    if ($check->get_result()->num_rows == 0) {
                        $total_tunjangan = 0;
                        $total_potongan = 0;
                        
                        foreach ($data['komponen_details'] as $comp) {
                            if ($comp['nama_komponen'] === 'Gaji Pokok') continue;
                            if ($comp['jenis'] === 'pendapatan') $total_tunjangan += $comp['jumlah'];
                            else $total_potongan += $comp['jumlah'];
                        }
                        
                        $stmt_insert->bind_param("iiidddd", $data['karyawan_id'], $bulan, $tahun, $data['gaji_pokok'], $total_tunjangan, $total_potongan, $data['total_gaji']);
                        
                        if ($stmt_insert->execute()) {
                            $penggajian_id = $conn->insert_id;
                            $count++;
                            foreach ($data['komponen_details'] as $comp) {
                                if ($comp['nama_komponen'] === 'Gaji Pokok') continue;
                                $stmt_insert_detail->bind_param("iissd", $penggajian_id, $comp['komponen_id'], $comp['nama_komponen'], $comp['jenis'], $comp['jumlah']);
                                $stmt_insert_detail->execute();
                            }
                        }
                    }
                    $check->close();
                }
            } else {
                // Standard calculation logic (for preview or direct generate without edit)
            while ($row = $res_karyawan->fetch_assoc()) {
                // Cek apakah sudah ada gaji untuk karyawan ini di periode ini
                $check = $conn->prepare("SELECT id FROM hr_penggajian WHERE karyawan_id = ? AND periode_bulan = ? AND periode_tahun = ?");
                $check->bind_param("iii", $row['id'], $bulan, $tahun);
                $check->execute();
                if ($check->get_result()->num_rows == 0) {
                    // Ambil jumlah kehadiran untuk karyawan ini pada periode ini
                    $stmt_absensi->bind_param("iii", $row['id'], $bulan, $tahun);
                    $stmt_absensi->execute();
                    $absensi_result = $stmt_absensi->get_result()->fetch_assoc();
                    $jumlah_hari_hadir = $absensi_result['hadir'] ?? 0;

                    $gaji_pokok = $row['gaji_pokok'] ?? 0;
                    $tunjangan_jabatan = $row['tunjangan'] ?? 0;
                    
                    $total_pendapatan_tambahan = 0;
                    $total_potongan = 0;
                    $komponen_details = [];

                    // 1. Tambahkan Tunjangan Jabatan sebagai komponen pendapatan tetap
                    if ($tunjangan_jabatan > 0) {
                        $total_pendapatan_tambahan += $tunjangan_jabatan;
                        $komponen_details[] = ['komponen_id' => 0, 'nama_komponen' => 'Tunjangan Jabatan', 'jenis' => 'pendapatan', 'jumlah' => $tunjangan_jabatan];
                    }
                    
                    // 2. Proses komponen gaji default
                    foreach ($default_components as $comp) {
                        $jumlah_komponen = $comp['nilai_default'];
                        $multiplier = 1;

                        // Hitung ulang jumlah jika tipe hitungnya harian
                        if ($comp['tipe_hitung'] === 'harian') {
                            $jumlah_komponen = $comp['nilai_default'] * $jumlah_hari_hadir;
                            $multiplier = $jumlah_hari_hadir;
                        }

                        if ($comp['jenis'] === 'pendapatan') {
                            $total_pendapatan_tambahan += $jumlah_komponen;
                        } else {
                            $total_potongan += $jumlah_komponen;
                        }

                        $komponen_details[] = [
                            'komponen_id' => $comp['id'], 
                            'nama_komponen' => $comp['nama_komponen'], 
                            'jenis' => $comp['jenis'], 
                            'jumlah' => $jumlah_komponen,
                            'tipe_hitung' => $comp['tipe_hitung'],
                            'nilai_satuan' => $comp['nilai_default'],
                            'multiplier' => $multiplier
                        ];
                    }

                    // --- Kalkulasi BPJS ---
                    $bpjs_kes_karyawan = 0;
                    $bpjs_tk_karyawan = 0;

                    if ($row['ikut_bpjs_kes'] == 1) {
                        $rate_kes = (float)($tax_settings['bpjs_kes_karyawan'] ?? 1.0) / 100;
                        $bpjs_kes_karyawan = $gaji_pokok * $rate_kes; // Basis biasanya Gapok + Tunjangan Tetap, disini pakai Gapok dulu
                        $total_potongan += $bpjs_kes_karyawan;
                        $komponen_details[] = ['komponen_id' => 0, 'nama_komponen' => 'Iuran BPJS Kesehatan', 'jenis' => 'potongan', 'jumlah' => $bpjs_kes_karyawan];
                    }

                    if ($row['ikut_bpjs_tk'] == 1) {
                        $rate_jht = (float)($tax_settings['bpjs_tk_jht_karyawan'] ?? 2.0) / 100;
                        $rate_jp = (float)($tax_settings['bpjs_tk_jp_karyawan'] ?? 1.0) / 100;
                        $bpjs_tk_karyawan = $gaji_pokok * ($rate_jht + $rate_jp);
                        $total_potongan += $bpjs_tk_karyawan;
                        $komponen_details[] = ['komponen_id' => 0, 'nama_komponen' => 'Iuran BPJS Ketenagakerjaan', 'jenis' => 'potongan', 'jumlah' => $bpjs_tk_karyawan];
                    }

                    // --- Kalkulasi PPh 21 Sederhana (Metode Gross) ---
                    // 1. Penghasilan Bruto Setahun
                    $bruto_sebulan = $gaji_pokok + $total_pendapatan_tambahan;
                    $bruto_setahun = $bruto_sebulan * 12;
                    
                    // 2. Pengurang (Biaya Jabatan 5% max 6jt/thn + Iuran Pensiun/JHT)
                    $biaya_jabatan = min($bruto_setahun * 0.05, 6000000);
                    $iuran_pensiun_setahun = ($bpjs_tk_karyawan) * 12; // Asumsi JHT/JP adalah pengurang
                    $neto_setahun = $bruto_setahun - $biaya_jabatan - $iuran_pensiun_setahun;

                    // 3. PTKP
                    $ptkp_key = 'ptkp_' . strtolower(str_replace('/', '', $row['status_ptkp']));
                    $ptkp = (float)($tax_settings[$ptkp_key] ?? 54000000);
                    
                    // 4. PKP (Penghasilan Kena Pajak)
                    $pkp = $neto_setahun - $ptkp;
                    $pkp = floor($pkp / 1000) * 1000; // Pembulatan ke bawah ribuan

                    $pph21_setahun = 0;
                    if ($pkp > 0) {
                        // Tarif Progresif 2024 (UU HPP)
                        if ($pkp <= 60000000) {
                            $pph21_setahun = $pkp * 0.05;
                        } elseif ($pkp <= 250000000) {
                            $pph21_setahun = (60000000 * 0.05) + (($pkp - 60000000) * 0.15);
                        } elseif ($pkp <= 500000000) {
                            $pph21_setahun = (60000000 * 0.05) + (190000000 * 0.15) + (($pkp - 250000000) * 0.25);
                        } else {
                            // Simplifikasi untuk > 500jt
                            $pph21_setahun = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + (($pkp - 500000000) * 0.30);
                        }
                    }

                    $pph21_sebulan = $pph21_setahun / 12;
                    
                    if ($pph21_sebulan > 0) {
                        $total_potongan += $pph21_sebulan;
                        $komponen_details[] = ['komponen_id' => 0, 'nama_komponen' => 'PPh 21', 'jenis' => 'potongan', 'jumlah' => $pph21_sebulan];
                    }

                    // Kalkulasi final
                    $total_gaji = $gaji_pokok + $total_pendapatan_tambahan - $total_potongan;

                    if ($is_preview) {
                        $preview_data[] = [
                            'karyawan_id' => $row['id'],
                            'nama_lengkap' => $row['nama_lengkap'],
                            'gaji_pokok' => $gaji_pokok,
                            'total_pendapatan' => $gaji_pokok + $total_pendapatan_tambahan,
                            'total_potongan' => $total_potongan,
                            'total_gaji' => $total_gaji,
                            'komponen_details' => $komponen_details,
                            'absensi' => $absensi_result // Tambahkan data absensi ke preview
                        ];
                    } else {
                        // Insert to DB
                        $stmt_insert->bind_param("iiidddd", $row['id'], $bulan, $tahun, $gaji_pokok, $total_pendapatan_tambahan, $total_potongan, $total_gaji);
                        if ($stmt_insert->execute()) {
                            $penggajian_id = $conn->insert_id;
                            $count++;

                            // Insert semua detail komponen
                            foreach ($komponen_details as $detail) {
                                $stmt_insert_detail->bind_param("iissd", $penggajian_id, $detail['komponen_id'], $detail['nama_komponen'], $detail['jenis'], $detail['jumlah']);
                                $stmt_insert_detail->execute();
                            }
                        }
                    }
                }
                $check->close();
            }
            } // End else (standard calculation)
            $stmt_absensi->close();

            if ($is_preview) {
                echo json_encode(['success' => true, 'data' => $preview_data]);
            } else {
                echo json_encode(['success' => true, 'message' => "Berhasil generate $count data gaji."]);
            }

        } elseif ($action === 'save') {
            // Validasi
            if (empty($_POST['karyawan_id']) || empty($_POST['periode_bulan']) || empty($_POST['periode_tahun'])) {
                throw new Exception("Data tidak lengkap.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $karyawan_id = (int)$_POST['karyawan_id'];
            $bulan = (int)$_POST['periode_bulan'];
            $tahun = (int)$_POST['periode_tahun'];
            $gaji_pokok = (float)$_POST['gaji_pokok'];
            $tunjangan = (float)$_POST['tunjangan'];
            $potongan = (float)$_POST['potongan'];
            $status = $_POST['status'] ?? 'draft';
            
            $total_gaji = $gaji_pokok + $tunjangan - $potongan;

            // Cek duplikasi jika insert baru
            if (!$id) {
                $check = $conn->prepare("SELECT id FROM hr_penggajian WHERE karyawan_id = ? AND periode_bulan = ? AND periode_tahun = ?");
                $check->bind_param("iii", $karyawan_id, $bulan, $tahun);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    throw new Exception("Data gaji untuk karyawan ini pada periode tersebut sudah ada.");
                }
            }
            
            $conn->begin_transaction();

            try {
                if ($id) {
                    // Update
                    $stmt = $conn->prepare("UPDATE hr_penggajian SET karyawan_id=?, periode_bulan=?, periode_tahun=?, gaji_pokok=?, tunjangan=?, potongan=?, total_gaji=?, status=? WHERE id=?");
                    $stmt->bind_param("iiiddddsi", $karyawan_id, $bulan, $tahun, $gaji_pokok, $tunjangan, $potongan, $total_gaji, $status, $id);
                    $penggajian_id = $id;
                } else {
                    // Insert
                    $stmt = $conn->prepare("INSERT INTO hr_penggajian (karyawan_id, periode_bulan, periode_tahun, gaji_pokok, tunjangan, potongan, total_gaji, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiidddds", $karyawan_id, $bulan, $tahun, $gaji_pokok, $tunjangan, $potongan, $total_gaji, $status);
                }

                if (!$stmt->execute()) {
                    throw new Exception("Gagal menyimpan data gaji utama: " . $stmt->error);
                }

                if (!$id) {
                    $penggajian_id = $conn->insert_id;
                }

                // Hapus komponen lama dan insert yang baru dari form manual
                $conn->query("DELETE FROM hr_penggajian_komponen WHERE penggajian_id = $penggajian_id");
                $stmt_insert_comp = $conn->prepare("INSERT INTO hr_penggajian_komponen (penggajian_id, komponen_id, nama_komponen, jenis, jumlah) VALUES (?, ?, ?, ?, ?)");
                $komponen_id_manual = 0; // ID 0 untuk komponen manual

                if ($tunjangan > 0) {
                    $stmt_insert_comp->bind_param("iissd", $penggajian_id, $komponen_id_manual, 'Tunjangan Lain-lain (Manual)', 'pendapatan', $tunjangan);
                    $stmt_insert_comp->execute();
                }
                if ($potongan > 0) {
                    $stmt_insert_comp->bind_param("iissd", $penggajian_id, $komponen_id_manual, 'Potongan Lain-lain (Manual)', 'potongan', $potongan);
                    $stmt_insert_comp->execute();
                }

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Data gaji berhasil disimpan.']);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e; // Re-throw exception to be caught by the outer try-catch
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            $stmt = $conn->prepare("DELETE FROM hr_penggajian WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data gaji berhasil dihapus.']);
            } else {
                throw new Exception("Gagal menghapus data: " . $stmt->error);
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}