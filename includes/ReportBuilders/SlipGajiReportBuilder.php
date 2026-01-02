<?php

class SlipGajiReportBuilder implements ReportBuilderInterface {
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
        $penggajian_id = isset($this->params['id']) ? (int)$this->params['id'] : 0;
        if ($penggajian_id === 0) {
            throw new Exception('ID Penggajian tidak valid.');
        }

        // Fetch main payroll data
        $stmt = $this->conn->prepare("
            SELECT 
                p.*, 
                k.nama_lengkap, k.nip, 
                j.nama_jabatan, 
                d.nama_divisi,
                gg.nama_golongan as nama_golongan_gaji
            FROM hr_penggajian p
            JOIN hr_karyawan k ON p.karyawan_id = k.id
            LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
            LEFT JOIN hr_divisi d ON k.divisi_id = d.id
            LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $penggajian_id);
        $stmt->execute();
        $penggajian = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$penggajian) {
            throw new Exception('Data penggajian tidak ditemukan.');
        }

        // Fetch component details
        $stmt_comp = $this->conn->prepare("SELECT * FROM hr_penggajian_komponen WHERE penggajian_id = ? ORDER BY jenis, nama_komponen");
        $stmt_comp->bind_param("i", $penggajian_id);
        $stmt_comp->execute();
        $komponen = $stmt_comp->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_comp->close();

        // Fetch settings for header
        $bulan_map = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $periode = $bulan_map[$penggajian['periode_bulan']] . ' ' . $penggajian['periode_tahun'];
 
        $this->pdf->AddPage(); 

        // --- Manual Header Rendering ---
        $this->pdf->SetFont('Helvetica', 'B', 12);
        $this->pdf->Cell(0, 7, 'SLIP GAJI KARYAWAN', 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->Cell(0, 5, 'Periode: ' . $periode, 0, 1, 'C');
        $this->pdf->Ln(4);

        // Employee Details
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->Cell(30, 5, 'Nama', 0, 0);
        $this->pdf->Cell(5, 5, ':', 0, 0, 'C');
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(0, 5, $penggajian['nama_lengkap'], 0, 1);
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->Cell(30, 5, 'NIP', 0, 0);
        $this->pdf->Cell(5, 5, ':', 0, 0, 'C');
        $this->pdf->Cell(0, 5, $penggajian['nip'], 0, 1);
        $this->pdf->Cell(30, 5, 'Jabatan', 0, 0);
        $this->pdf->Cell(5, 5, ':', 0, 0, 'C');
        $this->pdf->Cell(0, 5, $penggajian['nama_jabatan'] . ' (' . $penggajian['nama_golongan_gaji'] . ')', 0, 1);
        $this->pdf->Ln(4);

        // Salary Details
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(0, 7, 'RINCIAN PENDAPATAN', 0, 1, 'L', true);

        $this->pdf->SetFont('Helvetica', '', 10);
        $pendapatan = [['nama' => 'Gaji Pokok', 'jumlah' => $penggajian['gaji_pokok']]];
        $potongan = [];

        foreach ($komponen as $k) {
            if ($k['jenis'] == 'pendapatan') $pendapatan[] = ['nama' => $k['nama_komponen'], 'jumlah' => $k['jumlah']];
            else $potongan[] = ['nama' => $k['nama_komponen'], 'jumlah' => $k['jumlah']];
        }

        // Render Pendapatan
        foreach ($pendapatan as $item) {
            if ((float)$item['jumlah'] == 0) continue;
            $this->pdf->Cell(80, 5, $item['nama']);
            $this->pdf->Cell(48, 5, number_format($item['jumlah'], 0, ',', '.'), 0, 1, 'R');
        }
        $this->pdf->Ln(1);

        // Render Potongan
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(0, 7, 'RINCIAN POTONGAN', 0, 1, 'L', true);

        $this->pdf->SetFont('Helvetica', '', 10);
        $has_potongan = false;
        foreach ($potongan as $item) {
            if ((float)$item['jumlah'] > 0) {
                $has_potongan = true;
                $this->pdf->Cell(80, 5, $item['nama']);
                $this->pdf->Cell(48, 5, number_format($item['jumlah'], 0, ',', '.'), 0, 1, 'R');
            }
        }
        if (!$has_potongan) {
            $this->pdf->Cell(0, 5, 'Tidak ada potongan.', 0, 1);
        }
        $this->pdf->Ln(2);

        // Summary Box
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->Cell(80, 6, 'Total Pendapatan', 'T');
        $this->pdf->Cell(48, 6, number_format($penggajian['gaji_pokok'] + $penggajian['tunjangan'], 0, ',', '.'), 'T', 1, 'R');
        
        $this->pdf->Cell(80, 6, 'Total Potongan');
        $this->pdf->Cell(48, 6, number_format($penggajian['potongan'], 0, ',', '.'), 0, 1, 'R');
        
        $this->pdf->Ln(2);
        $this->pdf->SetFont('Helvetica', 'B', 12);
        $this->pdf->Cell(80, 8, 'GAJI BERSIH', 'T');
        $this->pdf->Cell(48, 8, number_format($penggajian['total_gaji'], 0, ',', '.'), 'T', 1, 'R');

        $this->pdf->signature_date = date('Y-m-d');
        $this->pdf->RenderSignatureBlock();
    }
}