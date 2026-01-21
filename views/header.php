<?php
// Ambil pengaturan aplikasi dari database untuk digunakan di seluruh UI
$app_settings = [];
$settings_conn = Database::getInstance()->getConnection();
$settings_result = $settings_conn->query("SELECT setting_key, setting_value FROM settings");
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $app_settings[$row['setting_key']] = $row['setting_value'];
    }
}
$app_name = htmlspecialchars($app_settings['app_name'] ?? 'Aplikasi RT');
$notification_interval = (int)($app_settings['notification_interval'] ?? 15000);
$log_cleanup_days = (int)($app_settings['log_cleanup_interval_days'] ?? 180);

// Determine Layout Mode
$layout_mode = $_COOKIE['layout_mode'] ?? 'sidebar'; // 'sidebar' or 'icon_menu'
?>
<!doctype html>
<html lang="en" class="<?= $layout_mode === 'icon_menu' ? 'layout-icon-menu' : '' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $app_name ?></title>    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <?php $v=date("Ymd"); ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v='.$v) ?>">
    <!-- Favicon  -->
    <link rel="icon" href="assets/favicon.png" />
    <script>
        // Konfigurasi Tailwind
        tailwind.config = {
            darkMode: 'class', // atau 'media'
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: 'var(--theme-color, #007aff)',
                            '50': 'var(--theme-color-50, #e6f2ff)',
                            '100': 'var(--theme-color-100, #b3d9ff)',
                            '500': 'var(--theme-color-500, #007aff)',
                            '600': 'var(--theme-color-600, #006de6)',
                        },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Helvetica', 'Arial', 'sans-serif', '"Apple Color Emoji"', '"Segoe UI Emoji"', '"Segoe UI Symbol"'],
                    }
                }
            }
        }

        const userRole = '<?= $_SESSION['role'] ?? 'warga' ?>';
        const username = '<?= $_SESSION['username'] ?? '' ?>';
        const basePath = '<?= BASE_PATH ?>';
        const notificationInterval = <?= $notification_interval ?>;
        const logCleanupDays = <?= $log_cleanup_days ?>;
        const layoutMode = '<?= $layout_mode ?>';

        // Load Sidebar State immediately to prevent flash
        if (layoutMode === 'sidebar' && localStorage.getItem('sidebar_minimized') === 'true' && window.innerWidth >= 1024) {
            document.documentElement.classList.add('sidebar-minimized'); // Use html/body class
            // We add it to body in DOMContentLoaded, but adding to html here helps early rendering
        }
    </script>
    <style>
        /* Custom scrollbar untuk sidebar */
        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.2); border-radius: 10px; }
        html.dark .sidebar-scroll::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.2); }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 font-sans">
<div id="app-container" class="flex h-screen overflow-hidden">
    <?php if ($layout_mode === 'sidebar'): ?>
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-40 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-700 px-4 flex-shrink-0">
            <a href="<?= base_url('/dashboard') ?>" class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-white truncate">
                <?php
                $logo_path = $app_settings['app_logo'] ?? null;
                $logo_url = $logo_path ? base_url($logo_path) : base_url('assets/img/logo.png');
                ?>
                <img src="<?= $logo_url ?>" alt="Logo" class="h-8 w-8 object-contain rounded">
                <span class="hidden-in-collapsed sidebar-text"><?= $app_name ?></span>
            </a>
        </div>

        <!-- Sidebar Search -->
        <div class="px-3 pt-3 pb-1" id="sidebar-search-container">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 pointer-events-none">
                    <i class="bi bi-search text-sm"></i>
                </span>
                <input type="text" id="sidebar-search" class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 transition-colors" placeholder="Cari menu...">
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav id="sidebar-nav" class="flex-1 overflow-y-auto sidebar-scroll p-2">
            <?php require_once __DIR__ . '/_menu_items.php'; ?>
        </nav>
    </aside>

    <!-- Mobile Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>
    <?php endif; ?>

    <!-- Content Area Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 relative">
        <!-- SPA Spinner Overlay (Scoped to Content) -->
        <div id="spa-spinner-overlay" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-[60] flex flex-col items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
            <?php
            $logo_url_spinner = isset($app_settings['app_logo']) && !empty($app_settings['app_logo']) ? base_url($app_settings['app_logo']) : base_url('assets/img/logo.png');
            ?>
            <div class="relative animate-flip-horizontal">
                <img src="<?= $logo_url_spinner ?>" alt="Loading..." class="w-16 h-16">
                <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/50 to-transparent opacity-0 animate-shine pointer-events-none"></div>
            </div>
            <div class="w-12 h-1 bg-black/20 rounded-full mt-2 animate-shadow-pulse"></div>
            <span class="mt-3 text-primary font-semibold text-sm tracking-wider loading-text">Memuat...</span>
        </div>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto content-wrapper">
        <!-- Top Navbar -->
        <header class="sticky top-0 shadow-sm border-b border-gray-200 dark:border-gray-700 h-16 flex items-center justify-between px-4 lg:px-6 flex-shrink-0 z-50">
            <!-- Background Blur Layer (Separated to fix fixed-positioning context for children) -->
            <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm -z-10"></div>
            <!-- Left side: Toggle & Title -->
            <div class="flex items-center">
                <?php if ($layout_mode === 'sidebar'): // Hanya tampilkan toggle di mode sidebar biasa ?>
                <button onclick="toggleSidebar()" class="text-gray-500 dark:text-gray-400 focus:outline-none p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="bi bi-list text-2xl"></i>
                </button>
                <?php endif; ?>
                <h1 id="page-title" class="text-xl font-semibold text-gray-800 dark:text-white ml-3 hidden sm:block">Dashboard</h1>
                <div id="live-clock" class="text-gray-500 dark:text-gray-400 text-sm font-medium ml-4 pl-4 border-l border-gray-300 dark:border-gray-600 hidden md:block"></div>
            </div>

            <!-- Right side: Clock, Search, Profile -->
            <div class="flex items-center space-x-2">
                
                <!-- Waffle Menu (Icon Menu Mode) -->
                <?php if ($layout_mode === 'icon_menu'): ?>
                <div class="relative" data-controller="dropdown">
                    <button id="waffle-menu-button" onclick="toggleDropdown(this)" class="p-2 rounded-md text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none" title="Menu Aplikasi">
                        <i class="bi bi-grid-3x3-gap-fill text-xl"></i>
                    </button>
                    <div class="dropdown-menu waffle-menu-dropdown hidden absolute right-0 mt-2 w-[480px] bg-white/90 dark:bg-gray-800/90 backdrop-blur-md rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 p-4 max-h-[80vh] overflow-y-auto">
                        <div class="mb-3 relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 pointer-events-none">
                                <i class="bi bi-search text-sm"></i>
                            </span>
                            <input type="text" id="icon-menu-search" class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 transition-colors" placeholder="Cari menu...">
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-4 gap-2" id="icon-menu-grid">
                            <?php require __DIR__ . '/_menu_items.php'; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Profile Dropdown -->
                <div class="relative" data-controller="dropdown">
                    <button onclick="toggleDropdown(this)" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="bi bi-person-circle text-xl"></i>
                        <span class="hidden md:inline">
                            <?php
                            $h = date('H');
                            if ($h >= 3 && $h < 11) $greet = "Selamat Pagi";
                            elseif ($h >= 11 && $h < 15) $greet = "Selamat Siang";
                            elseif ($h >= 15 && $h < 18) $greet = "Selamat Sore";
                            else $greet = "Selamat Malam";
                            echo $greet . ", " . htmlspecialchars($_SESSION['username']);
                            ?>
                        </span>
                        <i class="bi bi-chevron-down text-xs"></i>
                    </button>
                    <div class="dropdown-menu hidden absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                        <a href="#" id="theme-switcher" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="bi bi-moon-stars-fill me-2"></i><span id="theme-switcher-text">Mode Gelap</span>
                        </a>
                        
                        <!-- Layout Switcher -->
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Layout Menu</div>
                        <button onclick="setLayoutMode('sidebar')" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 <?= $layout_mode === 'sidebar' ? 'bg-gray-50 dark:bg-gray-700 font-bold' : '' ?>">
                            <i class="bi bi-layout-sidebar me-2"></i> Sidebar (Default)
                        </button>
                        <button onclick="setLayoutMode('icon_menu')" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 <?= $layout_mode === 'icon_menu' ? 'bg-gray-50 dark:bg-gray-700 font-bold' : '' ?>">
                            <i class="bi bi-grid-fill me-2"></i> Icon Menu
                        </button>

                        <div class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                            <label for="theme-color-picker" class="flex items-center"><i class="bi bi-palette-fill me-2"></i>Warna Tema</label>
                            <input type="color" id="theme-color-picker" class="w-8 h-8 p-0 border-none rounded" value="#007aff" title="Pilih warna tema Anda">
                        </div>
                        <a href="<?= base_url('/my-profile/change-password') ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="bi bi-key-fill me-2"></i>Ganti Password
                        </a>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <a href="<?= base_url('/logout') ?>" data-spa-ignore id="logout-link" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Scrollable Area -->
        <main id="main-content" class="flex-1 p-4 sm:p-6">

<!-- Global Search Modal -->
<div id="globalSearchModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-start justify-center min-h-screen pt-16 px-4 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('globalSearchModal')"></div>
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="p-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="bi bi-search text-gray-400"></i>
                    </span>
                    <input type="text" class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary" id="global-search-input" placeholder="Ketik disini apa yang dicari..." autocomplete="off">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3" id="global-search-spinner" style="display: none;">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary"></div>
                    </span>
                </div>
            </div>
            <div id="global-search-results" class="px-4 pb-4 max-h-[60vh] overflow-y-auto">
                <p class="text-gray-500 text-center py-8">Masukkan kata kunci untuk memulai pencarian.</p>
            </div>
        </div>
    </div>
</div>