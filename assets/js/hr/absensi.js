let activeDateFilter = null;

function initAbsensiPage() {
    if (document.getElementById('absensi-table-body')) {
        // --- Transformasi UI: Ganti input tanggal dengan filter bulan/tahun jika masih menggunakan input date ---
        const filterTanggal = document.getElementById('filter-tanggal');
        if (filterTanggal) {
            const container = filterTanggal.parentElement;
            const wrapper = document.createElement('div');
            wrapper.className = 'flex gap-2'; // Tailwind flex

            // Select Bulan
            const monthSelect = document.createElement('select');
            monthSelect.id = 'filter-bulan';
            monthSelect.className = 'rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm';
            const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const currentMonth = new Date().getMonth() + 1;
            months.forEach((m, i) => {
                const option = new Option(m, i + 1);
                if (i + 1 === currentMonth) option.selected = true;
                monthSelect.add(option);
            });

            // Select Tahun
            const yearSelect = document.createElement('select');
            yearSelect.id = 'filter-tahun';
            yearSelect.className = 'rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm';
            const currentYear = new Date().getFullYear();
            for (let i = 0; i < 5; i++) {
                const year = currentYear - i;
                yearSelect.add(new Option(year, year));
            }

            wrapper.appendChild(monthSelect);
            wrapper.appendChild(yearSelect);
            
            // Ganti input tanggal dengan wrapper baru
            container.replaceChild(wrapper, filterTanggal);
        }
        // -----------------------------------------------------------------------------------

        loadAbsensi();
        loadKaryawanOptionsForAbsensi();
        loadGolonganOptions();
        loadDivisiOptionsForFilter();
        loadStatusOptions();

        const searchInput = document.getElementById('search-absensi');
        if (searchInput) searchInput.addEventListener('input', debounce(loadAbsensi, 500));

        const filterBulan = document.getElementById('filter-bulan');
        if (filterBulan) filterBulan.addEventListener('change', () => {
            activeDateFilter = null; // Reset filter tanggal saat bulan berubah
            loadAbsensi();
        });

        const filterTahun = document.getElementById('filter-tahun');
        if (filterTahun) filterTahun.addEventListener('change', () => {
            activeDateFilter = null; // Reset filter tanggal saat tahun berubah
            loadAbsensi();
        });

        const filterDivisi = document.getElementById('filter-divisi');
        if (filterDivisi) filterDivisi.addEventListener('change', loadAbsensi);

        const filterStatus = document.getElementById('filter-status-absensi');
        if (filterStatus) filterStatus.addEventListener('change', loadAbsensi);

        const saveBtn = document.getElementById('save-absensi-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveAbsensi);
    }
}

function loadAbsensi() {
    const search = document.getElementById('search-absensi').value;
    const filterBulan = document.getElementById('filter-bulan');
    const filterTahun = document.getElementById('filter-tahun');
    const divisiId = document.getElementById('filter-divisi') ? document.getElementById('filter-divisi').value : '';
    const status = document.getElementById('filter-status-absensi').value;
    const tbody = document.getElementById('absensi-table-body');

    let url = `${basePath}/api/hr/absensi?search=${encodeURIComponent(search)}`;
    
    // Cek parameter URL untuk filter (misal dari link penggajian)
    const urlParams = new URLSearchParams(window.location.search);
    const paramBulan = urlParams.get('bulan');
    const paramTahun = urlParams.get('tahun');
    const paramKaryawanId = urlParams.get('karyawan_id');

    if (activeDateFilter) {
        url += `&tanggal=${activeDateFilter}`;
        const infoEl = document.getElementById('absensi-info');
        if(infoEl) infoEl.innerHTML = `Menampilkan data tanggal: <strong>${formatDate(activeDateFilter)}</strong> <button onclick="resetDateFilter()" class="text-xs text-red-500 underline ml-2 hover:text-red-700">Reset Filter</button>`;
    } else if (filterBulan && filterTahun && filterBulan.value && filterTahun.value) {
        url += `&bulan=${filterBulan.value}&tahun=${filterTahun.value}`;
        const infoEl = document.getElementById('absensi-info');
        if(infoEl) infoEl.textContent = 'Menampilkan data terbaru';
    } else if (paramBulan && paramTahun) {
        url += `&bulan=${paramBulan}&tahun=${paramTahun}`;
        if (filterBulan) filterBulan.value = paramBulan;
        if (filterTahun) filterTahun.value = paramTahun;
    }

    if (divisiId) url += `&divisi_id=${divisiId}`;
    if (status) url += `&status=${status}`;
    if (paramKaryawanId) url += `&karyawan_id=${paramKaryawanId}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hitung Ringkasan
                const summary = { hadir: 0, sakit: 0, izin: 0, alpa: 0 };
                const totalRecords = data.data ? data.data.length : 0;
                if (data.data && Array.isArray(data.data)) {
                    data.data.forEach(item => {
                        const s = (item.status || '').toLowerCase();
                        if (summary.hasOwnProperty(s)) summary[s]++;
                    });
                }
                updateAbsensiSummary(summary, totalRecords, data.total_karyawan);
                renderAbsensiChart(data.chart_data);
                renderTopEmployees(data.top_employees);

                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data absensi untuk filter ini.</td></tr>';
                    return;
                }
                
                data.data.forEach(item => {
                    // Helper untuk inisial nama
                    const getInitials = (name) => name ? name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : '?';
                    
                    // Icon status
                    let statusIcon = '';
                    switch((item.status || '').toLowerCase()) {
                        case 'hadir': statusIcon = '<i class="bi bi-check-circle-fill mr-1.5"></i>'; break;
                        case 'sakit': statusIcon = '<i class="bi bi-bandaid-fill mr-1.5"></i>'; break;
                        case 'izin': statusIcon = '<i class="bi bi-chat-left-text-fill mr-1.5"></i>'; break;
                        case 'alpa': statusIcon = '<i class="bi bi-x-circle-fill mr-1.5"></i>'; break;
                        default: statusIcon = '';
                    }

                    const statusBadge = `<span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full shadow-sm ${item.badge_class}">${statusIcon} ${item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : '-'}</span>`;

                    // Escape string untuk keamanan
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");

                    const row = `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200 group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 group-hover:bg-white dark:group-hover:bg-gray-600 transition-colors shadow-sm border border-gray-200 dark:border-gray-600">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">${formatDate(item.tanggal)}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                                        <span class="text-xs font-bold">${getInitials(item.nama_lengkap)}</span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">${item.nama_lengkap}</div>
                                        ${item.nip ? `<div class="text-xs text-gray-500 dark:text-gray-400">${item.nip}</div>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <i class="bi bi-people mr-2 text-gray-400"></i>
                                    ${item.golongan || '-'}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <div class="flex flex-col space-y-1.5">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 border border-green-100 dark:border-green-800">
                                        <i class="bi bi-box-arrow-in-right mr-1.5"></i> ${item.jam_masuk || '--:--'}
                                    </span>
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-100 dark:border-red-800">
                                        <i class="bi bi-box-arrow-right mr-1.5"></i> ${item.jam_keluar || '--:--'}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                ${statusBadge}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="${item.keterangan || ''}">
                                ${item.keterangan || '-'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button onclick='editAbsensi(${itemJson})' class="p-1.5 rounded-md text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20 transition-colors" title="Edit">
                                        <i class="bi bi-pencil-square text-lg"></i>
                                    </button>
                                    <button onclick="deleteAbsensi(${item.id})" class="p-1.5 rounded-md text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors" title="Hapus">
                                        <i class="bi bi-trash text-lg"></i>
                                    </button>
                                </div>
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

function loadKaryawanOptionsForAbsensi() {
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

function loadGolonganOptions() {
    fetch(`${basePath}/api/hr/absensi?action=get_golongan`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('golongan');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Golongan</option>';
                    data.data.forEach(item => {
                        select.insertAdjacentHTML('beforeend', `<option value="${item.nama_golongan}">${item.nama_golongan}</option>`);
                    });
                }
            }
        });
}

function loadDivisiOptionsForFilter() {
    fetch(`${basePath}/api/hr/divisi`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filter-divisi');
                if (select) {
                    data.data.forEach(divisi => {
                        select.insertAdjacentHTML('beforeend', `<option value="${divisi.id}">${divisi.nama_divisi}</option>`);
                    });
                }
            }
        });
}

function loadStatusOptions() {
    fetch(`${basePath}/api/hr/absensi?action=get_status`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formSelect = document.getElementById('status');
                const filterSelect = document.getElementById('filter-status-absensi');
                
                if (formSelect) formSelect.innerHTML = '';
                if (filterSelect) filterSelect.innerHTML = '<option value="">Semua Status</option>';

                data.data.forEach(item => {
                    const optionHtml = `<option value="${item.nama_status}">${item.nama_status.charAt(0).toUpperCase() + item.nama_status.slice(1)}</option>`;
                    if (formSelect) formSelect.insertAdjacentHTML('beforeend', optionHtml);
                    if (filterSelect) filterSelect.insertAdjacentHTML('beforeend', optionHtml);
                });
            }
        });
}

function openAbsensiModal(reset = true) {
    if (reset) {
        document.getElementById('absensi-form').reset();
        document.getElementById('absensi-id').value = '';
        document.getElementById('absensi-action').value = 'save';
        document.getElementById('modal-title').innerText = 'Tambah Absensi';
        // Set default tanggal hari ini
        document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
    }
    document.getElementById('absensiModal').classList.remove('hidden');
}

function closeAbsensiModal() {
    document.getElementById('absensiModal').classList.add('hidden');
}

function editAbsensi(item) {
    openAbsensiModal(false);
    document.getElementById('absensi-id').value = item.id;
    document.getElementById('absensi-action').value = 'save';
    document.getElementById('modal-title').innerText = 'Edit Absensi';
    
    document.getElementById('karyawan_id').value = item.karyawan_id;
    document.getElementById('tanggal').value = item.tanggal;
    document.getElementById('golongan').value = item.golongan || '';
    document.getElementById('jam_masuk').value = item.jam_masuk;
    document.getElementById('jam_keluar').value = item.jam_keluar;
    document.getElementById('status').value = item.status;
    document.getElementById('keterangan').value = item.keterangan;
}

function saveAbsensi() {
    const form = document.getElementById('absensi-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/absensi`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAbsensiModal();
            loadAbsensi();
            showToast('Data absensi berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteAbsensi(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data absensi ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/absensi`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAbsensi();
            showToast('Data absensi berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}

function renderTopEmployees(data) {
    const container = document.getElementById('top-employees-list');
    if (!container) return;

    if (!data || data.length === 0) {
        container.innerHTML = '<div class="flex flex-col items-center justify-center h-40 text-gray-500"><i class="bi bi-people text-3xl mb-2 opacity-50"></i><p class="text-sm">Belum ada data kehadiran.</p></div>';
        return;
    }

    let html = '';
    data.forEach((emp, index) => {
        let rankClass = 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
        let rankIcon = `<span class="font-bold text-sm">#${index + 1}</span>`;
        
        if (index === 0) {
            rankClass = 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400';
            rankIcon = '<i class="bi bi-trophy-fill"></i>';
        } else if (index === 1) {
            rankClass = 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300';
            rankIcon = '<i class="bi bi-medal-fill"></i>';
        } else if (index === 2) {
            rankClass = 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400';
            rankIcon = '<i class="bi bi-award-fill"></i>';
        }

        // Format time HH:MM
        const avgMasuk = emp.avg_masuk ? emp.avg_masuk.substring(0, 5) : '-';
        const avgPulang = emp.avg_pulang ? emp.avg_pulang.substring(0, 5) : '-';

        html += `
            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${rankClass}">
                        ${rankIcon}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" title="${emp.nama_lengkap}">${emp.nama_lengkap}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Hadir: <span class="font-medium text-gray-700 dark:text-gray-300">${emp.total_hadir} hari</span></p>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-1 flex-shrink-0 pl-2">
                    <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-800" title="Rata-rata Jam Masuk">
                        <i class="bi bi-box-arrow-in-right mr-1"></i> ${avgMasuk}
                    </div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800" title="Rata-rata Jam Pulang">
                        <i class="bi bi-box-arrow-right mr-1"></i> ${avgPulang}
                    </div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function updateAbsensiSummary(summary, totalRecords, totalKaryawan) {
    let container = document.getElementById('absensi-summary');
    
    // Jika container belum ada, buat secara dinamis sebelum tabel
    if (!container) {
        const tbody = document.getElementById('absensi-table-body');
        if (tbody) {
            const table = tbody.closest('table');
            if (table) {
                container = document.createElement('div');
                container.id = 'absensi-summary';
                container.className = 'mb-6';
                // Insert sebelum parent tabel (biasanya div responsive) atau tabel itu sendiri
                const target = table.parentElement.classList.contains('overflow-x-auto') ? table.parentElement : table;
                target.parentElement.insertBefore(container, target);
            }
        }
    }

    if (container) {
        // Helper untuk hitung persen
        const getPercent = (val) => totalRecords > 0 ? ((val / totalRecords) * 100).toFixed(1) : '0.0';

        container.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Karyawan</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${totalKaryawan || '-'}</span>
                    <span class="text-xs text-gray-400 mt-1">Aktif</span>
                </div>
                <div onclick="filterByStatus('hadir')" class="cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col items-center">
                    <span class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wider">Hadir</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${summary.hadir}</span>
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 mt-1">${getPercent(summary.hadir)}%</span>
                </div>
                <div onclick="filterByStatus('sakit')" class="cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col items-center">
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Sakit</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${summary.sakit}</span>
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400 mt-1">${getPercent(summary.sakit)}%</span>
                </div>
                <div onclick="filterByStatus('izin')" class="cursor-pointer hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col items-center">
                    <span class="text-xs font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">Izin</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${summary.izin}</span>
                    <span class="text-xs font-medium text-yellow-600 dark:text-yellow-400 mt-1">${getPercent(summary.izin)}%</span>
                </div>
                <div onclick="filterByStatus('alpa')" class="cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col items-center">
                    <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider">Alpa</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${summary.alpa}</span>
                    <span class="text-xs font-medium text-red-600 dark:text-red-400 mt-1">${getPercent(summary.alpa)}%</span>
                </div>
            </div>
        `;
    }
}

window.filterByStatus = function(status) {
    const filterStatus = document.getElementById('filter-status-absensi');
    if (filterStatus) {
        filterStatus.value = status;
        activeDateFilter = null; // Reset filter tanggal saat klik summary status
        loadAbsensi();
    }
};

let absensiChartInstance = null;

function renderAbsensiChart(chartData) {
    const ctx = document.getElementById('absensi-chart');
    if (!ctx) return;

    // Siapkan data untuk semua hari dalam bulan tersebut
    const filterBulan = document.getElementById('filter-bulan').value;
    const filterTahun = document.getElementById('filter-tahun').value;
    const daysInMonth = new Date(filterTahun, filterBulan, 0).getDate();
    
    const labels = Array.from({length: daysInMonth}, (_, i) => i + 1);
    const dataHadir = new Array(daysInMonth).fill(0);
    const dataSakit = new Array(daysInMonth).fill(0);
    const dataIzin = new Array(daysInMonth).fill(0);
    const dataAlpa = new Array(daysInMonth).fill(0);

    if (chartData) {
        chartData.forEach(item => {
            const dayIndex = item.day - 1;
            if (dayIndex >= 0 && dayIndex < daysInMonth) {
                dataHadir[dayIndex] = parseInt(item.hadir);
                dataSakit[dayIndex] = parseInt(item.sakit);
                dataIzin[dayIndex] = parseInt(item.izin);
                dataAlpa[dayIndex] = parseInt(item.alpa);
            }
        });
    }

    if (absensiChartInstance) {
        absensiChartInstance.destroy();
    }

    absensiChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Hadir', data: dataHadir, backgroundColor: '#10B981', stack: 'Stack 0' },
                { label: 'Sakit', data: dataSakit, backgroundColor: '#3B82F6', stack: 'Stack 0' },
                { label: 'Izin', data: dataIzin, backgroundColor: '#F59E0B', stack: 'Stack 0' },
                { label: 'Alpa', data: dataAlpa, backgroundColor: '#EF4444', stack: 'Stack 0' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index', intersect: false } },
            scales: { x: { stacked: true, title: { display: true, text: 'Tanggal' } }, y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Jumlah Karyawan' } } },
            onClick: (e, elements) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const datasetIndex = elements[0].datasetIndex;
                    const day = labels[index];
                    const month = document.getElementById('filter-bulan').value;
                    const year = document.getElementById('filter-tahun').value;
                    
                    const dayStr = String(day).padStart(2, '0');
                    const monthStr = String(month).padStart(2, '0');
                    
                    activeDateFilter = `${year}-${monthStr}-${dayStr}`;
                    
                    const statuses = ['hadir', 'sakit', 'izin', 'alpa'];
                    const filterStatus = document.getElementById('filter-status-absensi');
                    if (filterStatus && statuses[datasetIndex]) filterStatus.value = statuses[datasetIndex];

                    loadAbsensi();
                }
            },
            onHover: (event, chartElement) => {
                event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
            }
        }
    });
}