function initPayrollDashboardPage() {
    loadDivisiOptionsForDashboard();
    loadPayrollDashboardData();
}

function loadDivisiOptionsForDashboard() {
    fetch(`${basePath}/api/hr/divisi`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('dashboard-filter-divisi');
                if (select) {
                    data.data.forEach(divisi => {
                        select.insertAdjacentHTML('beforeend', `<option value="${divisi.id}">${divisi.nama_divisi}</option>`);
                    });
                }
            }
        });
}

function loadPayrollDashboardData() {
    const container = document.getElementById('payroll-dashboard-content');
    const tahun = document.getElementById('dashboard-filter-tahun').value;
    const divisiId = document.getElementById('dashboard-filter-divisi') ? document.getElementById('dashboard-filter-divisi').value : '';
    
    fetch(`${basePath}/api/hr/payroll-dashboard?tahun=${tahun}&divisi_id=${divisiId}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
                
                // Render Cards
                container.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mr-4">
                            <i class="bi bi-cash-stack text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Gaji Tahun Ini</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">${formatter.format(data.total_gaji_tahun_ini)}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 mr-4">
                            <i class="bi bi-calendar-check text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Gaji Bulan Terakhir</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">${formatter.format(data.gaji_bulan_terakhir)}</p>
                            <p class="text-xs text-gray-500">Periode: ${data.periode_terakhir}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 mr-4">
                            <i class="bi bi-people text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Karyawan Digaji</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">${data.jumlah_karyawan_digaji}</p>
                            <p class="text-xs text-gray-500">Bulan Terakhir</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 mr-4">
                            <i class="bi bi-graph-down-arrow text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Potongan</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">${formatter.format(data.total_potongan_bulan_terakhir)}</p>
                            <p class="text-xs text-gray-500">Bulan Terakhir</p>
                        </div>
                    </div>
                `;

                // Render Charts
                renderPayrollTrendChart(data.trend_gaji);
                renderPayrollCompositionChart(data.komposisi_gaji);
                renderDivisionComparisonChart(data.perbandingan_divisi);
            } else {
                showToast(res.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Gagal memuat data dashboard.', 'error');
        });
}

function renderPayrollTrendChart(data) {
    const ctx = document.getElementById('payroll-trend-chart');
    if (!ctx) return;

    // Destroy existing chart if any
    const existingChart = Chart.getChart(ctx);
    if (existingChart) existingChart.destroy();

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.bulan),
            datasets: [{
                label: 'Total Gaji',
                data: data.map(d => d.total),
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" }).format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}

function renderDivisionComparisonChart(data) {
    const ctx = document.getElementById('payroll-division-chart');
    if (!ctx) return;

    // Destroy existing chart if any
    const existingChart = Chart.getChart(ctx);
    if (existingChart) existingChart.destroy();

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.nama_divisi || 'Tanpa Divisi'),
            datasets: [{
                label: 'Total Gaji',
                data: data.map(d => d.total),
                averages: data.map(d => d.rata_rata),
                counts: data.map(d => d.jumlah_karyawan),
                backgroundColor: 'rgba(139, 92, 246, 0.7)', // Violet
                borderColor: 'rgba(139, 92, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Horizontal bar chart
            scales: {
                x: { 
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" }).format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.x !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.x);
                            }
                            return label;
                        },
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            const avg = context.dataset.averages[index];
                            const count = context.dataset.counts[index];
                            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
                            return `Rata-rata: ${formatter.format(avg)} (${count} Karyawan)`;
                        }
                    }
                }
            }
        }
    });
}

function renderPayrollCompositionChart(data) {
    const ctx = document.getElementById('payroll-composition-chart');
    if (!ctx) return;

    // Destroy existing chart if any
    const existingChart = Chart.getChart(ctx);
    if (existingChart) existingChart.destroy();

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Gaji Pokok', 'Tunjangan', 'Potongan'],
            datasets: [{
                data: [data.gaji_pokok, data.tunjangan, data.potongan],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)', // Blue
                    'rgba(16, 185, 129, 0.7)', // Green
                    'rgba(239, 68, 68, 0.7)'   // Red
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}
