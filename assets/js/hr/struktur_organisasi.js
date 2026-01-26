let salaryTrendChartInstance = null;
let currentZoom = 1;

function initStrukturOrganisasiPage() {
    // Pastikan Google Charts dimuat sebelum digunakan
    if (typeof loadGoogleCharts === 'function') {
        loadGoogleCharts().then(() => {
            google.charts.load('current', {packages:['orgchart']});
            google.charts.setOnLoadCallback(drawChart);
        }).catch(err => console.error('Failed to load Google Charts:', err));
    }

    loadDivisiOptions();

    const filterDivisi = document.getElementById('filter-divisi-struktur');
    if (filterDivisi) {
        filterDivisi.addEventListener('change', drawChart);
    }

    // Zoom Controls
    const container = document.getElementById('orgchart-container');
    const zoomInBtn = document.getElementById('zoom-in');
    const zoomOutBtn = document.getElementById('zoom-out');
    const zoomResetBtn = document.getElementById('zoom-reset');
    const exportPdfBtn = document.getElementById('export-pdf');

    if (zoomInBtn && zoomOutBtn && zoomResetBtn && container) {
        zoomInBtn.addEventListener('click', () => {
            currentZoom += 0.1;
            container.style.transform = `scale(${currentZoom})`;
        });
        zoomOutBtn.addEventListener('click', () => {
            if (currentZoom > 0.2) currentZoom -= 0.1;
            container.style.transform = `scale(${currentZoom})`;
        });
        zoomResetBtn.addEventListener('click', () => {
            currentZoom = 1;
            container.style.transform = `scale(1)`;
        });
    }

    if (exportPdfBtn && container) {
        exportPdfBtn.addEventListener('click', async () => {
            const originalTransform = container.style.transform;
            const originalBtnText = exportPdfBtn.innerHTML;
            
            try {
                exportPdfBtn.disabled = true;
                exportPdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';
                
                // Reset zoom sementara agar capture full size dan resolusi bagus
                container.style.transform = 'scale(1)';
                
                // Tunggu sebentar agar render ulang selesai
                await new Promise(resolve => setTimeout(resolve, 100));

                const canvas = await html2canvas(container, {
                    scale: 2, // Tingkatkan resolusi
                    backgroundColor: '#ffffff'
                });

                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                // Landscape A4
                const pdf = new jsPDF('l', 'mm', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save('struktur-organisasi.pdf');

            } catch (error) {
                console.error('Export PDF failed:', error);
                alert('Gagal mengekspor PDF.');
            } finally {
                // Kembalikan zoom dan tombol
                container.style.transform = originalTransform;
                exportPdfBtn.disabled = false;
                exportPdfBtn.innerHTML = originalBtnText;
            }
        });
    }

    // Helper function to generate node HTML
    function generateNodeHtml(item) {
        const getInitials = (name) => name ? name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : '?';
        
        // Tentukan warna dan ikon berdasarkan jabatan
        const jabatanLower = (item.nama_jabatan || '').toLowerCase();
        let borderColor = 'border-gray-300';
        let icon = 'bi-person';

        if (jabatanLower.includes('direktur') || jabatanLower.includes('komisaris')) {
            borderColor = 'border-blue-500';
            icon = 'bi-person-workspace';
        } else if (jabatanLower.includes('manager')) {
            borderColor = 'border-purple-500';
            icon = 'bi-person-video3';
        } else if (jabatanLower.includes('supervisor')) {
            borderColor = 'border-teal-500';
            icon = 'bi-person-check';
        } else if (jabatanLower.includes('staff')) {
            borderColor = 'border-gray-400';
            icon = 'bi-person';
        }

        const photoPath = item.foto_profil ? `${basePath}/${item.foto_profil}` : null;
        const photoHtml = photoPath
            ? `<img src="${photoPath}" alt="${item.nama_lengkap}" class="w-12 h-12 rounded-full object-cover">`
            : `<div class="w-12 h-12 rounded-full bg-primary/20 text-primary flex items-center justify-center text-lg font-bold">${getInitials(item.nama_lengkap)}</div>`;

        return `<div draggable="true" ondragstart="drag(event)" ondrop="drop(event)" ondragover="allowDrop(event)" data-id="${item.id}">
            <div class="flex items-center gap-3 p-2">
                <div class="flex-shrink-0">
                    ${photoHtml}
                </div>
                <div class="text-left min-w-0">
                    <div class="font-bold text-sm text-gray-800 truncate" title="${item.nama_lengkap}">${item.nama_lengkap}</div>
                    <div class="text-xs text-gray-500 italic flex items-center gap-1">
                        <i class="bi ${icon}"></i>
                        <span class="truncate" title="${item.nama_jabatan || 'Tanpa Jabatan'}">${item.nama_jabatan || 'Tanpa Jabatan'}</span>
                        ${item.nama_divisi ? `<span class="text-gray-400 mx-1">•</span><span class="truncate" title="${item.nama_divisi}">${item.nama_divisi}</span>` : ''}
                    </div>
                </div>
            </div>
        </div>`;
    }

    async function drawChart() {
        const container = document.getElementById('orgchart-container');
        const divisiId = document.getElementById('filter-divisi-struktur') ? document.getElementById('filter-divisi-struktur').value : '';

        try {
            const response = await fetch(`${basePath}/api/hr/karyawan?action=get_org_chart_data&divisi_id=${divisiId}`);
            const result = await response.json();

            if (!result.success || result.data.length === 0) {
                container.innerHTML = '<div class="text-center p-10 text-gray-500">Tidak ada data untuk ditampilkan.</div>';
                return;
            }

            const data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            // Filter data untuk memastikan hanya karyawan aktif yang ditampilkan
            // dan menangani potensi masalah data (misal: atasan_id menunjuk ke karyawan non-aktif/terhapus)
            const validIds = new Set(result.data.map(item => item.id.toString()));

            const rows = result.data.map(item => {
                const v = {
                    v: item.id.toString(),
                    f: generateNodeHtml(item) // Gunakan helper function
                };
                const manager = (item.atasan_id && validIds.has(item.atasan_id.toString())) ? item.atasan_id.toString() : '';
                const tooltip = item.nama_divisi || '';
                return [v, manager, tooltip];
            });

            data.addRows(rows);

            const chart = new google.visualization.OrgChart(container);
            chart.draw(data, {
                allowHtml: true,
                nodeClass: 'org-node-custom',
                selectedNodeClass: 'org-node-selected-custom'
            });

            // Add event listener for node clicks
            google.visualization.events.addListener(chart, 'select', () => {
                const selection = chart.getSelection();
                if (selection.length > 0) {
                    const row = selection[0].row;
                    const employeeId = data.getValue(row, 0);
                    
                    showEmployeeDetail(employeeId);
                }
            });

        } catch (error) {
            console.error('Error drawing chart:', error);
            container.innerHTML = '<div class="text-center p-10 text-red-500">Gagal memuat struktur organisasi.</div>';
        }
    }

    // Expose drag and drop functions to global scope
    window.allowDrop = function(ev) {
        ev.preventDefault();
    };

    window.drag = function(ev) {
        // Set data ID karyawan yang sedang di-drag
        const target = ev.target.closest('[draggable="true"]');
        if (target) {
            ev.dataTransfer.setData("text", target.getAttribute('data-id'));
        }
    };

    window.drop = function(ev) {
        ev.preventDefault();
        const draggedId = ev.dataTransfer.getData("text");
        const target = ev.target.closest('[draggable="true"]');
        
        if (target) {
            const newManagerId = target.getAttribute('data-id');

            // Cegah drop ke diri sendiri
            if (draggedId === newManagerId) return;

            if (confirm('Apakah Anda yakin ingin mengubah atasan karyawan ini?')) {
                updateAtasan(draggedId, newManagerId);
            }
        }
    };

    async function updateAtasan(karyawanId, atasanId) {
        // Tampilkan loading indicator
        const container = document.getElementById('orgchart-container');
        container.innerHTML = '<div class="text-center p-10 text-gray-500"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>Memperbarui struktur...</div>';

        const formData = new FormData();
        formData.append('action', 'update_atasan');
        formData.append('karyawan_id', karyawanId);
        formData.append('atasan_id', atasanId);

        try {
            const response = await fetch(`${basePath}/api/hr/karyawan`, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                drawChart(); // Refresh chart
            } else {
                alert('Gagal mengubah atasan: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating manager:', error);
            alert('Terjadi kesalahan sistem.');
            drawChart(); // Refresh chart to restore state even on error
        }
    }

    async function showEmployeeDetail(id) {
        const modal = document.getElementById('employeeDetailModal');
        const content = document.getElementById('employee-detail-content');
        
        modal.classList.remove('hidden');
        content.innerHTML = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div></div>';

        try {
            // Kita bisa menggunakan endpoint get_single yang mungkin sudah ada atau buat baru.
            // Di sini saya asumsikan kita perlu endpoint khusus atau filter by ID di endpoint list
            // Untuk efisiensi, mari kita gunakan endpoint list dengan filter ID jika backend mendukung,
            // atau tambahkan action 'get_detail' di handler.
            // Mari kita coba gunakan endpoint list dengan filter ID yang sudah ada di karyawan_handler.php (GET default)
            // Namun, endpoint default mengembalikan array. Kita perlu filter di client atau backend.
            // Lebih baik tambahkan action 'get_detail' di handler untuk performa.
            
            const response = await fetch(`${basePath}/api/hr/karyawan?action=get_detail&id=${id}`);
            const result = await response.json();

            if (result.success) {
                // Pastikan result.data ada dan bukan array kosong
                const emp = Array.isArray(result.data) ? result.data[0] : result.data;
                
                if (!emp) {
                    throw new Error('Data karyawan kosong.');
                }

                const currencyFormatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

                const nama = emp.nama_lengkap || 'Karyawan';
                const photoUrl = emp.foto_profil ? `${basePath}/${emp.foto_profil}` : null;
                const photoHtml = photoUrl 
                    ? `<img src="${photoUrl}" class="w-24 h-24 rounded-full object-cover mx-auto mb-4 border-4 border-white shadow-md">`
                    : `<div class="w-24 h-24 rounded-full bg-primary/10 text-primary flex items-center justify-center text-3xl font-bold mx-auto mb-4 border-4 border-white shadow-md">${nama.charAt(0)}</div>`;

                // Pastikan riwayat adalah array (fallback ke empty array jika null/undefined)
                const riwayatJabatan = Array.isArray(emp.riwayat_jabatan) ? emp.riwayat_jabatan : [];
                const riwayatGaji = Array.isArray(emp.riwayat_gaji) ? emp.riwayat_gaji : [];

                content.innerHTML = `
                    <div class="text-center">
                        ${photoHtml}
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">${nama}</h4>
                        <p class="text-sm text-primary font-medium">${emp.nama_jabatan || '-'}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">${emp.nip}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-left border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div><span class="text-xs text-gray-500 block">Divisi</span><span class="font-medium">${emp.nama_divisi || '-'}</span></div>
                        <div><span class="text-xs text-gray-500 block">Kantor</span><span class="font-medium">${emp.nama_kantor || '-'}</span></div>
                        <div><span class="text-xs text-gray-500 block">Status</span><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${emp.status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${emp.status}</span></div>
                        <div><span class="text-xs text-gray-500 block">Tanggal Masuk</span><span class="font-medium">${emp.tanggal_masuk ? new Date(emp.tanggal_masuk).toLocaleDateString('id-ID') : '-'}</span></div>
                    </div>
                    <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h5 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-2">Riwayat Jabatan</h5>
                        ${
                            (riwayatJabatan && riwayatJabatan.length > 0)
                            ? `<ul class="space-y-2 text-xs">` +
                                riwayatJabatan.map(hist => `
                                    <li class="flex justify-between items-center">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">${hist.nama_jabatan}</span>
                                        <span class="text-gray-500">${hist.tanggal_mulai ? new Date(hist.tanggal_mulai).toLocaleDateString('id-ID') : '-'} - ${hist.tanggal_selesai ? new Date(hist.tanggal_selesai).toLocaleDateString('id-ID') : 'Sekarang'}</span>
                                    </li>
                                `).join('') + `</ul>`
                            : '<p class="text-xs text-gray-400">Tidak ada riwayat jabatan.</p>'
                        }
                    </div>
                    
                    ${(riwayatGaji.length > 1) ? `
                    <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h5 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-2">Tren Gaji</h5>
                        <div class="h-40 w-full"><canvas id="salary-trend-chart"></canvas></div>
                    </div>` : ''}

                    <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h5 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-2">Riwayat Gaji</h5>
                        ${
                            (riwayatGaji && riwayatGaji.length > 0)
                            ? `<div class="max-h-40 overflow-y-auto custom-scrollbar"><ul class="space-y-2 text-xs">` +
                                riwayatGaji.map(hist => {
                                    const diff = parseFloat(hist.gaji_baru) - parseFloat(hist.gaji_lama);
                                    const diffClass = diff >= 0 ? 'text-green-600' : 'text-red-600';
                                    const diffIcon = diff >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                    return `
                                    <li class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-1 last:border-0">
                                        <span class="text-gray-500 w-20">${hist.tanggal_perubahan ? new Date(hist.tanggal_perubahan).toLocaleDateString('id-ID') : '-'}</span>
                                        <span class="font-medium text-gray-700 dark:text-gray-300 flex-1 text-right mr-2">${currencyFormatter.format(hist.gaji_baru)}</span>
                                        <span class="${diffClass} w-24 text-right"><i class="bi ${diffIcon}"></i> ${currencyFormatter.format(Math.abs(diff))}</span>
                                    </li>
                                `}).join('') + `</ul></div>`
                            : '<p class="text-xs text-gray-400">Tidak ada riwayat gaji.</p>'
                        }
                    </div>
                `;

                // Render Chart jika ada data riwayat gaji lebih dari 1 (untuk melihat tren)
                if (riwayatGaji && riwayatGaji.length > 1) {
                    // Data riwayat gaji biasanya urut DESC (terbaru dulu), kita balik untuk chart (ASC)
                    const sortedHistory = [...riwayatGaji].reverse();
                    
                    const labels = sortedHistory.map(h => new Date(h.tanggal_perubahan).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }));
                    const dataGaji = sortedHistory.map(h => parseFloat(h.gaji_baru));

                    const ctx = document.getElementById('salary-trend-chart').getContext('2d');
                    
                    if (salaryTrendChartInstance) {
                        salaryTrendChartInstance.destroy();
                    }

                    salaryTrendChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Gaji Pokok',
                                data: dataGaji,
                                borderColor: '#10B981', // Emerald-500
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.3,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: false, ticks: { callback: (val) => currencyFormatter.format(val) } } }
                        }
                    });
                }
            } else {
                content.innerHTML = `<p class="text-red-500 text-center">Gagal memuat detail karyawan: ${result.message || 'Data tidak ditemukan'}</p>`;
            }
        } catch (error) {
            console.error(error);
            content.innerHTML = `<p class="text-red-500 text-center">Terjadi kesalahan sistem.</p>`;
        }
    }
}