<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex justify-between flex-wrap md:flex-nowrap items-center pt-3 pb-6 mb-6 border-b border-gray-200 dark:border-gray-700">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <span class="p-2 bg-blue-100 text-blue-600 rounded-lg dark:bg-blue-900/30 dark:text-blue-400">
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Sidebar Navigation (Sticky) -->
    <div class="lg:col-span-3">
        <nav class="sticky top-24 space-y-1" aria-label="Sidebar">
            <!-- Mobile: Horizontal Scroll, Desktop: Vertical Stack -->
            <div class="flex lg:flex-col overflow-x-auto lg:overflow-visible gap-2 pb-4 lg:pb-0 no-scrollbar" id="guide-nav">
                <button onclick="switchGuideTab('overview', this)" class="guide-nav-item active flex items-center w-full px-3 py-2 text-sm font-medium rounded-md transition-colors whitespace-nowrap bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                    <i class="bi bi-diagram-3-fill mr-3 text-lg"></i>
                    <span class="truncate">Overview & Alur</span>
                </button>
                
                <button onclick="switchGuideTab('finance', this)" class="guide-nav-item flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition-colors whitespace-nowrap">
                    <i class="bi bi-wallet2 mr-3 text-lg"></i>
                    <span class="truncate">Keuangan</span>
                </button>

                <button onclick="switchGuideTab('hr', this)" class="guide-nav-item flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition-colors whitespace-nowrap">
                    <i class="bi bi-people-fill mr-3 text-lg"></i>
                    <span class="truncate">HR: Kepegawaian</span>
                </button>

                <button onclick="switchGuideTab('absensi', this)" class="guide-nav-item flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition-colors whitespace-nowrap">
                    <i class="bi bi-calendar-check-fill mr-3 text-lg"></i>
                    <span class="truncate">HR: Absensi & Cuti</span>
                </button>

                <button onclick="switchGuideTab('payroll', this)" class="guide-nav-item flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition-colors whitespace-nowrap">
                    <i class="bi bi-cash-coin mr-3 text-lg"></i>
                    <span class="truncate">HR: Payroll</span>
                </button>

                <button onclick="switchGuideTab('stock', this)" class="guide-nav-item flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition-colors whitespace-nowrap">
                    <i class="bi bi-box-seam-fill mr-3 text-lg"></i>
                    <span class="truncate">Stok & Aset</span>
                </button>

                <button onclick="switchGuideTab('portal', this)" class="guide-nav-item flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition-colors whitespace-nowrap">
                    <i class="bi bi-person-workspace mr-3 text-lg"></i>
                    <span class="truncate">Portal Karyawan</span>
                </button>

                <button onclick="switchGuideTab('admin', this)" class="guide-nav-item flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition-colors whitespace-nowrap">
                    <i class="bi bi-gear-fill mr-3 text-lg"></i>
                    <span class="truncate">Administrasi</span>
                </button>
            </div>
        </nav>
    </div>

    <!-- Content Area -->
    <div class="lg:col-span-9">
        <!-- 1. OVERVIEW -->
        <div id="tab-overview" class="guide-content block animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Alur Kerja Sistem (Workflow)</h2>
                <p class="mb-6 text-gray-600 dark:text-gray-300">Aplikasi ini mengintegrasikan tiga pilar utama operasional bisnis: <strong>Keuangan</strong>, <strong>Sumber Daya Manusia (HR)</strong>, dan <strong>Inventaris</strong>. Memahami alur ini membantu Anda memaksimalkan efisiensi dan akurasi data.</p>
                <div class="mermaid-container overflow-x-auto p-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <pre class="mermaid text-center">
                    graph TD
                        classDef finance fill:#e0f2fe,stroke:#1e40af,stroke-width:2px,color:#1e3a8a
                        classDef hr fill:#fce7f3,stroke:#9d174d,stroke-width:2px,color:#831843
                        classDef stock fill:#dcfce7,stroke:#166534,stroke-width:2px,color:#14532d
                        classDef admin fill:#f3f4f6,stroke:#4b5563,stroke-width:2px,color:#374151

                        subgraph " "
                            direction TB
                            
                            subgraph "1. Setup & Master Data"
                                A1[Setup Akun & Saldo Awal]:::finance
                                A2[Data Karyawan & Struktur HR]:::hr
                                A3[Master Barang & Aset]:::stock
                            end

                            subgraph "2. Operasional Harian"
                                B1(Transaksi Kas & Jurnal):::finance
                                B2(Absensi & Pengajuan Cuti):::hr
                                B3(Pembelian & Penjualan):::stock
                            end

                            subgraph "3. Proses Periodik"
                                C1(Rekonsiliasi Bank):::finance
                                C2(Proses Penggajian):::hr
                                C3(Stok Opname & Penyusutan):::stock
                            end

                            subgraph "4. Laporan & Analisis"
                                D1>Laporan Keuangan]:::finance
                                D2>Slip Gaji & Laporan HR]:::hr
                                D3>Laporan Stok & Aset]:::stock
                            end
                        end

                        A1 --> B1
                        A2 --> B2
                        A3 --> B3

                        B1 --> C1
                        B2 --> C2
                        B3 --> C3

                        C1 --> D1
                        C2 --> D2
                        C3 --> D3
                    </pre>
                </div>
            </div>
        </div>

        <!-- 2. KEUANGAN -->
        <div id="tab-finance" class="guide-content hidden animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Modul Keuangan & Akuntansi</h2>
                <div class="space-y-8">
                    <!-- Fitur Utama -->
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-journal-check"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Jurnal Otomatis</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Setiap transaksi kas, pembelian, dan penjualan langsung terjurnal otomatis.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-bank2"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Rekonsiliasi Bank</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Cocokkan catatan sistem dengan rekening koran bank dengan mudah.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-graph-up-arrow"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Laporan Real-time</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Neraca, Laba Rugi, dan Arus Kas tersedia kapan saja secara instan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                            <h4 class="font-bold text-blue-700 dark:text-blue-400 text-sm flex items-center gap-2">
                                <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Pembayaran Listrik
                            </h4>
                        </div>
                        <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                            <div class="order-2 lg:order-1 space-y-5">
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Input Transaksi</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Staf mencatat pengeluaran Rp 300.000 untuk listrik di menu <strong>Transaksi Kas</strong>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Proses Sistem</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Sistem otomatis menjurnal: <br><span class="font-mono text-xs bg-gray-100 dark:bg-gray-900 px-1 rounded">(Dr) Beban Listrik / (Cr) Kas</span>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300 flex items-center justify-center font-bold text-sm">3</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Hasil Akhir</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Laporan Laba Rugi langsung mencatat beban, dan saldo Kas di Neraca berkurang.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="order-1 lg:order-2 flex justify-center bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <img src="https://raw.githubusercontent.com/CreatCodeBuild/asset/main/finance-flowchart.svg" alt="Financial Workflow" class="max-h-48 w-auto drop-shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. HR: KEPEGAWAIAN -->
        <div id="tab-hr" class="guide-content hidden animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Modul HRIS: Kepegawaian</h2>
                <div class="space-y-8">
                    <!-- Fitur Utama -->
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-800 dark:text-rose-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-person-vcard"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Database Lengkap</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Simpan data pribadi, kontrak, NPWP, hingga BPJS karyawan dalam satu tempat.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-800 dark:text-rose-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-diagram-3"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Struktur Organisasi</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Atur hierarki perusahaan dengan manajemen Divisi, Jabatan, dan Kantor.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-800 dark:text-rose-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-alarm"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Monitoring Kontrak</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dashboard otomatis menampilkan peringatan untuk kontrak yang akan habis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                            <h4 class="font-bold text-rose-700 dark:text-rose-400 text-sm flex items-center gap-2">
                                <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Karyawan Baru
                            </h4>
                        </div>
                        <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                            <div class="order-2 lg:order-1 space-y-5">
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900 dark:text-rose-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Input Data</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">HRD memasukkan data 'Citra' ke menu <strong>Karyawan</strong>, mengatur Jabatan 'Staff' dan Gaji Pokok.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-pink-100 text-pink-600 dark:bg-pink-900 dark:text-pink-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Integrasi</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Data Citra otomatis tersedia di modul Absensi dan Payroll. Akun Portal Karyawan juga dibuat.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="order-1 lg:order-2 flex justify-center bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <img src="https://raw.githubusercontent.com/CreatCodeBuild/asset/main/hris-flowchart.svg" alt="HRIS Workflow" class="max-h-48 w-auto drop-shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. HR: ABSENSI & CUTI -->
        <div id="tab-absensi" class="guide-content hidden animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Modul HRIS: Absensi & Cuti</h2>
                <div class="space-y-8">
                    <!-- Fitur Utama -->
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-clock-history"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Tracking Kehadiran</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Catat jam masuk/pulang, hitung keterlambatan, dan rekap kehadiran bulanan.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-calendar-heart"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Manajemen Cuti</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pengajuan cuti online, persetujuan berjenjang, dan pemotongan kuota otomatis.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-calendar3"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Kalender Visual</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat siapa yang cuti atau hadir dalam tampilan kalender yang intuitif.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-100 dark:border-purple-900/50 flex items-start gap-3">
                        <i class="bi bi-info-circle-fill text-purple-600 mt-1"></i>
                        <div>
                            <h5 class="font-bold text-purple-700 dark:text-purple-300 text-sm">Integrasi Payroll</h5>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Data absensi (jumlah kehadiran, keterlambatan) akan ditarik secara otomatis saat proses penggajian untuk menghitung tunjangan harian atau potongan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. HR: PAYROLL -->
        <div id="tab-payroll" class="guide-content hidden animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Modul HRIS: Payroll (Penggajian)</h2>
                <div class="space-y-8">
                    <!-- Fitur Utama -->
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-sliders"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Komponen Fleksibel</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Atur tunjangan tetap, harian, atau potongan sesuai kebijakan perusahaan.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-calculator"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Pajak & BPJS</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Perhitungan otomatis PPh 21 (TER) dan iuran BPJS Ketenagakerjaan/Kesehatan.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-receipt"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Slip Gaji Digital</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Generate slip gaji massal dan distribusikan langsung ke portal karyawan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                            <h4 class="font-bold text-amber-700 dark:text-amber-400 text-sm flex items-center gap-2">
                                <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Penggajian Akhir Bulan
                            </h4>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex gap-4 items-start">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900 dark:text-amber-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Generate</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">HRD klik "Generate Gaji". Sistem menarik Gaji Pokok, Tunjangan Jabatan, dan menghitung Tunjangan Makan berdasarkan jumlah kehadiran dari modul Absensi.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 items-start">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Kalkulasi Otomatis</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Sistem menghitung potongan BPJS dan PPh 21 sesuai status PTKP karyawan.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 items-start">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300 flex items-center justify-center font-bold text-sm">3</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Finalisasi</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Setelah review, HRD memfinalisasi gaji. Jurnal beban gaji terbentuk otomatis, dan slip gaji muncul di portal karyawan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. STOK & ASET -->
        <div id="tab-stock" class="guide-content hidden animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Modul Stok & Aset Tetap</h2>
                <div class="space-y-8">
                    <!-- Fitur Utama -->
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-800 dark:text-emerald-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-box-seam"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Stok Real-time</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Stok otomatis bertambah saat pembelian dan berkurang saat penjualan.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-800 dark:text-emerald-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-card-list"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Kartu Stok</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Audit trail lengkap untuk setiap pergerakan barang masuk dan keluar.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-800 dark:text-emerald-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-building-gear"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Aset & Depresiasi</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Manajemen aset tetap dengan perhitungan penyusutan otomatis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                            <h4 class="font-bold text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-2">
                                <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Stok Opname
                            </h4>
                        </div>
                        <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                            <div class="order-2 lg:order-1 space-y-5">
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Temuan Fisik</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Admin gudang menemukan selisih stok 'Buku Tulis' (Sistem: 100, Fisik: 98).</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 dark:bg-teal-900 dark:text-teal-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Penyesuaian</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Admin input stok fisik 98 di menu <strong>Stok Opname</strong>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300 flex items-center justify-center font-bold text-sm">3</span></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">Otomatisasi</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Sistem mengupdate stok, mencatat di Kartu Stok, dan membuat jurnal penyesuaian persediaan (kerugian).</p>
                                    </div>
                                </div>
                            </div>
                            <div class="order-1 lg:order-2 flex justify-center bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <img src="https://raw.githubusercontent.com/CreatCodeBuild/asset/main/inventory-flowchart.svg" alt="Inventory Workflow" class="max-h-48 w-auto drop-shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. PORTAL KARYAWAN -->
        <div id="tab-portal" class="guide-content hidden animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Portal Karyawan</h2>
                <div class="space-y-8">
                    <!-- Fitur Utama -->
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-800 dark:text-cyan-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-phone"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Akses Mandiri</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan dapat login untuk melihat data pribadi dan mengajukan cuti.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-800 dark:text-cyan-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-eye"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Transparansi</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat riwayat kehadiran dan sisa kuota cuti secara real-time.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-800 dark:text-cyan-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-download"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Unduh Dokumen</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Download slip gaji bulanan dalam format PDF kapan saja.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                            <h4 class="font-bold text-cyan-700 dark:text-cyan-400 text-sm flex items-center gap-2">
                                <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Cek Slip Gaji
                            </h4>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                Karyawan bernama 'Budi' ingin memeriksa rincian gajinya bulan lalu untuk keperluan pengajuan kredit.
                            </p>
                            <div class="flex items-center gap-4 p-4 bg-cyan-50 dark:bg-cyan-900/20 rounded-lg border border-cyan-100 dark:border-cyan-800">
                                <i class="bi bi-phone-vibrate text-2xl text-cyan-600"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Budi login ke aplikasi -> Buka menu <strong>Slip Gaji</strong> -> Pilih Periode -> Klik <strong>Download PDF</strong>. <br>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic">Tanpa perlu menghubungi HRD secara manual.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. ADMINISTRASI -->
        <div id="tab-admin" class="guide-content hidden animate-fade-in">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Administrasi & Pengaturan</h2>
                <div class="space-y-8">
                    <!-- Fitur Utama -->
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 flex items-center justify-center mb-3 text-lg"><i class="bi bi-shield-lock"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Role Management</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Batasi akses menu berdasarkan peran (misal: Kasir, HRD, Manager).</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 flex items-center justify-center mb-3 text-lg"><i class="bi bi-activity"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Audit Log</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pantau setiap aktivitas login dan perubahan data oleh pengguna.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
                                <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 flex items-center justify-center mb-3 text-lg"><i class="bi bi-archive"></i></div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Tutup Buku</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Proses akhir tahun untuk memindahkan laba rugi ke modal secara otomatis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                            <h4 class="font-bold text-slate-700 dark:text-slate-400 text-sm flex items-center gap-2">
                                <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Pembatasan Akses
                            </h4>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                Perusahaan ingin agar <strong>Staf Akuntansi</strong> hanya fokus pada keuangan dan tidak bisa melihat data gaji karyawan lain.
                            </p>
                            <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                <i class="bi bi-person-lock text-2xl text-slate-600"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Admin membuat role 'Accounting' -> Centang menu 'Transaksi', 'Akuntansi', 'Laporan' -> Hapus centang menu 'HR & Payroll'. <br>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic">Hasil: Staf Akuntansi aman bekerja tanpa risiko kebocoran data HR.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchGuideTab(tabId, button) {
        // Hide all contents
        document.querySelectorAll('.guide-content').forEach(el => el.classList.add('hidden'));
        // Show selected content
        document.getElementById('tab-' + tabId).classList.remove('hidden');

        // Reset all buttons
        document.querySelectorAll('.guide-nav-item').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'text-blue-700', 'dark:bg-blue-900/20', 'dark:text-blue-300');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900', 'dark:text-gray-300', 'dark:hover:bg-gray-800', 'dark:hover:text-white');
        });

        // Highlight active button
        button.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900', 'dark:text-gray-300', 'dark:hover:bg-gray-800', 'dark:hover:text-white');
        button.classList.add('bg-blue-50', 'text-blue-700', 'dark:bg-blue-900/20', 'dark:text-blue-300');
        
        // Scroll to top of content on mobile
        if (window.innerWidth < 1024) {
             document.getElementById('tab-' + tabId).scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
</script>

<!-- Mermaid.js untuk merender diagram -->
<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ securityLevel: 'loose', theme: document.documentElement.classList.contains('dark') ? 'dark' : 'neutral' });
</script>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>