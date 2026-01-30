<?php
require_once __DIR__ . '/ReportBuilderInterface.php';

class RaporKPIReportBuilder implements ReportBuilderInterface
{
    private $pdf;
    private $conn;
    private $params;

    public function __construct(PDF $pdf, mysqli $conn, array $params)
    {
        $this->pdf = $pdf;
        $this->conn = $conn;
        $this->params = $params;
    }

    public function build(): void
    {
        $id = isset($this->params['id']) ? (int)$this->params['id'] : 0;

        // Fetch Appraisal Header
        $sql = "SELECT p.*, k.nama_lengkap, k.nip, k.tanggal_masuk, j.nama_jabatan, d.nama_divisi, t.nama_template,
                u.nama_lengkap as nama_penilai
                FROM hr_penilaian_kinerja p
                JOIN hr_karyawan k ON p.karyawan_id = k.id
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                LEFT JOIN hr_divisi d ON k.divisi_id = d.id
                JOIN hr_kpi_templates t ON p.template_id = t.id
                LEFT JOIN users u ON p.penilai_id = u.id
                WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $header = $stmt->get_result()->fetch_assoc();

        if (!$header) {
            $this->pdf->AddPage();
            $this->pdf->SetFont('Helvetica', '', 12);
            $this->pdf->Cell(0, 10, 'Data penilaian tidak ditemukan.', 0, 1, 'C');
            return;
        }

        // Fetch Appraisal Details
        $sql_details = "SELECT d.*, i.indikator, i.bobot 
                        FROM hr_penilaian_detail d 
                        JOIN hr_kpi_indicators i ON d.indikator_id = i.id 
                        WHERE d.penilaian_id = ?";
        $stmt_details = $this->conn->prepare($sql_details);
        $stmt_details->bind_param('i', $id);
        $stmt_details->execute();
        $details = $stmt_details->get_result()->fetch_all(MYSQLI_ASSOC);

        $this->pdf->report_title = 'RAPOR PENILAIAN KINERJA (KPI)';
        $this->pdf->report_period = 'Periode: ' . date('F Y', mktime(0, 0, 0, $header['periode_bulan'], 1, $header['periode_tahun']));
        $this->pdf->AddPage();

        // Employee Info
        $this->pdf->SetFont('Helvetica', '', 10);
        
        $x_start = 10;
        $y_start = $this->pdf->GetY();
        
        // Left Column
        $this->pdf->SetXY($x_start, $y_start);
        $this->pdf->Cell(30, 6, 'Nama', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(60, 6, $header['nama_lengkap'], 0, 1);
        
        $this->pdf->Cell(30, 6, 'NIP', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(60, 6, $header['nip'], 0, 1);

        $this->pdf->Cell(30, 6, 'Jabatan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(60, 6, $header['nama_jabatan'] ?? '-', 0, 1);

        // Right Column
        $this->pdf->SetXY($x_start + 100, $y_start);
        $this->pdf->Cell(30, 6, 'Divisi', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(60, 6, $header['nama_divisi'] ?? '-', 0, 1);

        $this->pdf->SetXY($x_start + 100, $y_start + 6);
        $this->pdf->Cell(30, 6, 'Tgl Penilaian', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(60, 6, date('d-m-Y', strtotime($header['tanggal_penilaian'])), 0, 1);

        $this->pdf->SetXY($x_start + 100, $y_start + 12);
        $this->pdf->Cell(30, 6, 'Penilai', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(60, 6, $header['nama_penilai'] ?? '-', 0, 1);

        $this->pdf->Ln(10);

        // Table Header
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
        $this->pdf->Cell(70, 8, 'Indikator Kinerja', 1, 0, 'L', true);
        $this->pdf->Cell(20, 8, 'Bobot', 1, 0, 'C', true);
        $this->pdf->Cell(20, 8, 'Skor', 1, 0, 'C', true);
        $this->pdf->Cell(25, 8, 'Nilai Akhir', 1, 0, 'C', true);
        $this->pdf->Cell(45, 8, 'Komentar', 1, 1, 'L', true);

        // Table Body
        $this->pdf->SetFont('Helvetica', '', 9);
        $no = 1;
        
        foreach ($details as $row) {
            $nilai_akhir = ($row['skor'] * $row['bobot']) / 100;
            
            $this->pdf->Cell(10, 8, $no++, 1, 0, 'C');
            $this->pdf->Cell(70, 8, $row['indikator'], 1, 0, 'L');
            $this->pdf->Cell(20, 8, $row['bobot'] . '%', 1, 0, 'C');
            $this->pdf->Cell(20, 8, $row['skor'], 1, 0, 'C');
            $this->pdf->Cell(25, 8, number_format($nilai_akhir, 2), 1, 0, 'C');
            
            // Truncate comment if too long to keep row height consistent
            $komentar = $row['komentar'] ?? '';
            if (strlen($komentar) > 25) {
                $komentar = substr($komentar, 0, 22) . '...';
            }
            $this->pdf->Cell(45, 8, $komentar, 1, 1, 'L');
        }

        // Total Row
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(120, 8, 'Total Skor Akhir', 1, 0, 'R');
        $this->pdf->Cell(25, 8, number_format($header['total_skor'], 2), 1, 0, 'C');
        $this->pdf->Cell(45, 8, '', 1, 1, 'C');

        // Catatan Umum
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, 'Catatan Umum / Rekomendasi:', 0, 1);
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->MultiCell(0, 6, $header['catatan'] ?: '-', 1, 'L');

        // Signatures
        $this->pdf->Ln(15);
        $y = $this->pdf->GetY();
        
        if ($y + 40 > 270) {
            $this->pdf->AddPage();
            $y = $this->pdf->GetY();
        }

        $this->pdf->Cell(63, 6, 'Dibuat Oleh,', 0, 0, 'C');
        $this->pdf->Cell(63, 6, 'Mengetahui,', 0, 0, 'C');
        $this->pdf->Cell(63, 6, 'Karyawan,', 0, 1, 'C');

        $this->pdf->Ln(20);

        $this->pdf->Cell(63, 6, $header['nama_penilai'] ?? '(...................)', 0, 0, 'C');
        $this->pdf->Cell(63, 6, '(...................)', 0, 0, 'C');
        $this->pdf->Cell(63, 6, $header['nama_lengkap'], 0, 1, 'C');
        
        $this->pdf->SetFont('Helvetica', 'I', 8);
        $this->pdf->Cell(63, 4, 'Penilai', 0, 0, 'C');
        $this->pdf->Cell(63, 4, 'Atasan / HR', 0, 0, 'C');
        $this->pdf->Cell(63, 4, 'Yang Dinilai', 0, 1, 'C');
    }
}