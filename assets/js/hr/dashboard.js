let kpiChartInstance = null;
let kpiComparisonChartInstance = null;

async function initHrDashboardPage() {
    await loadBottomPerformer();
    await loadTopPerformer();
    await loadExpiringContractsWidget();
    await loadProbationEndingWidget();
    await loadKpiKaryawanOptions();
    await loadDivisiOptions();
    await loadKpiComparisonChart();
    initQuickEmployeeSearch();
    initCalendarWidget();

    const select = document.getElementById('kpi_karyawan_select');    
    if (select) {
        select.addEventListener('change', (e) => {
            const karyawanId = e.target.value;
            if (karyawanId) {
                loadKpiTrendChart(karyawanId);
            }
        });
    }

    const bulanFilter = document.getElementById('compare_filter_bulan');
    const tahunFilter = document.getElementById('compare_filter_tahun');
    const divisiFilter = document.getElementById('compare_filter_divisi');
    if (bulanFilter && tahunFilter) {
        bulanFilter.addEventListener('change', loadKpiComparisonChart);
        tahunFilter.addEventListener('change', loadKpiComparisonChart);
    }
    if (divisiFilter) {
        divisiFilter.addEventListener('change', loadKpiComparisonChart);
    }
}

async function loadExpiringContractsWidget() {
    const container = document.getElementById('expiring-contracts-list');
    const countEl = document.getElementById('expiring-count'); // New element
    if (!container) return;

    try {
        const response = await fetch(`${basePath}/api/hr/dokumen?action=expiring_contracts`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            // Update count
            if (countEl) countEl.textContent = result.data.length;
            
            container.innerHTML = result.data.map(item => {
                const sisa = parseInt(item.sisa_hari);
                let statusClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                let statusText = `${sisa} hari`;
                let icon = 'bi-clock';
                
                if (sisa < 0) {
                    statusClass = 'bg-red-50 text-red-700 border-red-200';
                    statusText = `Expired`;
                    icon = 'bi-x-circle';
                } else if (sisa <= 7) {
                    statusClass = 'bg-orange-50 text-orange-700 border-orange-200';
                    icon = 'bi-exclamation-triangle';
                }

                return `
                    <div class="flex items-center p-3 rounded-lg border ${statusClass} dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <div class="flex-shrink-0 mr-3 text-lg">
                            <i class="bi ${icon} text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">${item.nama_lengkap}</p>
                            <p class="text-xs opacity-80 truncate">${item.nama_jabatan || 'Karyawan'}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold block">${statusText}</span>
                            <span class="text-[10px] opacity-75">${formatDate(item.tanggal_berakhir_kontrak)}</span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            if (countEl) countEl.textContent = 0;
            container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-gray-400"><i class="bi bi-check-circle-fill text-3xl mb-2 text-green-500"></i><p class="text-sm">Semua kontrak aman.</p></div>';
        }
    } catch (error) {
        console.error('Error loading expiring contracts widget:', error);
        container.innerHTML = '<div class="text-center text-sm text-red-500 py-4">Gagal memuat data.</div>';
    }
}

async function loadProbationEndingWidget() {
    const container = document.getElementById('probation-ending-list');
    if (!container) return;

    try {
        const response = await fetch(`${basePath}/api/hr/dokumen?action=probation_ending`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(item => {
                const sisa = parseInt(item.sisa_hari);
                let statusClass = 'bg-blue-50 text-blue-700 border-blue-200';
                let statusText = `${sisa} hari`;

                return `
                    <div class="flex items-center p-3 rounded-lg border ${statusClass} dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <div class="flex-shrink-0 mr-3 text-lg">
                            <i class="bi bi-person-check text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">${item.nama_lengkap}</p>
                            <p class="text-xs opacity-80 truncate">${item.nama_jabatan || 'Karyawan'}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold block">${statusText}</span>
                            <span class="text-[10px] opacity-75">Sisa Waktu</span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-gray-400"><i class="bi bi-check2-all text-3xl mb-2 text-blue-500"></i><p class="text-sm">Tidak ada probation berakhir.</p></div>';
        }
    } catch (error) {
        console.error('Error loading probation widget:', error);
        container.innerHTML = '<div class="text-center text-sm text-red-500 py-4">Gagal memuat data.</div>';
    }
}

function initQuickEmployeeSearch() {
    const searchInput = document.getElementById('quick-employee-search');
    const resultsContainer = document.getElementById('quick-search-results');

    if (!searchInput || !resultsContainer) return;

    let debounceTimer;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const term = e.target.value.trim();

        if (term.length < 2) {
            resultsContainer.classList.add('hidden');
            resultsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(async () => {
            try {
                const response = await fetch(`${basePath}/api/hr/karyawan?search=${encodeURIComponent(term)}&status=aktif`);
                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    resultsContainer.innerHTML = result.data.map(emp => `
                        <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 flex items-center gap-3 transition-colors" onclick="selectEmployeeFromSearch(${emp.id})">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">
                                    ${emp.nama_lengkap.charAt(0)}
                                </div>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800 dark:text-white text-sm">${emp.nama_lengkap}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">${emp.nip} ${emp.nama_jabatan ? '• ' + emp.nama_jabatan : ''}</div>
                            </div>
                        </div>
                    `).join('');
                    resultsContainer.classList.remove('hidden');
                } else {
                    resultsContainer.innerHTML = '<div class="p-4 text-sm text-gray-500 text-center">Tidak ditemukan karyawan dengan nama/NIP tersebut.</div>';
                    resultsContainer.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        }, 300);
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });
}

window.selectEmployeeFromSearch = function(id) {
    const kpiSelect = document.getElementById('kpi_karyawan_select');
    if (kpiSelect) {
        // Cek apakah opsi ada di dropdown (dropdown memuat karyawan aktif)
        let optionExists = false;
        for (let i = 0; i < kpiSelect.options.length; i++) {
            if (kpiSelect.options[i].value == id) {
                optionExists = true;
                break;
            }
        }
        
        if (optionExists) {
            kpiSelect.value = id;
            kpiSelect.dispatchEvent(new Event('change'));
            
            // Scroll ke grafik tren
            const chartContainer = document.getElementById('kpi-chart-container');
            if(chartContainer) {
                chartContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Efek highlight
                chartContainer.parentElement.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
                setTimeout(() => chartContainer.parentElement.classList.remove('ring-2', 'ring-primary', 'ring-offset-2'), 2000);
            }
        } else {
            showToast('Data karyawan tidak tersedia di filter grafik.', 'info');
        }
    }
    
    // Bersihkan pencarian
    const searchInput = document.getElementById('quick-employee-search');
    const resultsContainer = document.getElementById('quick-search-results');
    if(searchInput) searchInput.value = '';
    if(resultsContainer) resultsContainer.classList.add('hidden');
};
async function loadKpiKaryawanOptions() {
    const select = document.getElementById('kpi_karyawan_select');
    if (!select) return;

    try {
        const response = await fetch(`${basePath}/api/hr/karyawan?status=aktif`);
        const result = await response.json();
        if (result.success) {
            result.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.nama_lengkap} - ${item.nip}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading karyawan options:', error);
    }
}

async function loadDivisiOptions() {
    const select = document.getElementById('compare_filter_divisi');
    if (!select) return;

    try {
        const response = await fetch(`${basePath}/api/hr/divisi`);
        const result = await response.json();
        if (result.success) {
            result.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama_divisi;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading divisi options:', error);
    }
}

async function loadKpiTrendChart(karyawanId) {
    const chartContainer = document.getElementById('kpi-chart-container');
    const placeholder = document.getElementById('kpi-chart-placeholder');
    if (!chartContainer || !placeholder) return;

    placeholder.classList.add('hidden');
    chartContainer.classList.remove('hidden');

    try {
        const response = await fetch(`${basePath}/api/hr/kpi?action=get_kpi_trend&karyawan_id=${karyawanId}`);
        const result = await response.json();

        if (result.success) {
            const labels = [];
            const scores = [];
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

            const trendData = new Map();
            for (let i = 11; i >= 0; i--) {
                const d = new Date();
                d.setMonth(d.getMonth() - i);
                const key = `${d.getFullYear()}-${d.getMonth() + 1}`;
                trendData.set(key, null);
            }

            result.data.forEach(item => {
                const key = `${item.periode_tahun}-${item.periode_bulan}`;
                trendData.set(key, item.total_skor);
            });
            
            trendData.forEach((score, key) => {
                const [year, month] = key.split('-');
                labels.push(`${monthNames[parseInt(month) - 1]} '${year.slice(-2)}`);
                scores.push(score);
            });

            const ctx = document.getElementById('kpi-chart').getContext('2d');
            
            if (kpiChartInstance) {
                kpiChartInstance.destroy();
            }

            kpiChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Skor KPI',
                        data: scores,
                        borderColor: 'var(--theme-color, #4F46E5)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.3,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { color: document.body.classList.contains('dark') ? '#9CA3AF' : '#6B7281' },
                            grid: { color: document.body.classList.contains('dark') ? '#374151' : '#E5E7EB' }
                        },
                        x: {
                            ticks: { color: document.body.classList.contains('dark') ? '#9CA3AF' : '#6B7281' },
                             grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => context.parsed.y !== null ? `${context.dataset.label || ''}: ${context.parsed.y.toFixed(2)}` : null
                            }
                        }
                    }
                }
            });
        } else {
            placeholder.innerHTML = `<p class="text-red-500">Gagal memuat data tren.</p>`;
            placeholder.classList.remove('hidden');
            chartContainer.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error loading KPI trend chart:', error);
        placeholder.innerHTML = `<p class="text-red-500">Terjadi kesalahan saat memuat data.</p>`;
        placeholder.classList.remove('hidden');
        chartContainer.classList.add('hidden');
    }
}

async function loadKpiComparisonChart() {
    const bulan = document.getElementById('compare_filter_bulan').value;
    const tahun = document.getElementById('compare_filter_tahun').value;
    const divisi = document.getElementById('compare_filter_divisi') ? document.getElementById('compare_filter_divisi').value : '';
    const container = document.getElementById('kpi-comparison-chart-container');
    const canvas = document.getElementById('kpi-comparison-chart');

    try {
        const response = await fetch(`${basePath}/api/hr/kpi?action=get_kpi_comparison&bulan=${bulan}&tahun=${tahun}&divisi_id=${divisi}`);
        const result = await response.json();

        if (result.success) {
            const labels = result.data.map(item => item.nama_lengkap);
            const scores = result.data.map(item => item.total_skor);

            const ctx = canvas.getContext('2d');

            if (kpiComparisonChartInstance) {
                kpiComparisonChartInstance.destroy();
            }

            // Adjust container height based on number of employees
            const newHeight = Math.max(200, labels.length * 35); // 35px per bar, min 200px
            container.style.height = `${newHeight}px`;

            kpiComparisonChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Skor KPI',
                        data: scores,
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'var(--theme-color, #4F46E5)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Horizontal bar chart
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { color: document.body.classList.contains('dark') ? '#9CA3AF' : '#6B7281' },
                            grid: { color: document.body.classList.contains('dark') ? '#374151' : '#E5E7EB' }
                        },
                        y: {
                            ticks: { color: document.body.classList.contains('dark') ? '#9CA3AF' : '#6B7281' },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.dataset.label || ''}: ${parseFloat(context.raw).toFixed(2)}`
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading KPI comparison chart:', error);
    }
}


async function loadBottomPerformer() {
    const placeholder = document.getElementById('bottom-performer-placeholder');
    const content = document.getElementById('bottom-performer-content');
    const photoEl = document.getElementById('bottom-performer-photo'); // Image element

    if (!placeholder || !content) return;

    try {
        const response = await fetch(`${basePath}/api/hr/kpi?action=get_bottom_performer`);
        const result = await response.json();

        if (result.success && result.data) {
            const performer = result.data;
            document.getElementById('bottom-performer-name').textContent = performer.nama_lengkap;
            document.getElementById('bottom-performer-jabatan').textContent = performer.nama_jabatan || 'N/A';
            document.getElementById('bottom-performer-score').textContent = parseFloat(performer.total_skor).toFixed(2);
            document.getElementById('bottom-performer-period').textContent = performer.periode;
            
            placeholder.classList.add('hidden');
            content.classList.remove('hidden');
        } else {
            placeholder.innerHTML = 'Belum ada data.';
            if (photoEl) photoEl.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error loading bottom performer:', error);
        placeholder.innerHTML = 'Gagal memuat.';
    }
}


async function loadTopPerformer() {
    const placeholder = document.getElementById('top-performer-placeholder');
    const content = document.getElementById('top-performer-content');
    const photoEl = document.getElementById('top-performer-photo'); // Image element
    
    if (!placeholder || !content) return;

    try {
        const response = await fetch(`${basePath}/api/hr/kpi?action=get_top_performer`);
        const result = await response.json();

        if (result.success && result.data) {
            const performer = result.data;
            document.getElementById('top-performer-name').textContent = performer.nama_lengkap;
            document.getElementById('top-performer-jabatan').textContent = performer.nama_jabatan || 'N/A';
            document.getElementById('top-performer-score').textContent = parseFloat(performer.total_skor).toFixed(2);
            document.getElementById('top-performer-period').textContent = performer.periode;
            
            placeholder.classList.add('hidden');
            content.classList.remove('hidden');
        } else {
            placeholder.innerHTML = 'Belum ada data.';
            if (photoEl) photoEl.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error loading top performer:', error);
        placeholder.innerHTML = 'Gagal memuat.';
    }
}

function initCalendarWidget() {
    const container = document.getElementById('calendar-widget-container');
    const titleEl = document.getElementById('calendar-widget-title');
    const prevBtn = document.getElementById('calendar-prev-month');
    const nextBtn = document.getElementById('calendar-next-month');

    if (!container || !titleEl || !prevBtn || !nextBtn) return;

    let currentDate = new Date();

    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const dayNames = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];

    async function renderCalendar(year, month) {
        container.innerHTML = `<div class="text-center py-10 text-gray-400"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary mx-auto"></div></div>`;
        titleEl.textContent = `${monthNames[month]} ${year}`;

        // Fetch events
        const response = await fetch(`${basePath}/api/hr/kpi?action=get_calendar_widget_data&bulan=${month + 1}&tahun=${year}`);
        const result = await response.json();
        const events = result.success ? result.data : {};

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        let calendarHtml = '<div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">';
        dayNames.forEach(day => {
            calendarHtml += `<div>${day}</div>`;
        });
        calendarHtml += '</div><div class="grid grid-cols-7 gap-1">';

        // Blank days
        for (let i = 0; i < firstDay; i++) {
            calendarHtml += '<div></div>';
        }

        // Date cells
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = new Date(year, month, day).toDateString() === new Date().toDateString();
            
            let cellClass = 'h-8 flex items-center justify-center rounded-lg text-sm transition-colors';
            let tooltipText = '';
            let eventIndicator = '';

            if (events[dateStr]) {
                const holiday = events[dateStr].find(e => e.type === 'holiday');
                const leaves = events[dateStr].filter(e => e.type === 'leave');

                if (holiday) {
                    cellClass += ' bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 font-bold';
                    tooltipText = holiday.text;
                } else if (leaves.length > 0) {
                    cellClass += ' bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300';
                    tooltipText = leaves.map(l => l.text).join('\n');
                    eventIndicator = `<div class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-blue-500 rounded-full"></div>`;
                }
            }
            
            if (isToday && !events[dateStr]) {
                cellClass += ' bg-primary text-white font-bold';
            } else if (!events[dateStr]) {
                cellClass += ' text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700';
            }

            calendarHtml += `<div class="relative" ${tooltipText ? `title="${tooltipText}"` : ''}><div class="${cellClass}">${day}</div>${eventIndicator}</div>`;
        }

        calendarHtml += '</div>';
        container.innerHTML = calendarHtml;
    }

    prevBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
    });

    nextBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
    });

    renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
}