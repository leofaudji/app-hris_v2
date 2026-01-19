function initPenggajianPage() {
    if (document.getElementById('penggajian-table-body')) {
        loadPenggajian();
        loadKaryawanOptionsForPayroll();

        const btnTampilkan = document.getElementById('btn-tampilkan');
        if (btnTampilkan) btnTampilkan.addEventListener('click', loadPenggajian);

        const btnGenerate = document.getElementById('generate-gaji-btn');
        if (btnGenerate) btnGenerate.addEventListener('click', showPreviewPayroll);

        const saveBtn = document.getElementById('save-penggajian-btn');
        if (saveBtn) saveBtn.addEventListener('click', savePenggajian);

        const confirmGenerateBtn = document.getElementById('confirm-generate-gaji-btn');
        if (confirmGenerateBtn) confirmGenerateBtn.addEventListener('click', confirmAndGeneratePayroll);
    }
}

function loadPenggajian() {
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    const tbody = document.getElementById('penggajian-table-body');

    fetch(`${basePath}/api/hr/penggajian?bulan=${bulan}&tahun=${tahun}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data gaji untuk periode ini.</td></tr>';
                    return;
                }
                
                const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

                data.data.forEach(item => {
                    let statusBadge = item.status === 'final' 
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Final</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Draft</span>';

                    // Escape string untuk keamanan
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");

                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_lengkap}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_jabatan || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${formatter.format(item.gaji_pokok)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${formatter.format(item.tunjangan)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-500 dark:text-red-400">${formatter.format(item.potongan)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">${formatter.format(item.total_gaji)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="printSlipGaji(${item.id})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3" title="Cetak Slip Gaji"><i class="bi bi-printer-fill"></i></button>
                                <button onclick='editPenggajian(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                                <button onclick="deletePenggajian(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
        });
}

function loadKaryawanOptionsForPayroll() {
    fetch(`${basePath}/api/hr/karyawan?status=aktif`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('karyawan_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Karyawan</option>';
                    data.data.forEach(karyawan => {
                        select.insertAdjacentHTML('beforeend', `<option value="${karyawan.id}">${karyawan.nama_lengkap} (${karyawan.nip})</option>`);
                    });
                }
            }
        });
}

function showPreviewPayroll() {
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    const bulanNama = document.getElementById('filter-bulan').options[document.getElementById('filter-bulan').selectedIndex].text;

    openPreviewGajiModal();
    const previewContent = document.getElementById('preview-gaji-content');
    previewContent.innerHTML = `<div class="text-center p-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div><p class="mt-4 text-gray-500">Menghitung preview gaji untuk ${bulanNama} ${tahun}...</p></div>`;

    const formData = new FormData();
    formData.append('action', 'preview_generate');
    formData.append('bulan', bulan);
    formData.append('tahun', tahun);

    fetch(`${basePath}/api/hr/penggajian`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            renderPreviewTable(result.data);
        } else {
            previewContent.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">${result.message || 'Gagal memuat preview.'}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        previewContent.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">Terjadi kesalahan jaringan.</div>`;
    });
}

function renderPreviewTable(data) {
    const previewContent = document.getElementById('preview-gaji-content');
    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
    window.previewData = data; // Store data globally for editing

    if (data.length === 0) {
        previewContent.innerHTML = `<p class="text-center text-gray-500 py-8">Semua karyawan aktif sudah memiliki data gaji untuk periode ini.</p>`;
        document.getElementById('confirm-generate-gaji-btn').disabled = true;
        return;
    }
    
    document.getElementById('confirm-generate-gaji-btn').disabled = false;

    let accordionHtml = '<div class="space-y-3">';
    
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;

    data.forEach((karyawan, index) => {
        // --- Absensi ---
        const absensi = karyawan.absensi || { hadir: 0, sakit: 0, izin: 0, alpa: 0 };
        const tooltipHadir = absensi.tanggal_hadir ? `Hadir tgl: ${absensi.tanggal_hadir}` : 'Lihat Detail Hadir';
        const absensiHtml = `
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Ringkasan Absensi</h4>
                <div class="flex flex-wrap gap-2">
                    <a href="${basePath}/hr/absensi?karyawan_id=${karyawan.karyawan_id}&bulan=${bulan}&tahun=${tahun}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800 transition-colors" title="${tooltipHadir}">Hadir: <strong>${absensi.hadir || 0}</strong> <i class="bi bi-box-arrow-up-right ml-1"></i></a>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200" title="Sakit">Sakit: <strong>${absensi.sakit || 0}</strong></span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200" title="Izin">Izin: <strong>${absensi.izin || 0}</strong></span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200" title="Alpa">Alpa: <strong>${absensi.alpa || 0}</strong></span>
                </div>
            </div>
        `;

        // --- Rincian Gaji ---
        let pendapatanHtml = '';
        let potonganHtml = '';
        const totalPendapatan = karyawan.total_pendapatan;
        const totalPotongan = karyawan.total_potongan;

        pendapatanHtml += `
            <div class="flex justify-between items-center py-1.5">
                <span class="text-sm text-gray-800 dark:text-gray-300">Gaji Pokok</span>
                <input type="number" class="w-36 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600" 
                    value="${karyawan.gaji_pokok}" oninput="updatePreviewTotal(${index}, 'gaji_pokok', this.value)">
            </div>
        `;

        karyawan.komponen_details.forEach((komp, kIndex) => {
            let label = komp.nama_komponen;
            if (komp.tipe_hitung === 'harian') {
                const rate = parseFloat(komp.nilai_satuan) || 0;
                const rateFormatted = new Intl.NumberFormat('id-ID').format(rate);
                label += ` <div class="text-xs text-gray-500 font-normal">(${komp.multiplier} hari x ${rateFormatted})</div>`;
            }

            const componentRow = `
                <div class="flex justify-between items-center py-1.5">
                    <span class="text-sm text-gray-800 dark:text-gray-300">${label}</span>
                    <input type="number" class="w-36 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600" 
                        value="${komp.jumlah}" oninput="updatePreviewComponent(${index}, ${kIndex}, this.value)">
                </div>
            `;
            if (komp.jenis === 'pendapatan') {
                pendapatanHtml += componentRow;
            } else {
                potonganHtml += componentRow;
            }
        });

        const rincianGajiHtml = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div>
                    <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-2 border-b border-gray-200 dark:border-gray-600 pb-1">Pendapatan</h4>
                    <div class="space-y-1">${pendapatanHtml}</div>
                    <div class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-600 mt-2">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Total Pendapatan</span>
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200" id="total-pendapatan-${index}">${formatter.format(totalPendapatan)}</span>
                    </div>
                </div>
                <div>
                    <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-2 border-b border-gray-200 dark:border-gray-600 pb-1">Potongan</h4>
                    <div class="space-y-1">${potonganHtml || '<p class="text-sm text-gray-500 py-1.5">Tidak ada potongan.</p>'}</div>
                    <div class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-600 mt-2">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Total Potongan</span>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400" id="total-potongan-${index}">${formatter.format(totalPotongan)}</span>
                    </div>
                </div>
            </div>
        `;

        // --- Final Accordion Item ---
        accordionHtml += `
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800">
                <button type="button" class="w-full flex justify-between items-center p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50" onclick="toggleAccordion(this)">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">${karyawan.nama_lengkap}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Gaji Bersih: <span class="font-bold text-primary" id="total-gaji-header-${index}">${formatter.format(karyawan.total_gaji)}</span></p>
                    </div>
                    <i class="bi bi-chevron-down transform transition-transform text-gray-500"></i>
                </button>
                
                <div class="accordion-content hidden">
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        ${absensiHtml}
                        ${rincianGajiHtml}
                    </div>
                </div>
            </div>
        `;
    });

    accordionHtml += '</div>';
    previewContent.innerHTML = accordionHtml;

    // Add the toggleAccordion function to the window object so it's accessible from inline onclick
    if (!window.toggleAccordion) {
        window.toggleAccordion = function(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i.bi-chevron-down');
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    }
}

window.updatePreviewTotal = function(karyawanIndex, field, value) {
    const karyawan = window.previewData[karyawanIndex];
    karyawan[field] = parseFloat(value) || 0;
    recalculateTotal(karyawanIndex);
};

window.updatePreviewComponent = function(karyawanIndex, componentIndex, value) {
    const karyawan = window.previewData[karyawanIndex];
    karyawan.komponen_details[componentIndex].jumlah = parseFloat(value) || 0;
    recalculateTotal(karyawanIndex);
};

function recalculateTotal(index) {
    const karyawan = window.previewData[index];
    let totalPendapatan = karyawan.gaji_pokok;
    let totalPotongan = 0;
    
    karyawan.komponen_details.forEach(komp => {
        if (komp.jenis === 'pendapatan') {
            totalPendapatan += komp.jumlah;
        } else {
            totalPotongan += komp.jumlah;
        }
    });
    
    const totalGaji = totalPendapatan - totalPotongan;
    karyawan.total_gaji = totalGaji; // Update the data object

    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
    
    // Update all relevant UI elements
    document.getElementById(`total-gaji-header-${index}`).innerText = formatter.format(totalGaji);
    document.getElementById(`total-pendapatan-${index}`).innerText = formatter.format(totalPendapatan);
    document.getElementById(`total-potongan-${index}`).innerText = formatter.format(totalPotongan);
}

function confirmAndGeneratePayroll() {
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    const confirmBtn = document.getElementById('confirm-generate-gaji-btn');
    const originalBtnHtml = confirmBtn.innerHTML;

    confirmBtn.disabled = true;
    confirmBtn.innerHTML = `<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white inline-block mr-2"></div> Memproses...`;

    const formData = new FormData();
    formData.append('action', 'generate');
    formData.append('bulan', bulan);
    formData.append('tahun', tahun);
    formData.append('edited_data', JSON.stringify(window.previewData)); // Send edited data

    fetch(`${basePath}/api/hr/penggajian`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closePreviewGajiModal();
            loadPenggajian();
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Gagal generate gaji', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan jaringan.', 'error');
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnHtml;
    });
}

function openPenggajianModal(reset = true) {
    if (reset) {
        document.getElementById('penggajian-form').reset();
        document.getElementById('penggajian-id').value = '';
        document.getElementById('penggajian-action').value = 'save';
        document.getElementById('modal-title').innerText = 'Tambah Gaji Manual';
        
        // Set periode dari filter
        document.getElementById('form-periode-bulan').value = document.getElementById('filter-bulan').value;
        document.getElementById('form-periode-tahun').value = document.getElementById('filter-tahun').value;
    }
    document.getElementById('penggajianModal').classList.remove('hidden');
}

function closePenggajianModal() {
    document.getElementById('penggajianModal').classList.add('hidden');
}

function openPreviewGajiModal() {
    document.getElementById('previewGajiModal').classList.remove('hidden');
}

function closePreviewGajiModal() {
    document.getElementById('previewGajiModal').classList.add('hidden');
}

function editPenggajian(item) {
    openPenggajianModal(false);
    document.getElementById('penggajian-id').value = item.id;
    document.getElementById('penggajian-action').value = 'save';
    document.getElementById('modal-title').innerText = 'Edit Gaji';
    
    document.getElementById('form-periode-bulan').value = item.periode_bulan;
    document.getElementById('form-periode-tahun').value = item.periode_tahun;
    document.getElementById('karyawan_id').value = item.karyawan_id;
    document.getElementById('gaji_pokok').value = parseFloat(item.gaji_pokok);
    document.getElementById('tunjangan').value = parseFloat(item.tunjangan);
    document.getElementById('potongan').value = parseFloat(item.potongan);
    document.getElementById('status').value = item.status;
}

function savePenggajian() {
    const form = document.getElementById('penggajian-form');
    const formData = new FormData(form);
    
    // Jika periode kosong (misal tambah manual tanpa filter), ambil dari filter
    if (!formData.get('periode_bulan')) formData.set('periode_bulan', document.getElementById('filter-bulan').value);
    if (!formData.get('periode_tahun')) formData.set('periode_tahun', document.getElementById('filter-tahun').value);

    fetch(`${basePath}/api/hr/penggajian`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closePenggajianModal();
            loadPenggajian();
            showToast('Data gaji berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deletePenggajian(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data gaji ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/penggajian`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadPenggajian();
            showToast('Data gaji berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}

function printSlipGaji(id) {
    // Buat form sementara untuk mengirim data via POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank'; // Buka di tab baru

    const params = {
        report: 'slip-gaji',
        id: id
    };

    for (const key in params) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = key;
        hiddenField.value = params[key];
        form.appendChild(hiddenField);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}