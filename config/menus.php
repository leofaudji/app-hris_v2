<?php
// Konfigurasi Struktur Menu Aplikasi
// Key harus unik untuk setiap item menu

return [
    ['type' => 'item', 'key' => 'dashboard', 'label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'bi bi-speedometer2'],
    ['type' => 'item', 'key' => 'buku_panduan', 'label' => 'Buku Panduan', 'url' => '/buku-panduan', 'icon' => 'bi bi-question-circle-fill'],
    
    ['type' => 'header', 'label' => 'Aktivitas Utama'],
    
    ['type' => 'collapse', 'key' => 'transaksi', 'label' => 'Transaksi', 'icon' => 'bi bi-pencil-square', 'children' => [
        ['key' => 'penjualan', 'label' => 'Penjualan', 'url' => '/penjualan', 'icon' => 'bi bi-cart-plus'],
        ['key' => 'pembelian', 'label' => 'Pembelian', 'url' => '/pembelian', 'icon' => 'bi bi-bag-plus'],
        ['key' => 'transaksi_kas', 'label' => 'Transaksi Kas', 'url' => '/transaksi', 'icon' => 'bi bi-cash-stack'],
        ['key' => 'entri_jurnal', 'label' => 'Entri Jurnal', 'url' => '/entri-jurnal', 'icon' => 'bi bi-journal-plus'],
    ]],
    
    ['type' => 'collapse', 'key' => 'akuntansi', 'label' => 'Akuntansi', 'icon' => 'bi bi-calculator', 'children' => [
        ['key' => 'coa', 'label' => 'Bagan Akun (COA)', 'url' => '/coa', 'icon' => 'bi bi-list-columns-reverse'],
        ['key' => 'saldo_awal', 'label' => 'Saldo Awal', 'url' => '/saldo-awal', 'icon' => 'bi bi-bank'],
        ['key' => 'anggaran', 'label' => 'Anggaran', 'url' => '/anggaran', 'icon' => 'bi bi-pie-chart'],
        ['key' => 'daftar_jurnal', 'label' => 'Daftar Jurnal', 'url' => '/daftar-jurnal', 'icon' => 'bi bi-journal-text'],
        ['key' => 'buku_besar', 'label' => 'Buku Besar', 'url' => '/buku-besar', 'icon' => 'bi bi-book'],
    ]],

    ['type' => 'collapse', 'key' => 'stok', 'label' => 'Stok & Inventaris', 'icon' => 'bi bi-box-seam', 'children' => [
        ['key' => 'barang_stok', 'label' => 'Barang & Stok', 'url' => '/stok', 'icon' => 'bi bi-box-seam'],
        ['key' => 'stok_opname', 'label' => 'Stok Opname', 'url' => '/stok-opname', 'icon' => 'bi bi-clipboard-check'],
        ['key' => 'laporan_stok', 'label' => 'Laporan Stok', 'url' => '/laporan-stok', 'icon' => 'bi bi-file-earmark-bar-graph'],
        ['key' => 'kartu_stok', 'label' => 'Kartu Stok', 'url' => '/laporan-kartu-stok', 'icon' => 'bi bi-card-list'],
        ['key' => 'nilai_persediaan', 'label' => 'Nilai Persediaan', 'url' => '/laporan-persediaan', 'icon' => 'bi bi-currency-dollar'],
        ['key' => 'pertumbuhan_persediaan', 'label' => 'Pertumbuhan Persediaan', 'url' => '/laporan-pertumbuhan-persediaan', 'icon' => 'bi bi-graph-up-arrow'],
        ['key' => 'aset_tetap', 'label' => 'Aset Tetap', 'url' => '/aset-tetap', 'icon' => 'bi bi-building'],
    ]],

    ['type' => 'header', 'label' => 'HR & Payroll'],
    
    ['type' => 'collapse', 'key' => 'hr_kepegawaian', 'label' => 'Kepegawaian', 'icon' => 'bi bi-people', 'children' => [
        ['key' => 'hr_karyawan', 'label' => 'Data Karyawan', 'url' => '/hr/karyawan', 'icon' => 'bi bi-people'],
        ['key' => 'hr_absensi', 'label' => 'Absensi', 'url' => '/hr/absensi', 'icon' => 'bi bi-calendar-check'],
        ['key' => 'hr_manajemen_cuti', 'label' => 'Manajemen Cuti', 'url' => '/hr/manajemen-cuti', 'icon' => 'bi bi-calendar-range'],
        ['key' => 'hr_kalender_cuti', 'label' => 'Kalender Cuti', 'url' => '/hr/kalender-cuti', 'icon' => 'bi bi-calendar3'],
    ]],

    ['type' => 'collapse', 'key' => 'hr_master', 'label' => 'Master Data HR', 'icon' => 'bi bi-database-gear', 'children' => [
        ['key' => 'hr_master_dashboard', 'label' => 'Dashboard Master', 'url' => '/hr/master-dashboard', 'icon' => 'bi bi-speedometer'],
        ['key' => 'hr_jabatan', 'label' => 'Jabatan', 'url' => '/hr/jabatan', 'icon' => 'bi bi-briefcase'],
        ['key' => 'hr_divisi', 'label' => 'Divisi', 'url' => '/hr/divisi', 'icon' => 'bi bi-diagram-3'],
        ['key' => 'hr_kantor', 'label' => 'Kantor', 'url' => '/hr/kantor', 'icon' => 'bi bi-building'],
        ['key' => 'hr_jadwal_kerja', 'label' => 'Jadwal Kerja', 'url' => '/hr/jadwal-kerja', 'icon' => 'bi bi-clock'],
        ['key' => 'hr_golongan_absensi', 'label' => 'Golongan Absensi', 'url' => '/hr/golongan-absensi', 'icon' => 'bi bi-tags'],
        ['key' => 'hr_status_absensi', 'label' => 'Status Absensi', 'url' => '/hr/status-absensi', 'icon' => 'bi bi-check-circle'],
        ['key' => 'hr_jenis_cuti', 'label' => 'Jenis Cuti', 'url' => '/hr/jenis-cuti', 'icon' => 'bi bi-calendar-event'],
    ]],

    ['type' => 'collapse', 'key' => 'hr_payroll', 'label' => 'Penggajian', 'icon' => 'bi bi-cash-coin', 'children' => [
        ['key' => 'hr_payroll_dashboard', 'label' => 'Dashboard Penggajian', 'url' => '/hr/payroll-dashboard', 'icon' => 'bi bi-speedometer2'],
        ['key' => 'hr_penggajian', 'label' => 'Proses Gaji', 'url' => '/hr/penggajian', 'icon' => 'bi bi-cash'],
        ['key' => 'hr_laporan', 'label' => 'Laporan Gaji', 'url' => '/hr/laporan', 'icon' => 'bi bi-file-earmark-spreadsheet'],
        ['key' => 'hr_komponen_gaji', 'label' => 'Komponen Gaji', 'url' => '/hr/komponen-gaji', 'icon' => 'bi bi-puzzle'],
        ['key' => 'hr_golongan_gaji', 'label' => 'Golongan Gaji', 'url' => '/hr/golongan-gaji', 'icon' => 'bi bi-layers'],
        ['key' => 'hr_pengaturan_pajak', 'label' => 'Pengaturan Pajak', 'url' => '/hr/pengaturan-pajak', 'icon' => 'bi bi-calculator'],
    ]],

    ['type' => 'collapse', 'key' => 'portal_karyawan', 'label' => 'Portal Karyawan', 'icon' => 'bi bi-person-workspace', 'children' => [
        ['key' => 'portal_dashboard', 'label' => 'Dashboard', 'url' => '/hr/portal/dashboard', 'icon' => 'bi bi-speedometer'],
        ['key' => 'portal_profil', 'label' => 'Profil Saya', 'url' => '/hr/portal/profil', 'icon' => 'bi bi-person-badge'],
        ['key' => 'portal_absensi', 'label' => 'Data Absensi', 'url' => '/hr/portal/absensi', 'icon' => 'bi bi-calendar-check'],
        ['key' => 'portal_slip_gaji', 'label' => 'Slip Gaji', 'url' => '/hr/portal/slip-gaji', 'icon' => 'bi bi-receipt'],
    ]],

    ['type' => 'header', 'label' => 'Akuntansi & Laporan'],

    ['type' => 'collapse', 'key' => 'laporan', 'label' => 'Laporan', 'icon' => 'bi bi-bar-chart-line-fill', 'children' => [
        ['key' => 'laporan_harian', 'label' => 'Laporan Harian', 'url' => '/laporan-harian', 'icon' => 'bi bi-calendar-day'],
        ['key' => 'penjualan_item', 'label' => 'Penjualan per Item', 'url' => '/laporan-penjualan-item', 'icon' => 'bi bi-cart-check'],
        ['key' => 'laporan_penjualan', 'label' => 'Laporan Penjualan', 'url' => '/laporan-penjualan', 'icon' => 'bi bi-file-earmark-text'],
        ['key' => 'laporan_keuangan', 'label' => 'Laporan Keuangan', 'url' => '/laporan', 'icon' => 'bi bi-file-earmark-bar-graph'],
        ['key' => 'neraca_saldo', 'label' => 'Neraca Saldo', 'url' => '/neraca-saldo', 'icon' => 'bi bi-scale'],
        ['key' => 'perubahan_laba', 'label' => 'Perubahan Laba', 'url' => '/laporan-laba-ditahan', 'icon' => 'bi bi-graph-up'],
        ['key' => 'pertumbuhan_laba', 'label' => 'Pertumbuhan Laba', 'url' => '/laporan-pertumbuhan-laba', 'icon' => 'bi bi-graph-up-arrow'],
        ['key' => 'analisis_rasio', 'label' => 'Analisis Rasio', 'url' => '/analisis-rasio', 'icon' => 'bi bi-pie-chart-fill'],
    ]],

    ['type' => 'collapse', 'key' => 'tools', 'label' => 'Alat & Proses', 'icon' => 'bi bi-tools', 'children' => [
        ['key' => 'transaksi_berulang', 'label' => 'Transaksi Berulang', 'url' => '/transaksi-berulang', 'icon' => 'bi bi-arrow-repeat'],
        ['key' => 'rekonsiliasi_bank', 'label' => 'Rekonsiliasi Bank', 'url' => '/rekonsiliasi-bank', 'icon' => 'bi bi-bank2'],
    ]],

    ['type' => 'header', 'label' => 'Administrasi'],

    ['type' => 'item', 'key' => 'users', 'label' => 'Users', 'url' => '/users', 'icon' => 'bi bi-people-fill'],
    ['type' => 'item', 'key' => 'roles', 'label' => 'Manajemen Role', 'url' => '/roles', 'icon' => 'bi bi-shield-lock-fill'],
    ['type' => 'item', 'key' => 'activity_log', 'label' => 'Log Aktivitas', 'url' => '/activity-log', 'icon' => 'bi bi-list-check'],
    ['type' => 'item', 'key' => 'tutup_buku', 'label' => 'Tutup Buku', 'url' => '/tutup-buku', 'icon' => 'bi bi-archive-fill'],
    ['type' => 'item', 'key' => 'settings', 'label' => 'Pengaturan', 'url' => '/settings', 'icon' => 'bi bi-gear-fill'],
];
