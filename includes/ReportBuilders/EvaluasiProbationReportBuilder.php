<?php
require_once __DIR__ . '/ReportBuilderInterface.php';

class EvaluasiProbationReportBuilder implements ReportBuilderInterface
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

        $sql = "SELECT ep.*, k.nama_lengkap, k.nip, k.tanggal_masuk, j.nama_jabatan, d.nama_divisi,
                u.nama_lengkap as nama_penilai
                FROM hr_evaluasi_probation ep
                JOIN hr_karyawan k ON ep.karyawan_id = k.id
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                LEFT JOIN hr_divisi d ON k.divisi_id = d.id
                LEFT JOIN users u ON ep.penilai_id = u.id
                WHERE ep.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) {
            throw new Exception("Data evaluasi tidak ditemukan.");
        }

        $this->pdf->report_title = 'HASIL EVALUASI MASA PERCOBAAN';
        $this->pdf->report_period = 'Tanggal Evaluasi: ' . date('d F Y', strtotime($data['tanggal_evaluasi']));
        $this->pdf->AddPage();

        $this->pdf->SetFont('Helvetica', '', 11);

        // Employee Info
        $this->pdf->Cell(40, 6, 'Nama Karyawan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_lengkap'], 0, 1);

        $this->pdf->Cell(40, 6, 'NIP', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nip'], 0, 1);

        $this->pdf->Cell(40, 6, 'Jabatan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_jabatan'] ?? '-', 0, 1);

        $this->pdf->Cell(40, 6, 'Divisi', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_divisi'] ?? '-', 0, 1);

        $this->pdf->Cell(40, 6, 'Tanggal Masuk', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, date('d F Y', strtotime($data['tanggal_masuk'])), 0, 1);

        $this->pdf->Ln(10);

        // Scores
        $this->pdf->SetFont('Helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'PENILAIAN', 0, 1);
        $this->pdf->SetFont('Helvetica', '', 11);

        $this->pdf->Cell(60, 8, 'Aspek Penilaian', 1, 0, 'C');
        $this->pdf->Cell(30, 8, 'Skor (0-100)', 1, 0, 'C');
        $this->pdf->Cell(100, 8, 'Keterangan', 1, 1, 'C');

        $this->pdf->Cell(60, 8, 'Kompetensi Teknis', 1, 0);
        $this->pdf->Cell(30, 8, $data['skor_teknis'], 1, 0, 'C');
        $this->pdf->Cell(100, 8, $this->getScoreDescription($data['skor_teknis']), 1, 1);

        $this->pdf->Cell(60, 8, 'Kecocokan Budaya', 1, 0);
        $this->pdf->Cell(30, 8, $data['skor_budaya'], 1, 0, 'C');
        $this->pdf->Cell(100, 8, $this->getScoreDescription($data['skor_budaya']), 1, 1);

        $avg = ($data['skor_teknis'] + $data['skor_budaya']) / 2;
        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(60, 8, 'Rata-rata', 1, 0, 'R');
        $this->pdf->Cell(30, 8, number_format($avg, 1), 1, 0, 'C');
        $this->pdf->Cell(100, 8, '', 1, 1);

        $this->pdf->Ln(10);

        // Recommendation
        $this->pdf->SetFont('Helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'REKOMENDASI', 0, 1);
        $this->pdf->SetFont('Helvetica', '', 11);
        
        $rekomendasi_text = '';
        switch ($data['rekomendasi']) {
            case 'angkat_tetap': $rekomendasi_text = 'Diangkat Menjadi Karyawan Tetap'; break;
            case 'perpanjang_probation': $rekomendasi_text = 'Perpanjangan Masa Percobaan'; break;
            case 'terminasi': $rekomendasi_text = 'Tidak Lulus Masa Percobaan (Terminasi)'; break;
            default: $rekomendasi_text = $data['rekomendasi'];
        }

        $this->pdf->MultiCell(0, 6, "Berdasarkan hasil evaluasi di atas, maka direkomendasikan bahwa karyawan tersebut:\n\n" . strtoupper($rekomendasi_text));
        $this->pdf->Ln(5);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'Catatan Tambahan:', 0, 1);
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->MultiCell(0, 6, $data['catatan'] ?: '-');

        $this->pdf->signature_date = $data['tanggal_evaluasi'];
        $this->pdf->RenderSignatureBlock();
    }

    private function getScoreDescription($score) {
        if ($score >= 90) return 'Sangat Baik';
        if ($score >= 80) return 'Baik';
        if ($score >= 70) return 'Cukup';
        if ($score >= 60) return 'Kurang';
        return 'Sangat Kurang';
    }
}