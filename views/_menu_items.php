<?php
// This file is included by header.php and contains the sidebar menu structure.
// Load menu configuration
$menu_items = require PROJECT_ROOT . '/config/menus.php';

// Fetch allowed menus for current user
$allowed_menus = [];
$is_admin = false;

// Cek apakah user adalah admin (berdasarkan role session atau role_id 1)
if ((isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1)) {
    $is_admin = true;
}

// Jika bukan admin dan memiliki role_id, ambil menu yang diizinkan dari database
if (!$is_admin) {
    $role_id = $_SESSION['role_id'] ?? null;

    // Fallback: Jika role_id tidak ada di session, coba ambil dari DB berdasarkan username
    if (!$role_id && isset($_SESSION['username'])) {
        $conn = Database::getInstance()->getConnection();
        $stmt_u = $conn->prepare("SELECT role_id FROM users WHERE username = ?");
        $stmt_u->bind_param("s", $_SESSION['username']);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        if ($row_u = $res_u->fetch_assoc()) {
            $role_id = $row_u['role_id'];
            $_SESSION['role_id'] = $role_id; // Simpan ke session untuk request berikutnya
        }
    }

    if ($role_id) {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("SELECT menu_key FROM role_menus WHERE role_id = ?");
        $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $allowed_menus[] = $row['menu_key'];
    }
}

// Pastikan variabel layout_mode tersedia
if (!isset($layout_mode)) $layout_mode = 'sidebar';

if (!function_exists('render_menu_item')) {
function render_menu_item($url, $icon, $text, $key = '', $layout = 'sidebar') {
    $badgeHtml = $key ? '<span id="badge-' . $key . '" class="ml-auto hidden bg-red-100 text-red-800 text-xs font-bold px-2 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300 shadow-sm"></span>' : '';
    
    if ($layout === 'icon_menu') {
        $colors = [
            ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-600 dark:text-indigo-300'],
            ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-300'],
            ['bg' => 'bg-sky-50 dark:bg-sky-900/20', 'text' => 'text-sky-600 dark:text-sky-300'],
            ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-300'],
            ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-300'],
            ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-300'],
            ['bg' => 'bg-teal-50 dark:bg-teal-900/20', 'text' => 'text-teal-600 dark:text-teal-300'],
            ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-300'],
        ];
        $color = $colors[crc32($key) % count($colors)];

        echo '<a href="' . base_url($url) . '" onclick="closeDropdown(this)" class="flex flex-col items-center justify-center p-3 text-center rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 group">
                <div class="w-12 h-12 flex items-center justify-center ' . $color['bg'] . ' ' . $color['text'] . ' rounded-2xl mb-2 shadow-sm ring-1 ring-inset ring-black/5 dark:ring-white/10 group-hover:scale-110 transition-transform duration-200">
                    <i class="' . $icon . ' text-2xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 leading-tight">' . $text . '</span>
              </a>';
    } else {
        echo '<a href="' . base_url($url) . '" class="flex items-center px-3 py-2 text-gray-700 rounded-md dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group transition-all duration-200">
                <i class="' . $icon . ' flex-shrink-0 w-6 h-6 text-lg text-gray-500 transition duration-75 group-hover:text-primary dark:text-gray-400 dark:group-hover:text-white flex items-center justify-center"></i>
                <span class="ml-3 flex-1 whitespace-nowrap font-medium text-sm sidebar-text">' . $text . '</span>
                ' . $badgeHtml . '
              </a>';
    }
}
}

if (!function_exists('render_collapsible_menu')) {
function render_collapsible_menu($id, $icon, $text, $items, $layout = 'sidebar') {
    $items_html = '';
    
    if ($layout === 'icon_menu') {
        // Render Header for Group
        echo '<div class="col-span-3 md:col-span-4 mt-2 mb-1 border-b border-gray-100 dark:border-gray-700 pb-1">
                <span class="text-xs font-bold text-gray-500 uppercase">' . $text . '</span>
              </div>';
        // Render Children as Grid Items
        foreach ($items as $item) {
            // Gunakan logika warna yang sama untuk konsistensi
            $colors = [
                ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-600 dark:text-indigo-300'],
                ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-300'],
                ['bg' => 'bg-sky-50 dark:bg-sky-900/20', 'text' => 'text-sky-600 dark:text-sky-300'],
                ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-300'],
                ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-300'],
                ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-300'],
                ['bg' => 'bg-teal-50 dark:bg-teal-900/20', 'text' => 'text-teal-600 dark:text-teal-300'],
                ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-300'],
            ];
            $color = $colors[crc32($item['key']) % count($colors)];

            echo '<a href="' . base_url($item['url']) . '" onclick="closeDropdown(this)" class="flex flex-col items-center justify-center p-3 text-center rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 group">
                    <div class="w-12 h-12 flex items-center justify-center ' . $color['bg'] . ' ' . $color['text'] . ' rounded-2xl mb-2 shadow-sm ring-1 ring-inset ring-black/5 dark:ring-white/10 group-hover:scale-110 transition-transform duration-200">
                        <i class="' . ($item['icon'] ?? 'bi bi-circle') . ' text-2xl"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 leading-tight">' . $item['label'] . '</span>
                  </a>';
        }
    } else {
        foreach ($items as $item) {
            $items_html .= '
            <li>
                <a href="' . base_url($item['url']) . '" class="flex items-center w-full py-2 pr-3 pl-4 text-sm font-normal text-gray-600 rounded-md transition duration-75 group hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                    <span class="sidebar-text">' . $item['label'] . '</span>
                    <span id="badge-' . $item['key'] . '" class="ml-auto hidden bg-red-100 text-red-800 text-xs font-bold px-2 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300 shadow-sm"></span>
                </a>
            </li>';
        }

        echo '<div data-controller="collapse">
                <button type="button" onclick="toggleCollapse(this)" class="flex items-center justify-between w-full px-3 py-2 text-gray-700 rounded-md transition-all duration-200 group hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                    <span class="flex items-center">
                        <i class="' . $icon . ' flex-shrink-0 w-6 h-6 text-lg text-gray-500 transition duration-75 group-hover:text-primary dark:text-gray-400 dark:group-hover:text-white flex items-center justify-center"></i>
                        <span class="ml-3 flex-1 whitespace-nowrap text-left font-medium text-sm sidebar-text">' . $text . '</span>
                    </span>
                    <i class="bi bi-chevron-down w-5 h-5 text-xs flex items-center justify-center transition-transform duration-200 sidebar-text"></i>
                </button>
                <div class="pl-4 ml-6 mt-1 relative before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:border-l before:border-dashed before:border-gray-300 dark:before:border-gray-600 sidebar-text">
                    <ul class="collapse-content space-y-1">
                        ' . $items_html . '
                    </ul>
                </div>
              </div>';
    }
}
}

function is_menu_allowed($key, $allowed_menus, $is_admin) {
    if ($is_admin) return true;
    return in_array($key, $allowed_menus);
}
?>

<?php
// Pre-process menu items to group by header and filter invisible items
$final_menu_structure = [];
$current_header = null;
$current_items = [];

foreach ($menu_items as $item) {
    if ($item['type'] === 'header') {
        // If we have accumulated items for the previous section, add them to structure
        if (!empty($current_items)) {
            $final_menu_structure[] = [
                'header' => $current_header,
                'items' => $current_items
            ];
        }
        // Start a new section
        $current_header = $item;
        $current_items = [];
        continue;
    }

    // Check permission for the item
    if (!is_menu_allowed($item['key'], $allowed_menus, $is_admin ?? false)) {
        continue;
    }

    if ($item['type'] === 'collapse') {
        // Filter children based on permissions
        $visible_children = [];
        if (isset($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as $child) {
                if (is_menu_allowed($child['key'], $allowed_menus, $is_admin ?? false)) {
                    $child['text'] = $child['label']; // Helper expects 'text'
                    $visible_children[] = $child;
                }
            }
        }
        
        // Only render parent if it has visible children
        if (!empty($visible_children)) {
            // Clone item to avoid modifying original config if used elsewhere
            $item_copy = $item;
            $item_copy['children'] = $visible_children;
            $current_items[] = $item_copy;
        }
    } else {
        // Regular item
        $current_items[] = $item;
    }
}

// Add the last section if it has items
if (!empty($current_items)) {
    $final_menu_structure[] = [
        'header' => $current_header,
        'items' => $current_items
    ];
}
?>

<!-- Menu Items Rendered -->
<?php foreach ($final_menu_structure as $section): ?>
    <?php 
    // Render Header if exists
    if ($section['header']) {
        $header = $section['header'];
        if ($layout_mode === 'icon_menu') {
             echo '<div class="col-span-3 md:col-span-4 mt-3 mb-1 px-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">' . $header['label'] . '</p>
                  </div>';
        }
        elseif ($layout_mode === 'sidebar') {
            echo '<div class="mt-4 mb-2 px-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">' . $header['label'] . '</p>
                    <div class="sidebar-header-divider"></div>
                  </div>';
        }
    }

    // Render Items
    foreach ($section['items'] as $item) {
        if ($item['type'] === 'item') {
            render_menu_item($item['url'], $item['icon'], $item['label'], $item['key'], $layout_mode);
        } elseif ($item['type'] === 'collapse') {
            render_collapsible_menu($item['key'] . '-menu', $item['icon'], $item['label'], $item['children'], $layout_mode);
        }
    }
    ?>
<?php endforeach; ?>