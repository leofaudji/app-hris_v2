<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Manajemen Rekrutmen (ATS)</h1>
        <div class="flex gap-2">
            <button onclick="openVacancyModal()" class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="bi bi-plus-lg mr-2"></i>Buat Lowongan
            </button>
            <button onclick="openApplicantModal()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="bi bi-person-plus mr-2"></i>Input Pelamar
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="rekrutmenTabs" data-tabs-toggle="#rekrutmenTabContent" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500 rounded-t-lg" id="vacancies-tab" data-tabs-target="#vacancies" type="button" role="tab" aria-controls="vacancies" aria-selected="true">Daftar Lowongan</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="applicants-tab" data-tabs-target="#applicants" type="button" role="tab" aria-controls="applicants" aria-selected="false">Database Pelamar</button>
            </li>
        </ul>
    </div>

    <div id="rekrutmenTabContent">
        <!-- Tab Lowongan -->
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="vacancies" role="tabpanel" aria-labelledby="vacancies-tab">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="vacancy-list-container">
                <!-- Cards injected via JS -->
                <div class="text-center py-8 col-span-full text-gray-500">Memuat lowongan...</div>
            </div>
        </div>

        <!-- Tab Pelamar -->
        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="applicants" role="tabpanel" aria-labelledby="applicants-tab">
            <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-end">
                    <select id="filter-applicant-status" class="rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="applied">Applied</option>
                        <option value="screening">Screening</option>
                        <option value="interview">Interview</option>
                        <option value="offering">Offering</option>
                        <option value="hired">Hired</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelamar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posisi Dilamar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Dokumen</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="applicant-table-body" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr><td colspan="5" class="text-center py-4">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lowongan -->
<div id="vacancyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('vacancyModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="vacancy-form">
                <input type="hidden" name="action" value="save_vacancy">
                <input type="hidden" name="id" id="vacancy_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4" id="vacancy-modal-title">Form Lowongan</h3>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Lowongan</label>
                            <input type="text" name="judul" id="vac_judul" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                            <select name="jabatan_id" id="vac_jabatan_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Divisi</label>
                            <select name="divisi_id" id="vac_divisi_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kuota</label>
                            <input type="number" name="kuota" id="vac_kuota" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" value="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="status" id="vac_status" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                                <option value="buka">Buka</option>
                                <option value="tutup">Tutup</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="vac_tgl_mulai" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="vac_tgl_selesai" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi Pekerjaan</label>
                            <textarea name="deskripsi" id="vac_deskripsi" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm"></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kualifikasi</label>
                            <textarea name="kualifikasi" id="vac_kualifikasi" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="closeModal('vacancyModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pelamar -->
<div id="applicantModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('applicantModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="applicant-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_applicant">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Input Data Pelamar</h3>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posisi Dilamar</label>
                        <select name="lowongan_id" id="app_lowongan_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP</label>
                            <input type="text" name="no_hp" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload CV (PDF/Doc)</label>
                        <input type="file" name="file_cv" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600" accept=".pdf,.doc,.docx">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan / Ringkasan</label>
                        <textarea name="catatan" rows="2" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm"></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="closeModal('applicantModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hire (Convert to Employee) -->
<div id="hireModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('hireModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="hire-form">
                <input type="hidden" name="action" value="convert_to_employee">
                <input type="hidden" name="pelamar_id" id="hire_pelamar_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Konfirmasi Penerimaan Karyawan</h3>
                    <p class="text-sm text-gray-500 mb-4">Melengkapi data untuk <strong id="hire_nama_pelamar"></strong> sebelum masuk ke database karyawan.</p>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" name="nip" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required placeholder="Contoh: EMP2023099">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" id="hire_tanggal_masuk" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kantor Penempatan</label>
                        <select name="kantor_id" id="hire_kantor_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jadwal Kerja</label>
                        <select name="jadwal_kerja_id" id="hire_jadwal_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Golongan Gaji</label>
                        <select name="golongan_gaji_id" id="hire_golongan_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Karyawan</label>
                        <select name="status_karyawan" id="hire_status_karyawan" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                            <option value="aktif">Aktif (Tetap)</option>
                            <option value="kontrak">Kontrak</option>
                            <option value="probation">Probation</option>
                        </select>
                    </div>
                    <div class="mb-3 hidden" id="hire_end_date_container">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Berakhir <span id="hire_end_date_label">(Kontrak/Probation)</span></label>
                        <input type="date" name="tanggal_berakhir_kontrak" id="hire_tanggal_berakhir" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Proses & Simpan</button>
                    <button type="button" onclick="closeModal('hireModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload SPK Signed -->
<div id="uploadSPKModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('uploadSPKModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="upload-spk-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_signed_spk">
                <input type="hidden" name="id" id="upload_spk_pelamar_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload SPK Ditandatangani</h3>
                    <p class="text-sm text-gray-500 mb-4">Unggah dokumen SPK yang telah ditandatangani oleh pelamar <strong id="upload_spk_nama"></strong>.</p>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File SPK (PDF/JPG)</label>
                        <input type="file" name="file_spk" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600" required accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Upload</button>
                    <button type="button" onclick="closeModal('uploadSPKModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>