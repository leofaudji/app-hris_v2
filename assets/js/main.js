// =================================================================================
// APLIKASI KEUANGAN - SINGLE PAGE APPLICATION (SPA) CORE
// =================================================================================
/**
 * Displays a toast notification.
 * @param {string} message The message to display.
 * @param {string} type The type of toast: 'success', 'error', or 'info'.
 * @param {string|null} title Optional title for the toast.
 */
function showToast(message, type = 'success', title = null) {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;

    const toastId = 'toast-' + Date.now();
    let toastIcon, defaultTitle, colors;

    switch (type) {
        case 'error':
            colors = {
                bg: 'bg-red-50 dark:bg-red-800/20',
                text: 'text-red-800 dark:text-red-200',
                icon: 'text-red-500',
                border: 'border-red-200 dark:border-red-700'
            };
            toastIcon = '<i class="bi bi-x-circle-fill"></i>';
            defaultTitle = 'Error';
            break;
        case 'info':
            colors = {
                bg: 'bg-blue-50 dark:bg-blue-800/20',
                text: 'text-blue-800 dark:text-blue-200',
                icon: 'text-blue-500',
                border: 'border-blue-200 dark:border-blue-700'
            };
            toastIcon = '<i class="bi bi-bell-fill"></i>';
            defaultTitle = 'Notifikasi';
            break;
        case 'success':
        default:
            colors = {
                bg: 'bg-green-50 dark:bg-green-800/20',
                text: 'text-green-800 dark:text-green-200',
                icon: 'text-green-500',
                border: 'border-green-200 dark:border-green-700'
            };
            toastIcon = '<i class="bi bi-check-circle-fill"></i>';
            defaultTitle = 'Sukses';
            break;
    }

    const toastTitle = title || defaultTitle;

    const toastHTML = `
        <div id="${toastId}" class="max-w-lg w-full ${colors.bg} ${colors.border} shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transition-transform transform translate-x-full">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 text-xl ${colors.icon}">
                        ${toastIcon}
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${toastTitle}</p>
                        <p class="mt-1 text-sm ${colors.text}">${message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button onclick="document.getElementById('${toastId}').remove()" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = document.getElementById(toastId);
    
    // Animate in
    setTimeout(() => {
        toastElement.classList.remove('translate-x-full');
        toastElement.classList.add('translate-x-0');
    }, 100);

    // Auto-hide
    setTimeout(() => {
        if (toastElement) toastElement.remove();
    }, 8000);
}

/**
 * Formats a number into accounting-style currency string.
 * Negative numbers are shown in red and parentheses.
 * @param {number} value The number to format.
 * @returns {string} The formatted HTML string.
 */
function formatCurrencyAccounting(value) {
    const formatter = new Intl.NumberFormat('id-ID', { 
        style: 'decimal', // Use decimal to avoid currency symbol inside parentheses
        minimumFractionDigits: 0 
    });

    if (value < 0) {
        return `<span class="text-danger">(Rp ${formatter.format(Math.abs(value))})</span>`;
    } else if (value > 0) {
        return `Rp ${formatter.format(value)}`;
    } else {
        return `Rp 0`;
    }
}

/**
 * Updates the active link in the sidebar based on the current URL.
 * @param {string} path The path of the page being navigated to.
 */
function updateActiveSidebarLink(path) {
    const sidebarLinks = document.querySelectorAll('#sidebar a');
    const cleanCurrentPath = path.length > 1 ? path.replace(/\/$/, "") : path;

    sidebarLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        const cleanLinkPath = linkPath.length > 1 ? linkPath.replace(/\/$/, "") : linkPath;

        // Reset all links first
        link.classList.remove('active');
        const parentCollapseTrigger = link.closest('[data-controller="collapse"]')?.querySelector('button');
        
        if (parentCollapseTrigger) {
            parentCollapseTrigger.classList.remove('active-parent');
        }

        if (cleanLinkPath === cleanCurrentPath) {
            // Style the active link
            link.classList.add('active');

            // Check if it's inside a collapsible menu
            const parentCollapseContent = link.closest('.collapse-content');
            if (parentCollapseContent) {
                // Show the content
                parentCollapseContent.style.maxHeight = parentCollapseContent.scrollHeight + 'px';

                // Style the trigger button
                const triggerButton = parentCollapseContent.previousElementSibling;
                if (triggerButton) {
                    triggerButton.classList.add('active-parent');
                    const icon = triggerButton.querySelector('.bi-chevron-down');
                    if (icon) icon.classList.add('rotate-180');
                }
            }
        }
    });
}

/**
 * Main navigation function for the SPA.
 * Fetches page content and injects it into the main content area.
 * @param {string} url The URL to navigate to.
 * @param {boolean} pushState Whether to push a new state to the browser history.
 */
async function navigate(url, pushState = true) {
    const mainContent = document.getElementById('main-content');
    const loadingBar = document.getElementById('spa-loading-bar');
    if (!mainContent) return;

    // --- Start Loading (Not implemented in Tailwind version, can be added) ---
    if (loadingBar) {
        loadingBar.classList.remove('is-finished'); // Reset state
        loadingBar.classList.add('is-loading');
    }

    // 1. Mulai animasi fade-out
    mainContent.classList.add('is-transitioning');
    mainContent.style.opacity = '0';
    // 2. Tunggu animasi fade-out selesai (durasi harus cocok dengan CSS)
    await new Promise(resolve => setTimeout(resolve, 200));

    try {
        const response = await fetch(url, {
            headers: {
                'X-SPA-Request': 'true'
            }
        });

        // --- Finish Loading ---
        if (loadingBar) {
            loadingBar.classList.add('is-finished');
        }

        if (!response.ok) {
            throw new Error(`Server responded with status ${response.status}`);
        }

        const html = await response.text();

        if (pushState) {
            history.pushState({ path: url }, '', url);
        }

        // 3. Ganti konten saat tidak terlihat
        mainContent.innerHTML = html;
        const pageTitle = document.querySelector('#main-content .h2, #main-content h1')?.textContent || 'Dashboard';
        document.getElementById('page-title').textContent = pageTitle;
        updateActiveSidebarLink(new URL(url).pathname);
        
        // 4. Mulai animasi fade-in
        mainContent.style.opacity = '1';

        runPageScripts(new URL(url).pathname); // Run scripts for the new page

        // Handle hash for scrolling to a specific item
        const hash = new URL(url).hash;
        if (hash) { 
            // Use a small timeout to ensure the element is rendered by the page script
            setTimeout(() => {
                const element = document.querySelector(hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Add a temporary highlight effect
                    element.classList.add('highlight-item');
                    setTimeout(() => element.classList.remove('highlight-item'), 3000);
                }
            }, 300); // 300ms delay should be enough
        } 
    } catch (error) {
        console.error('Navigation error:', error);
        let errorMessage = 'Gagal memuat halaman. Silakan coba lagi.';
        if (error.message.includes('403')) {
            errorMessage = 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.';
        } else if (error.message.includes('404')) {
            errorMessage = 'Halaman tidak ditemukan. Halaman yang Anda cari tidak ada atau telah dipindahkan.';
        }
        mainContent.innerHTML = `<div class="alert alert-danger m-3">${errorMessage}</div>`;
        // Tampilkan juga pesan error dengan fade-in
        mainContent.style.opacity = '1';
    } finally {
        // Hide the loading bar after a short delay to let the 'finished' animation complete
        if (loadingBar) {
            setTimeout(() => {
                loadingBar.classList.remove('is-loading');
                loadingBar.classList.remove('is-finished');
            }, 500); // 500ms delay
        }
    }
}

/**
 * A client-side router to run page-specific JavaScript after content is loaded.
 * @param {string} path The current page's path.
 */
function runPageScripts(path) {
    const cleanPath = path.replace(basePath, '').split('?')[0].replace(/\/$/, "") || '/';

    const routeMap = {
        '/': { script: 'dashboard.js', init: 'initDashboardPage' },
        '/dashboard': { script: 'dashboard.js', init: 'initDashboardPage' },
        '/transaksi': { script: 'transaksi.js', init: 'initTransaksiPage' },
        '/entri-jurnal': { script: 'entri_jurnal.js', init: 'initEntriJurnalPage' },
        '/coa': { script: 'coa.js', init: 'initCoaPage' },
        '/saldo-awal': { script: 'saldoawal.js', init: 'initSaldoAwalPage' },
        '/laporan': { script: 'laporan.js', init: 'initLaporanPage' },
        '/laporan-harian': { script: 'laporan_harian.js', init: 'initLaporanHarianPage' },
        '/laporan-stok': { script: 'laporan_stok.js', init: 'initLaporanStokPage' },
        '/buku-besar': { script: 'buku_besar.js', init: 'initBukuBesarPage' },
        '/settings': { script: 'settings.js', init: 'initSettingsPage' },
        '/my-profile/change-password': { script: 'myprofile.js', init: 'initMyProfilePage' },
        '/daftar-jurnal': { script: 'daftar_jurnal.js', init: 'initDaftarJurnalPage' },
        '/konsinyasi': { script: 'konsinyasi.js', init: 'initKonsinyasiPage' },
        '/transaksi-berulang': { script: 'transaksi_berulang.js', init: 'initTransaksiBerulangPage' },
        '/laporan-laba-ditahan': { script: 'laporan_laba_ditahan.js', init: 'initLaporanLabaDitahanPage' },
        '/tutup-buku': { script: 'tutupbuku.js', init: 'initTutupBukuPage' },
        '/analisis-rasio': { script: 'analisis_rasio.js', init: 'initAnalisisRasioPage' },
        '/activity-log': { script: 'activity_log.js', init: 'initActivityLogPage' },
        '/anggaran': { script: 'anggaran.js', init: 'initAnggaranPage' },
        '/users': { script: 'users.js', init: 'initUsersPage' },
        '/laporan-pertumbuhan-laba': { script: 'laporan_pertumbuhan_laba.js', init: 'initLaporanPertumbuhanLabaPage' },
        '/histori-rekonsiliasi': { script: 'histori_rekonsiliasi.js', init: 'initHistoriRekonsiliasiPage' },
        '/rekonsiliasi-bank': { script: 'rekonsiliasi_bank.js', init: 'initRekonsiliasiBankPage' },
        '/aset-tetap': { script: 'aset_tetap.js', init: 'initAsetTetapPage' },
        '/pembelian': { script: 'pembelian.js', init: 'initPembelianPage' },
        '/stok': { script: 'stok.js', init: 'initStokPage' },
        '/stok-opname': { script: 'stok_opname.js', init: 'initStokOpnamePage' },
        '/laporan-kartu-stok': { script: 'laporan_kartu_stok.js', init: 'initLaporanKartuStokPage' },
        '/laporan-persediaan': { script: 'laporan_persediaan.js', init: 'initLaporanPersediaanPage' },
        '/laporan-pertumbuhan-persediaan': { script: 'laporan_pertumbuhan_persediaan.js', init: 'initLaporanPertumbuhanPersediaanPage' },
        '/laporan-penjualan-item': { script: 'laporan_penjualan_item.js', init: 'initLaporanPenjualanItemPage' },
        '/laporan-penjualan': { script: 'laporan_penjualan.js', init: 'initLaporanPenjualanPage' },
        '/penjualan': { script: 'penjualan.js', init: 'initPenjualanPage' },
        '/neraca-saldo': { script: 'neraca_saldo.js', init: 'initNeracaSaldoPage' },
        '/roles': { script: 'roles.js', init: 'initRolesPage' },
        '/hr/karyawan': { script: 'hr/karyawan.js', init: 'initKaryawanPage' },
        '/hr/jabatan': { script: 'hr/jabatan.js', init: 'initJabatanPage' },
        '/hr/divisi': { script: 'hr/divisi.js', init: 'initDivisiPage' },
        '/hr/master-dashboard': { script: 'hr/master_dashboard.js', init: 'initMasterDashboardPage' },
        '/hr/kantor': { script: 'hr/kantor.js', init: 'initKantorPage' },
        '/hr/golongan-absensi': { script: 'hr/golonganabsensi.js', init: 'initGolonganAbsensiPage' },
        '/hr/status-absensi': { script: 'hr/statusabsensi.js', init: 'initStatusAbsensiPage' },
        '/hr/jadwal-kerja': { script: 'hr/jadwalkerja.js', init: 'initJadwalKerjaPage' },
        '/hr/absensi': { script: 'hr/absensi.js', init: 'initAbsensiPage' },
        '/hr/jenis-cuti': { script: 'hr/jeniscuti.js', init: 'initJenisCutiPage' },
        '/hr/manajemen-cuti': { script: 'hr/manajemencuti.js', init: 'initManajemenCutiPage' },
        '/hr/kalender-cuti': { script: 'hr/kalendercuti.js', init: 'initKalenderCutiPage' },
        '/hr/komponen-gaji': { script: 'hr/komponengaji.js', init: 'initKomponenGajiPage' },
        '/hr/golongan-gaji': { script: 'hr/golongangaji.js', init: 'initGolonganGajiPage' },
        '/hr/penggajian': { script: 'hr/penggajian.js', init: 'initPenggajianPage' },
        '/hr/payroll-dashboard': { script: 'hr/payroll_dashboard.js', init: 'initPayrollDashboardPage' },
        '/hr/laporan': { script: 'hr/laporan.js', init: 'initLaporanPage' },
        '/hr/pengaturan-pajak': { script: 'hr/pengaturan_pajak.js', init: 'initPengaturanPajakPage' },
        '/hr/portal/dashboard': { script: 'hr/portal/dashboard.js', init: 'initPortalDashboardPage' },
        '/hr/portal/profil': { script: 'hr/portal/profil.js', init: 'initPortalProfilPage' },
        '/hr/portal/absensi': { script: 'hr/portal/absensi.js', init: 'initPortalAbsensiPage' },
        '/hr/portal/slip-gaji': { script: 'hr/portal/slipgaji.js', init: 'initPortalSlipGajiPage' },
        '/buku-panduan': { script: null, init: null } // Halaman statis
    };

    const route = routeMap[cleanPath];

    if (route) {
        if (route.script && route.init) {
            loadScript(`${basePath}/assets/js/${route.script}`)
                .then(() => {
                    if (typeof window[route.init] === 'function') {
                        window[route.init]();
                    } else {
                        console.error(`Initialization function ${route.init} not found.`);
                    }
                })
                .catch(err => console.error(err));
        }
        // Jika script null, tidak melakukan apa-apa (untuk halaman statis)
    } else {
        console.warn(`No script definition for path: ${cleanPath}`);
    }
}

/**
 * Loads a script dynamically and returns a promise that resolves when it's loaded.
 * @param {string} src The source URL of the script.
 * @returns {Promise<void>}
 */
function loadScript(src) {
    return new Promise((resolve, reject) => {
        // Cek jika skrip sudah ada di dalam body
        const existingScript = document.querySelector(`body > script[src="${src}"]`);
        if (existingScript) {
            // Jika sudah ada, langsung resolve.
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = src;
        script.onload = () => {
            // Hapus skrip setelah dieksekusi untuk menjaga kebersihan DOM
            script.remove();
            resolve();
        };
        script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
        document.body.appendChild(script);
    });
}


// =================================================================================
// PAGE-SPECIFIC INITIALIZATION FUNCTIONS
// =================================================================================

function initKategoriPage() {
    console.log("Halaman Kategori diinisialisasi. (Belum diimplementasikan)");
}

/**
 * Calculates time since a given date.
 * @param {Date} date The date to compare against.
 * @returns {string} A human-readable string like "5 menit lalu".
 */
function formatDate(dateString) {
    if (!dateString || dateString.startsWith('0000')) return '';
    try {
        const date = new Date(dateString);
        // Check if the date is valid
        if (isNaN(date.getTime())) {
            return dateString; // Return original string if invalid
        }
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    } catch (e) {
        return dateString; // Return original string on error
    }
}

function timeSince(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " tahun lalu";
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " bulan lalu";
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " hari lalu";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " jam lalu";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " menit lalu";
    return "Baru saja";
}

/**
 * Updates sidebar badges (e.g. pending leave requests).
 */
function updateSidebarBadges() {
    fetch(`${basePath}/api/hr/manajemen-cuti?action=get_pending_count`)
        .then(response => response.json())
        .then(data => {
            const hasPending = data.success && data.total > 0;
            const totalPending = hasPending ? data.total : 0;

            // Update sidebar badge if it exists
            const badgeCuti = document.getElementById('badge-hr_manajemen_cuti');
            if (badgeCuti) {
                if (hasPending) {
                    badgeCuti.textContent = totalPending;
                    badgeCuti.classList.remove('hidden');
                } else {
                    badgeCuti.classList.add('hidden');
                }
            }

            // Update waffle menu button if it exists
            const waffleButton = document.getElementById('waffle-menu-button');
            if (waffleButton) {
                if (hasPending) {
                    waffleButton.classList.add('is-shaking');
                } else {
                    waffleButton.classList.remove('is-shaking');
                }
            }
        })
        .catch(err => console.error('Error fetching badges:', err));
}

/**
 * Toggles the sidebar between minimized and full-width states.
 */
function toggleSidebar() {
    // Mobile Logic (< 1024px)
    if (window.innerWidth < 1024) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar) sidebar.classList.toggle('-translate-x-full');
        if (overlay) overlay.classList.toggle('hidden');
    } 
    // Desktop Logic (>= 1024px)
    else {
        const body = document.body;
        const isMinimized = body.classList.toggle('sidebar-minimized');
        localStorage.setItem('sidebar_minimized', isMinimized);
    }
}

/**
 * Toggles a collapsible menu item in the sidebar.
 * @param {HTMLElement} button The button element that was clicked.
 */
function toggleCollapse(button) {
    // Find the main container for the collapsible menu
    const wrapper = button.closest('[data-controller="collapse"]');
    if (!wrapper) return;

    // Find the content to show/hide and the icon to rotate
    const content = wrapper.querySelector('.collapse-content');
    const icon = button.querySelector('.bi-chevron-down');

    if (content) {
        // If content is open (has a maxHeight), close it. Otherwise, open it.
        if (content.style.maxHeight && content.style.maxHeight !== '0px') {
            content.style.maxHeight = '0px';
        } else {
            content.style.maxHeight = content.scrollHeight + 'px';
        }
    }
    if (icon) {
        icon.classList.toggle('rotate-180'); // Tailwind class for rotation
    }
}

/**
 * Sets the layout mode and reloads the page.
 * @param {string} mode 'sidebar' or 'top_nav'
 */
function setLayoutMode(mode) {
    document.cookie = "layout_mode=" + mode + "; path=/; max-age=31536000"; // 1 year
    window.location.reload();
}

/**
 * Toggles a flyout menu in the icon-menu layout.
 * @param {HTMLElement} button The button element that was clicked.
 */
function toggleFlyoutMenu(button) {
    const wrapper = button.closest('[data-controller="flyout"]');
    if (!wrapper) return;

    const menu = wrapper.querySelector('.flyout-menu');
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');

    // Close all other flyouts first
    document.querySelectorAll('.flyout-menu').forEach(m => {
        if (m !== menu) {
            m.classList.add('hidden');
        }
    });

    // Then toggle the current one
    if (isHidden) {
        menu.classList.remove('hidden');
        // Reposition if it goes off-screen
        const rect = menu.getBoundingClientRect();
        if (rect.bottom > window.innerHeight) {
            menu.style.top = 'auto';
            menu.style.bottom = '5px';
        } else {
            menu.style.top = '0';
            menu.style.bottom = 'auto';
        }
    } else {
        menu.classList.add('hidden');
    }
}

/**
 * Initializes tooltips for the icon-menu layout.
 */
function initTooltips() {
    if (document.documentElement.classList.contains('layout-icon-menu')) {
        let tooltipEl = document.createElement('div');
        tooltipEl.className = 'fixed p-2 text-sm font-medium text-white bg-gray-900 dark:bg-black rounded-md shadow-sm opacity-0 transition-opacity duration-200 z-[100] pointer-events-none whitespace-nowrap';
        document.body.appendChild(tooltipEl);

        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', (e) => {
                tooltipEl.textContent = el.dataset.tooltip;
                tooltipEl.style.opacity = '1';
                const rect = el.getBoundingClientRect();
                tooltipEl.style.top = `${rect.top + rect.height / 2 - tooltipEl.offsetHeight / 2}px`;
                tooltipEl.style.left = `${rect.right + 10}px`;
            });
            el.addEventListener('mouseleave', () => { tooltipEl.style.opacity = '0'; });
        });
    }
}

/**
 * Initializes the icon menu search functionality.
 */
function initIconMenuSearch() {
    const searchInput = document.getElementById('icon-menu-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const grid = document.getElementById('icon-menu-grid');
        if (!grid) return;

        const items = grid.querySelectorAll('a');
        const headers = grid.querySelectorAll('div.col-span-3');

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(term)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });

        // Hide headers if searching to keep the view clean
        if (term.length > 0) {
            headers.forEach(header => header.classList.add('hidden'));
        } else {
            headers.forEach(header => header.classList.remove('hidden'));
        }
    });
    
    // Prevent dropdown from closing when clicking inside search input
    searchInput.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

function toggleDropdown(element) {
    // Stop shaking animation if user clicks the waffle menu
    if (element.id === 'waffle-menu-button') {
        element.classList.remove('is-shaking');
    }

    const menu = element.nextElementSibling;
    menu.classList.toggle('hidden');
    
    // Auto focus search input if exists inside the menu
    if (!menu.classList.contains('hidden')) {
        menu.classList.add('dropdown-animate-in');
        const searchInput = menu.querySelector('input');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 50);
        }
    }
}

/**
 * Closes the dropdown menu when an item is clicked.
 * @param {HTMLElement} element The element inside the dropdown that was clicked.
 */
function closeDropdown(element) {
    const menu = element.closest('.dropdown-menu');
    if (menu) {
        menu.classList.add('hidden');
    }
}

 function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

// =================================================================================
// GLOBAL INITIALIZATION
// =================================================================================

document.addEventListener('DOMContentLoaded', function () {
    // Update badges on load
    updateSidebarBadges();

    initTooltips();
    // Apply sidebar preference on load
    if (localStorage.getItem('sidebar_minimized') === 'true' && window.innerWidth >= 1024) {
        document.body.classList.add('sidebar-minimized');
    }

    // --- Theme Switcher ---
    const themeSwitcher = document.getElementById('theme-switcher');
    if (themeSwitcher) {
        const themeText = document.getElementById('theme-switcher-text');
        const htmlEl = document.documentElement;

        // Function to set the switcher state
        const setSwitcherState = (theme) => {
            if (theme === 'dark') {
                themeText.textContent = 'Mode Terang';
            } else {
                themeText.textContent = 'Mode Gelap';
            }
        };

        // Apply saved theme on load
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            htmlEl.classList.toggle('dark', savedTheme === 'dark');
            setSwitcherState(savedTheme);
        } else {
            // Set initial state based on default
            const currentTheme = htmlEl.classList.contains('dark') ? 'dark' : 'light';
            setSwitcherState(currentTheme);
        }

        themeSwitcher.addEventListener('click', (e) => {
            e.preventDefault();
            const isDark = htmlEl.classList.toggle('dark');
            const newTheme = isDark ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            setSwitcherState(newTheme);
        });
    }
    // --- Panic Button Logic ---
    const panicButton = document.getElementById('panic-button');
    if (panicButton) {
        let holdTimeout;
        const originalButtonHtml = panicButton.innerHTML;

        const startHold = (e) => {
            e.preventDefault();
            // Prevent action if button is already processing
            if (panicButton.disabled) return;

            panicButton.classList.add('is-holding');

            holdTimeout = setTimeout(async () => {
                panicButton.disabled = true;
                panicButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...`;

                try {
                    const response = await fetch(`${basePath}/api/panic`, { method: 'POST' });
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Server error');
                    }

                    showToast(result.message, 'success');
                    panicButton.classList.remove('btn-danger');
                    panicButton.classList.add('btn-success');
                    panicButton.innerHTML = `<i class="bi bi-check-circle-fill"></i> Terkirim`;

                } catch (error) {
                    // Use error.message if available from the thrown error
                    showToast(error.message || 'Gagal mengirim sinyal darurat.', 'error');
                    panicButton.innerHTML = `<i class="bi bi-x-circle-fill"></i> Gagal`;
                } finally {
                    // Reset button to original state after a few seconds
                    setTimeout(() => {
                        panicButton.classList.remove('is-holding', 'btn-success');
                        panicButton.classList.add('btn-danger');
                        panicButton.innerHTML = originalButtonHtml;
                        panicButton.disabled = false;
                    }, 5000); // Reset after 5 seconds
                }
            }, 3000); // 3 seconds
        };

        const cancelHold = () => {
            if (panicButton.disabled) return;
            clearTimeout(holdTimeout);
            panicButton.classList.remove('is-holding');
        };

        panicButton.addEventListener('mousedown', startHold);
        panicButton.addEventListener('touchstart', startHold, { passive: false });
        panicButton.addEventListener('mouseup', cancelHold);
        panicButton.addEventListener('mouseleave', cancelHold);
        panicButton.addEventListener('touchend', cancelHold);
    }

    // --- Live Clock in Header ---
    const clockElement = document.getElementById('live-clock');
    if (clockElement) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        function updateLiveClock() {
            const now = new Date();
            const dayName = days[now.getDay()];
            const day = now.getDate().toString().padStart(2, '0');
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');

            clockElement.textContent = `${dayName}, ${day} ${monthName} ${year} ${hours}:${minutes}:${seconds}`;
        }

        updateLiveClock(); // Initial call
        setInterval(updateLiveClock, 1000); // Update every second
    }

    // --- SPA Navigation Listeners ---
    // Intercept clicks on internal links
    document.body.addEventListener('click', e => {
        const link = e.target.closest('a');
        // Check if it's an internal, navigable link that doesn't open a new tab or has the 'data-spa-ignore' attribute
        if (link && link.href && link.target !== '_blank' && new URL(link.href).origin === window.location.origin && link.getAttribute('data-spa-ignore') === null) {
            e.preventDefault();
            if (new URL(link.href).pathname !== window.location.pathname) {
                navigate(link.href);
            }
        }
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', e => {
        if (e.state && e.state.path) {
            navigate(e.state.path, false); // false = don't push a new state
        }
    });

    // --- Initial Page Load ---
    updateActiveSidebarLink(window.location.pathname);
    runPageScripts(window.location.pathname);

    // --- Initialize Global Components ---
    initGlobalSearch();
    initRecurringModal();
    initSidebarSearch();
    initIconMenuSearch();

    // Inisialisasi Flatpickr untuk modal global (dipindahkan dari footer)
    if (typeof flatpickr !== 'undefined') {
        const startEl = document.querySelector("#recurring-start-date");
        const endEl = document.querySelector("#recurring-end-date");
        if (startEl) flatpickr(startEl, { dateFormat: "d-m-Y", allowInput: true });
        if (endEl) flatpickr(endEl, { dateFormat: "d-m-Y", allowInput: true });
    }
});

// --- Global Theme Color Picker Logic ---
document.addEventListener('DOMContentLoaded', function() {
    const savedColor = localStorage.getItem('theme_color');
    const colorPicker = document.getElementById('theme-color-picker');

    const applyThemeColor = (color) => {
        if (!color) return;
        document.documentElement.style.setProperty('--theme-color', color);
    };

    if (savedColor) {
        applyThemeColor(savedColor);
        if (colorPicker) {
            colorPicker.value = savedColor;
        }
    }

    if (colorPicker) {
        colorPicker.addEventListener('input', (e) => {
            applyThemeColor(e.target.value);
            localStorage.setItem('theme_color', e.target.value);
        });
    }
});
// --- Recurring Modal Logic (Global) ---
function initRecurringModal() {
    const saveBtn = document.getElementById('save-recurring-template-btn');
    const form = document.getElementById('recurring-form');
    if (!saveBtn || !form) return;

    saveBtn.addEventListener('click', async () => {
        const response = await fetch(`${basePath}/api/recurring`, { method: 'POST', body: new FormData(form) });
        const result = await response.json();
        showToast(result.message, result.status === 'success' ? 'success' : 'error');
        if (result.status === 'success') closeModal('recurringModal');
    });
}

function openRecurringModal(type, data, existingTemplate = null) {
    const recurringForm = document.getElementById('recurring-form');
    const startDateEl = document.getElementById('recurring-start-date');
    const endDateEl = document.getElementById('recurring-end-date');

    if (!recurringForm || !startDateEl || !endDateEl) return;

    recurringForm.reset();
    document.getElementById('recurring-template-type').value = type;
    document.getElementById('recurring-template-data').value = JSON.stringify(data);

    // Ambil instance flatpickr yang sudah diinisialisasi di footer.php
    const startDatePicker = startDateEl._flatpickr;
    const endDatePicker = endDateEl._flatpickr;

    if (existingTemplate) {
        document.getElementById('recurringModalLabel').textContent = 'Edit Jadwal Berulang';
        document.getElementById('recurring-id').value = existingTemplate.id;
        document.getElementById('recurring-name').value = existingTemplate.name;
        document.getElementById('recurring-frequency-interval').value = existingTemplate.frequency_interval;
        document.getElementById('recurring-frequency-unit').value = existingTemplate.frequency_unit;
        if (startDatePicker) startDatePicker.setDate(existingTemplate.start_date, true, "Y-m-d");
        if (endDatePicker && existingTemplate.end_date) endDatePicker.setDate(existingTemplate.end_date, true, "Y-m-d");
    } else {
        document.getElementById('recurringModalLabel').textContent = 'Atur Jadwal Berulang';
        document.getElementById('recurring-id').value = '';
        if (startDatePicker) startDatePicker.setDate(new Date(), true);
    }

    openModal('recurringModal');
}

function initGlobalSearch() {
    const searchModalEl = document.getElementById('globalSearchModal');
    if (!searchModalEl) return;

    const searchInput = document.getElementById('global-search-input');
    const resultsContainer = document.getElementById('global-search-results');
    const spinner = document.getElementById('global-search-spinner');

    let debounceTimer;

    const performSearch = async () => {
        const term = searchInput.value.trim();

        if (term.length < 3) {
            resultsContainer.innerHTML = '<p class="text-muted text-center">Masukkan minimal 3 karakter untuk mencari.</p>';
            spinner.style.display = 'none';
            return;
        }

        spinner.style.display = 'block';

        try {
            const response = await fetch(`${basePath}/api/global-search?term=${encodeURIComponent(term)}`);
            const result = await response.json();

            resultsContainer.innerHTML = '';
            if (result.status === 'success' && result.data.length > 0) {
                result.data.forEach(item => {
                    const resultItem = `
                        <a href="${basePath}${item.link}" class="search-result-item block p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <div class="d-flex align-items-center">
                                <i class="bi ${item.icon} fs-4 me-3 text-primary"></i>
                                <div>
                                    <div class="fw-bold">${item.title}</div>
                                    <small class="text-muted">${item.subtitle}</small>
                                </div>
                                <span class="badge bg-secondary ms-auto">${item.type}</span>
                            </div>
                        </a>
                    `;
                    resultsContainer.insertAdjacentHTML('beforeend', resultItem);
                });
            } else if (result.status === 'success') {
                resultsContainer.innerHTML = `<p class="text-muted text-center">Tidak ada hasil ditemukan untuk "<strong>${term}</strong>".</p>`;
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            resultsContainer.innerHTML = `<p class="text-danger text-center">Terjadi kesalahan: ${error.message}</p>`;
        } finally {
            spinner.style.display = 'none';
        }
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        spinner.style.display = 'block';
        debounceTimer = setTimeout(performSearch, 500); // Debounce for 500ms
    });

    resultsContainer.addEventListener('click', (e) => {
        const link = e.target.closest('a.search-result-item');
        if (link) {
            e.preventDefault();
            const url = link.href;
            closeModal('globalSearchModal');
            // Gunakan fungsi navigate SPA untuk pindah halaman dan menangani hash
            navigate(url);
        }
    });

    // Add a global click listener to close flyouts
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[data-controller="flyout"]')) {
            document.querySelectorAll('.flyout-menu').forEach(m => m.classList.add('hidden'));
        }
    });

    // Add keyboard shortcut (Ctrl+K or Cmd+K)
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault(); // Prevent default browser action (e.g., search)
            openModal('globalSearchModal');
            setTimeout(() => searchInput.focus(), 50);
        }

        // Close dropdowns and flyouts on ESC
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu:not(.hidden)').forEach(menu => {
                menu.classList.add('hidden');
            });
            document.querySelectorAll('.flyout-menu:not(.hidden)').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
}

/**
 * Initializes the sidebar search functionality.
 */
function initSidebarSearch() {
    const searchInput = document.getElementById('sidebar-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const nav = document.getElementById('sidebar-nav');
        if (!nav) return;

        const children = nav.children;
        
        Array.from(children).forEach(child => {
            // Handle Headers (div without data-controller)
            if (child.tagName === 'DIV' && !child.hasAttribute('data-controller')) {
                 if (term) child.classList.add('hidden');
                 else child.classList.remove('hidden');
                 return;
            }

            // Handle Simple Links (tag A directly)
            if (child.tagName === 'A') {
                const text = child.textContent.toLowerCase();
                if (text.includes(term)) {
                    child.classList.remove('hidden');
                } else {
                    child.classList.add('hidden');
                }
            }

            // Handle Collapsible Menus (div with data-controller="collapse")
            if (child.hasAttribute('data-controller')) {
                const button = child.querySelector('button');
                const parentText = button.textContent.toLowerCase();
                const content = child.querySelector('.collapse-content');
                const items = content.querySelectorAll('li');
                let hasMatch = false;

                items.forEach(li => {
                    const link = li.querySelector('a');
                    const text = link ? link.textContent.toLowerCase() : '';
                    if (text.includes(term)) {
                        li.classList.remove('hidden');
                        hasMatch = true;
                    } else {
                        li.classList.add('hidden');
                    }
                });

                if (term === '') {
                    // Reset state if search is empty
                    child.classList.remove('hidden');
                    items.forEach(li => li.classList.remove('hidden'));
                    
                    // Collapse unless active
                    const hasActive = content.querySelector('.text-primary'); 
                    if (!hasActive) { // Only collapse if it's not the active parent menu
                        content.style.maxHeight = '0px';
                        const icon = button.querySelector('.bi-chevron-down');
                        if(icon) icon.classList.remove('rotate-180');
                    }
                } else {
                    // Search logic
                    if (hasMatch || parentText.includes(term)) {
                        child.classList.remove('hidden');
                        content.style.maxHeight = content.scrollHeight + 'px'; // Expand
                        const icon = button.querySelector('.bi-chevron-down');
                        if(icon) icon.classList.add('rotate-180');
                        
                        // If parent matches, show all children
                        if (parentText.includes(term)) {
                            items.forEach(li => li.classList.remove('hidden'));
                        }
                    } else {
                        child.classList.add('hidden');
                    }
                }
            }
        });
    });
}
/**
 * Renders pagination controls.
 * @param {HTMLElement} container The container element for the pagination.
 * @param {object|null} pagination The pagination object from the API.
 * @param {function} onPageClick The callback function to execute when a page link is clicked.
 */
function renderPagination(container, pagination, onPageClick) {
    if (!container) return;
    container.innerHTML = '';
    if (!pagination || pagination.total_pages <= 1) {
        // Optional: show info even for single page
        const info = document.getElementById(container.id.replace('pagination', 'pagination-info'));
        if (info && pagination && pagination.total_records > 0) {
            info.textContent = `Menampilkan ${pagination.total_records} dari ${pagination.total_records} data.`;
        }
        return;
    }

    const { current_page, total_pages } = pagination;

    const createPageItem = (page, text, isDisabled = false, isActive = false) => {
        const a = document.createElement('a');
        a.href = '#';
        a.dataset.page = page;
        a.innerHTML = text;

        let baseClasses = 'flex items-center justify-center px-3 h-8 leading-tight';
        let stateClasses = '';
        if (isDisabled) {
            stateClasses = 'text-gray-500 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 cursor-not-allowed';
        } else if (isActive) {
            stateClasses = 'text-white bg-primary border border-primary z-10';
        } else {
            stateClasses = 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white';
        }
        a.className = `${baseClasses} ${stateClasses}`;
        return a;
    };

    const ul = document.createElement('ul');
    ul.className = 'inline-flex -space-x-px text-sm';

    const prevItem = createPageItem(current_page - 1, 'Prev', current_page === 1);
    prevItem.classList.add('rounded-l-lg');
    ul.appendChild(document.createElement('li')).appendChild(prevItem);

    const maxPagesToShow = 5;
    let startPage, endPage;
    if (total_pages <= maxPagesToShow) {
        startPage = 1; endPage = total_pages;
    } else {
        const maxPagesBeforeCurrent = Math.floor(maxPagesToShow / 2);
        const maxPagesAfterCurrent = Math.ceil(maxPagesToShow / 2) - 1;
        if (current_page <= maxPagesBeforeCurrent) { startPage = 1; endPage = maxPagesToShow; } 
        else if (current_page + maxPagesAfterCurrent >= total_pages) { startPage = total_pages - maxPagesToShow + 1; endPage = total_pages; } 
        else { startPage = current_page - maxPagesBeforeCurrent; endPage = current_page + maxPagesAfterCurrent; }
    }

    if (startPage > 1) {
        ul.appendChild(document.createElement('li')).appendChild(createPageItem(1, '1'));
        if (startPage > 2) ul.appendChild(document.createElement('li')).appendChild(createPageItem(0, '...', true));
    }

    for (let i = startPage; i <= endPage; i++) {
        ul.appendChild(document.createElement('li')).appendChild(createPageItem(i, i, false, i === current_page));
    }

    if (endPage < total_pages) {
        if (endPage < total_pages - 1) ul.appendChild(document.createElement('li')).appendChild(createPageItem(0, '...', true));
        ul.appendChild(document.createElement('li')).appendChild(createPageItem(total_pages, total_pages));
    }

    const nextItem = createPageItem(current_page + 1, 'Next', current_page === total_pages);
    nextItem.classList.add('rounded-r-lg');
    ul.appendChild(document.createElement('li')).appendChild(nextItem);

    container.appendChild(ul);

    container.addEventListener('click', (e) => {
        e.preventDefault();
        const pageLink = e.target.closest('a[data-page]');
        if (pageLink && !pageLink.classList.contains('cursor-not-allowed')) {
            const page = parseInt(pageLink.dataset.page, 10);
            if (page && page !== current_page) {
                onPageClick(page);
            }
        }
    });
}

function formatNumber(value) {
    if (typeof value !== 'number') return value;
    return new Intl.NumberFormat('id-ID').format(value);
}

/**
 * Debounce function to limit the rate at which a function can fire.
 * @param {Function} func The function to debounce.
 * @param {number} wait The delay in milliseconds.
 * @returns {Function}
 */
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}