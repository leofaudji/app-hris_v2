<?php
// File ini tidak memerlukan header/footer karena merupakan halaman mandiri
require_once __DIR__ . '/includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= get_setting('app_name', 'Aplikasi Keuangan') ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/assets/css/style.css') ?>">
    <link rel="icon" href="<?= base_url('assets/favicon.png') ?>">
    <?php
    $login_bg_color = get_setting('login_bg_color', '#075E54');
    $login_btn_color = get_setting('login_btn_color', '#25D366');
    ?>
    <style>
        :root {
            --brand-bg-color: <?= htmlspecialchars($login_bg_color) ?>;
            --brand-btn-color: <?= htmlspecialchars($login_btn_color) ?>;
        }
        /* Animasi Kursor Ketik */
        @keyframes cursor-blink {
            0%, 100% { border-color: transparent; }
            50% { border-color: white; }
        }
        .typing-cursor {
            border-right: 3px solid white;
            animation: cursor-blink 0.75s step-end infinite;
            padding-right: 5px;
            display: inline-block;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            bg: 'var(--brand-bg-color)',
                            btn: 'var(--brand-btn-color)',
                        },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans relative">
    <!-- Background Doodle Pattern -->
    <div class="fixed inset-0 z-0 pointer-events-none" style="background-image: url('<?= base_url('assets/doodle.png') ?>'); background-repeat: repeat; background-size: 400px auto; opacity: 1.15; background-attachment: fixed;"></div>

    <div class="min-h-screen flex flex-wrap relative z-10">
        <!-- Left Column -->
        <div class="hidden md:flex w-full md:w-1/2 lg:w-7/12 bg-gradient-to-br from-brand-bg to-gray-900 text-white items-center justify-center p-12 relative overflow-hidden">
            <!-- Doodle Overlay for Left Column -->
            <div class="absolute inset-0 pointer-events-none" style="background-image: url('<?= base_url('assets/doodle.png') ?>'); background-repeat: repeat; background-size: 400px auto; opacity: 0.2; background-attachment: fixed; mix-blend-mode: overlay;"></div>
            <div class="relative z-10">
                <h1 class="text-5xl font-bold mb-4"><?= get_setting('app_name', 'Aplikasi Keuangan') ?></h1>
                <p class="text-xl opacity-90 typing-cursor" id="typing-text"></p>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="w-full md:w-1/2 lg:w-5/12 flex flex-col items-center justify-center p-6 relative">
            <div class="flex-1 flex items-center justify-center w-full">
            <div class="bg-white/70 backdrop-blur-md shadow-xl rounded-2xl w-full max-w-md p-8 border border-white/50">
                <div class="text-center mb-8">
                    <img src="<?= base_url(get_setting('app_logo', 'assets/img/logo.png')) ?>" alt="Logo" class="h-12 mx-auto mb-4 hover:scale-110 transition-transform duration-300">
                    <h3 class="text-2xl font-semibold text-gray-800">Selamat Datang</h3>
                </div>
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['login_success'])): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <?= $_SESSION['login_success']; unset($_SESSION['login_success']); ?>
                            </div>
                        <?php endif; ?>
                        <form id="login-form" action="<?= base_url('/login') ?>" method="POST" class="space-y-5">
                            <div>
                                <input class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-btn focus:border-transparent transition duration-200" type="text" id="username" name="username" placeholder="Username" required autofocus>
                            </div>
                            <div class="relative">
                                <input class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-btn focus:border-transparent transition duration-200 pr-12" type="password" id="password" name="password" placeholder="Password" required>
                                <button class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none" type="button" id="togglePassword" title="Tampilkan/Sembunyikan password">
                                    <i class="bi bi-eye text-xl"></i>
                                </button>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-brand-btn focus:ring-brand-btn border-gray-300 rounded" type="checkbox" name="remember_me" id="remember_me">
                                    <label class="ml-2 block text-sm text-gray-700" for="remember_me"> Ingat Saya </label>
                                </div>
                                <a href="<?= base_url('/forgot') ?>" class="text-sm text-brand-btn hover:underline">Lupa Password?</a>
                            </div>
                            <div>
                                <button id="login-btn" class="w-full bg-brand-btn hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex justify-center items-center" type="submit">Login</button>
                            </div>
                        </form>
            </div>
            </div>
            <div class="w-full text-center py-4 z-10">
                <a href="https://crudworks.com" target="_blank" class="inline-block">
                    <img src="<?= base_url('assets/logo.png') ?>" alt="Logo" class="h-6 mx-auto mb-2 opacity-80 hover:opacity-100 hover:scale-110 transition-all duration-300">
                </a>
                <p class="text-sm text-gray-500 opacity-80">&copy; <?= date('Y') ?> All rights reserved.</p>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('login-form').addEventListener('submit', function() {
            const loginBtn = document.getElementById('login-btn');
            if (loginBtn) {
                loginBtn.disabled = true;
                loginBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg> Logging in...`;
            }
        });

        const togglePasswordBtn = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', function() {
                /* toggle the type attribute */
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                /* toggle the eye / eye-slash icon */
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }

        /* Apply user-defined theme color from localStorage */
        (function() {
            const savedColor = localStorage.getItem('theme_color');
            if (savedColor) {
                document.documentElement.style.setProperty('--brand-btn-color', savedColor);
            }
        })();

    // Efek Mengetik (Typing Effect)
    document.addEventListener('DOMContentLoaded', function() {
        const text = "Satu Platform Cerdas untuk Mengelola Seluruh Aspek Bisnis Anda.";
        const element = document.getElementById('typing-text');
        let i = 0;
        
        function typeWriter() {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 50); // Kecepatan mengetik (50ms)
            }
        }
        setTimeout(typeWriter, 500); // Mulai mengetik setelah 0.5 detik
    });
    </script>
</body>
</html>