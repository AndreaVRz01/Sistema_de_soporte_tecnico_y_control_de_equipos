<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Público de Equipos y Préstamos') | SIGA Enterprise</title>
    <meta name="description" content="@yield('meta_description', 'Portal institucional de consulta, catálogo y solicitud de préstamos de equipo técnico y cómputo.')">

    <!-- Tipografías Oficiales del Sistema: Space Grotesk, Hanken Grotesk, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300..900;1,300..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Carga de Estilos (Tailwind CSS CDN + Vite Fallback para desarrollo sin compilador previo) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#001a2b",
                        "primary-container": "#003049",
                        "on-primary": "#ffffff",
                        "on-primary-container": "#7398b6",
                        "inverse-primary": "#a5cbea",
                        secondary: "#835500",
                        "secondary-container": "#feb956",
                        "secondary-fixed": "#ffddb4",
                        "on-secondary": "#ffffff",
                        "on-secondary-container": "#734a00",
                        tertiary: "#675f2b",
                        "tertiary-container": "#b6ac6f",
                        surface: "#fff8f7",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#fff0ef",
                        "surface-container": "#ffe9e7",
                        "surface-container-high": "#ffe1df",
                        "on-surface": "#410004",
                        "on-surface-variant": "#42474d",
                        outline: "#72787e",
                        "outline-variant": "#c2c7cd",
                        error: "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-error": "#ffffff",
                        background: "#fff8f7"
                    },
                    fontFamily: {
                        headline: ["Space Grotesk", "sans-serif"],
                        body: ["Hanken Grotesk", "sans-serif"],
                        mono: ["JetBrains Mono", "monospace"]
                    }
                }
            }
        };
    </script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Hanken Grotesk', sans-serif;
            background-color: #fff8f7;
            color: #410004;
        }
        .dark body {
            background-color: #001a2b;
            color: #fff8f7;
        }
        .font-headline { font-family: 'Space Grotesk', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Neumorphic Tokens */
        .neu-raised {
            background: #fff8f7;
            box-shadow: -6px -6px 14px rgba(255, 255, 255, 0.95), 6px 6px 16px rgba(0, 48, 73, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }
        .dark .neu-raised {
            background: #001a2b;
            box-shadow: -4px -4px 12px rgba(255, 243, 176, 0.04), 6px 6px 18px rgba(0, 10, 18, 0.75);
            border: 1px solid rgba(224, 159, 62, 0.15);
        }

        .neu-inset {
            background: #fff0ef;
            box-shadow: inset 3px 3px 6px rgba(0, 48, 73, 0.08), inset -3px -3px 6px rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 48, 73, 0.06);
        }
        .dark .neu-inset {
            background: #001524;
            box-shadow: inset 3px 3px 6px rgba(0, 10, 18, 0.85), inset -2px -2px 5px rgba(255, 243, 176, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.6);
        }

        .neu-btn-primary {
            background: #003049;
            color: #ffffff;
            box-shadow: -3px -3px 8px rgba(255, 255, 255, 0.6), 4px 5px 12px rgba(0, 48, 73, 0.25);
            border: 1px solid rgba(255, 243, 176, 0.2);
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .neu-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: -4px -4px 12px rgba(255, 255, 255, 0.8), 6px 8px 16px rgba(0, 48, 73, 0.32);
        }
        .neu-btn-primary:active {
            transform: scale(0.98);
            box-shadow: inset 3px 3px 6px rgba(0, 10, 20, 0.6), inset -2px -2px 5px rgba(255, 243, 176, 0.2);
        }

        .neu-btn-secondary {
            background: #fff8f7;
            color: #001a2b;
            box-shadow: -4px -4px 10px rgba(255, 255, 255, 0.95), 4px 4px 10px rgba(0, 48, 73, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.7);
            transition: all 0.15s ease-in-out;
        }
        .neu-btn-secondary:active {
            transform: scale(0.98);
            box-shadow: inset 2px 2px 4px rgba(0, 48, 73, 0.12), inset -2px -2px 4px rgba(255, 255, 255, 0.8);
        }
        .dark .neu-btn-secondary {
            background: #002238;
            color: #cae6ff;
            box-shadow: -3px -3px 8px rgba(255, 243, 176, 0.04), 4px 4px 10px rgba(0, 10, 18, 0.6);
            border: 1px solid rgba(224, 159, 62, 0.15);
        }

        .neu-floating {
            box-shadow: 0 20px 35px -10px rgba(0, 48, 73, 0.18), 0 1px 4px rgba(0, 48, 73, 0.06);
        }
        .dark .neu-floating {
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.85);
            border: 1px solid rgba(224, 159, 62, 0.2);
        }

        @keyframes live-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.15); }
        }
        .animate-live-dot {
            animation: live-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-surface dark:bg-primary text-on-surface dark:text-surface antialiased transition-colors duration-200 min-h-screen flex flex-col">

    <!-- ========================================================================= -->
    <!-- HEADER PÚBLICO INSTITUCIONAL (TopNavBar)                                  -->
    <!-- ========================================================================= -->
    <header class="sticky top-0 z-40 w-full bg-surface dark:bg-primary border-b border-outline-variant/60 dark:border-primary-container shadow-xs transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Marca y Disparador Menú Móvil -->
            <div class="flex items-center gap-3">
                <button aria-label="Abrir menú de navegación" class="lg:hidden p-2 rounded-xl text-primary dark:text-inverse-primary hover:bg-surface-container dark:hover:bg-primary-container neu-raised transition-all active:scale-95 flex items-center justify-center cursor-pointer" id="public-drawer-toggle" type="button">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>

                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-primary-container text-secondary-container flex items-center justify-center font-headline font-bold neu-raised group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-2xl text-secondary-container" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-headline text-lg sm:text-xl font-bold text-primary dark:text-surface tracking-tight leading-none">SIGA Enterprise</span>
                        <span class="font-mono text-[10px] text-secondary dark:text-secondary-fixed uppercase tracking-wider font-semibold">Portal Público de Préstamos</span>
                    </div>
                </a>
            </div>

            <!-- Navegación de Escritorio -->
            <nav class="hidden lg:flex items-center space-x-6">
                <a href="{{ url('/') }}#catalogo" class="font-mono text-xs uppercase tracking-wider text-on-surface-variant dark:text-inverse-primary hover:text-primary dark:hover:text-surface font-semibold pb-1 transition-colors">
                    Catálogo de Equipos
                </a>
                <a href="{{ url('/') }}#como-funciona" class="font-mono text-xs uppercase tracking-wider text-on-surface-variant dark:text-inverse-primary hover:text-primary dark:hover:text-surface font-semibold pb-1 transition-colors">
                    Cómo Solicitar
                </a>
                <a href="{{ url('/') }}#horarios" class="font-mono text-xs uppercase tracking-wider text-on-surface-variant dark:text-inverse-primary hover:text-primary dark:hover:text-surface font-semibold pb-1 transition-colors">
                    Horarios de Entrega
                </a>
                <a href="{{ url('/') }}#terminos" class="font-mono text-xs uppercase tracking-wider text-on-surface-variant dark:text-inverse-primary hover:text-primary dark:hover:text-surface font-semibold pb-1 transition-colors">
                    Reglamento
                </a>
            </nav>

            <!-- Acciones de Cabecera: Modo Oscuro & Botón de Acceso Privado -->
            <div class="flex items-center gap-3">
                <!-- Conmutador Tema Claro/Oscuro -->
                <button aria-label="Cambiar tema visual" class="p-2 w-10 h-10 rounded-xl text-primary dark:text-secondary-container neu-raised flex items-center justify-center cursor-pointer transition-transform active:scale-95" id="theme-toggle-btn" type="button" title="Cambiar tema">
                    <span class="material-symbols-outlined text-xl" id="theme-toggle-icon">dark_mode</span>
                </button>

                <!-- Acceso a la Consola Privada -->
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 neu-btn-primary px-4 py-2 rounded-xl font-mono text-xs font-bold tracking-wider uppercase transition-all">
                            <span class="material-symbols-outlined text-base">dashboard</span>
                            <span class="hidden sm:inline">Ir al Panel</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 neu-btn-primary px-4 py-2 rounded-xl font-mono text-xs font-bold tracking-wider uppercase transition-all">
                            <span class="material-symbols-outlined text-base">lock_open</span>
                            <span>Acceso Personal</span>
                        </a>
                    @endauth
                @else
                    <a href="{{ url('/login') }}" class="inline-flex items-center gap-2 neu-btn-primary px-4 py-2 rounded-xl font-mono text-xs font-bold tracking-wider uppercase transition-all">
                        <span class="material-symbols-outlined text-base">lock_open</span>
                        <span>Acceso Personal</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- DRAWER LATERAL OFFCANVAS (Móvil y Navegación Rápida)                       -->
    <!-- ========================================================================= -->
    <div class="fixed inset-0 z-50 bg-primary/40 backdrop-blur-xs hidden transition-opacity" id="drawer-backdrop"></div>
    <aside aria-label="Menú principal de navegación" class="fixed inset-y-0 left-0 z-50 w-72 sm:w-80 bg-surface dark:bg-primary-container border-r border-outline-variant/60 dark:border-primary shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col justify-between p-5 overflow-y-auto" id="public-side-drawer">
        <div>
            <!-- Cabecera del Drawer -->
            <div class="flex items-center justify-between pb-4 border-b border-outline-variant/30 dark:border-primary">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary text-secondary-container flex items-center justify-center neu-raised">
                        <span class="material-symbols-outlined text-2xl">hub</span>
                    </div>
                    <div>
                        <h2 class="font-headline text-base font-bold text-primary dark:text-surface leading-tight">SIGA Enterprise</h2>
                        <p class="font-mono text-[11px] text-on-surface-variant dark:text-inverse-primary">Catálogo & Préstamos</p>
                    </div>
                </div>
                <button aria-label="Cerrar panel lateral" class="p-2 rounded-xl text-outline hover:text-primary dark:hover:text-surface cursor-pointer" id="public-drawer-close" type="button">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Navegación móvil -->
            <nav class="mt-6 flex flex-col gap-2">
                <a href="{{ url('/') }}" class="text-primary dark:text-surface bg-surface-container dark:bg-primary font-bold rounded-xl px-4 py-3 flex items-center gap-3 neu-raised transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-secondary text-xl">home</span>
                    <span class="font-mono text-xs uppercase tracking-wider">Inicio</span>
                </a>
                <a href="{{ url('/') }}#catalogo" class="text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container dark:hover:bg-primary/50 font-medium rounded-xl px-4 py-3 flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined text-secondary text-xl">inventory_2</span>
                    <span class="font-mono text-xs uppercase tracking-wider">Catálogo Público</span>
                </a>
                <a href="{{ url('/') }}#como-funciona" class="text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container dark:hover:bg-primary/50 font-medium rounded-xl px-4 py-3 flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined text-secondary text-xl">help</span>
                    <span class="font-mono text-xs uppercase tracking-wider">Cómo Solicitar</span>
                </a>
                <a href="{{ url('/') }}#horarios" class="text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container dark:hover:bg-primary/50 font-medium rounded-xl px-4 py-3 flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined text-secondary text-xl">schedule</span>
                    <span class="font-mono text-xs uppercase tracking-wider">Horarios de Entrega</span>
                </a>
                <a href="{{ url('/') }}#terminos" class="text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container dark:hover:bg-primary/50 font-medium rounded-xl px-4 py-3 flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined text-secondary text-xl">policy</span>
                    <span class="font-mono text-xs uppercase tracking-wider">Políticas de Resguardo</span>
                </a>
            </nav>

            <!-- Tarjeta de Mesa de Ayuda en el Drawer -->
            <div class="mt-8 p-4 rounded-2xl bg-surface-container-low dark:bg-primary neu-inset border border-outline-variant/30">
                <div class="flex items-center gap-2 mb-2 text-secondary font-mono text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-secondary inline-block animate-live-dot"></span>
                    <span>Soporte Técnico</span>
                </div>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mb-3">
                    ¿Tienes dudas con un equipo o necesitas un préstamo especial para laboratorio?
                </p>
                <div class="font-mono text-xs text-primary dark:text-surface font-semibold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base text-secondary">call</span>
                    <span>Ext. 4022 / Mesa Técnica</span>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-outline-variant/20">
            <p class="font-mono text-[10px] text-outline text-center uppercase tracking-widest">
                SIGA v4.2 • Tactile Design System
            </p>
        </div>
    </aside>

    <!-- ========================================================================= -->
    <!-- CONTENIDO PRINCIPAL                                                       -->
    <!-- ========================================================================= -->
    <main class="flex-1 w-full">
        @yield('content')
    </main>

    <!-- ========================================================================= -->
    <!-- FOOTER INSTITUCIONAL PÚBLICO                                              -->
    <!-- ========================================================================= -->
    <footer class="bg-surface dark:bg-primary border-t border-outline-variant/60 dark:border-primary-container mt-16 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Columna 1: Identidad -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary-container text-secondary-container flex items-center justify-center neu-raised">
                            <span class="material-symbols-outlined text-xl">inventory_2</span>
                        </div>
                        <span class="font-headline text-lg font-bold text-primary dark:text-surface">SIGA Enterprise</span>
                    </div>
                    <p class="font-body text-sm text-on-surface-variant dark:text-inverse-primary max-w-md">
                        Sistema Institucional de Gestión de Activos, Control de Préstamos y Trazabilidad Operativa para Laboratorios y Departamentos Técnicos.
                    </p>
                    <div class="flex items-center gap-2 pt-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full neu-inset text-[11px] font-mono text-secondary font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-live-dot"></span>
                            Sistema Operativo En Línea
                        </span>
                    </div>
                </div>

                <!-- Columna 2: Enlaces Rápidos -->
                <div>
                    <h3 class="font-mono text-xs uppercase tracking-wider text-secondary font-bold mb-3">Navegación</h3>
                    <ul class="space-y-2 font-body text-sm text-on-surface-variant dark:text-inverse-primary">
                        <li><a href="{{ url('/') }}#catalogo" class="hover:text-primary dark:hover:text-surface transition-colors">Catálogo Completo</a></li>
                        <li><a href="{{ url('/') }}#como-funciona" class="hover:text-primary dark:hover:text-surface transition-colors">Proceso de Préstamo</a></li>
                        <li><a href="{{ url('/') }}#horarios" class="hover:text-primary dark:hover:text-surface transition-colors">Ventanilla de Recepción</a></li>
                        <li><a href="{{ url('/') }}#terminos" class="hover:text-primary dark:hover:text-surface transition-colors">Responsivas y Firmas</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Mesa de Ayuda -->
                <div>
                    <h3 class="font-mono text-xs uppercase tracking-wider text-secondary font-bold mb-3">Mantenimiento</h3>
                    <ul class="space-y-2 font-body text-sm text-on-surface-variant dark:text-inverse-primary">
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-secondary">location_on</span>
                            <span>Edificio Central, Módulo B</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-secondary">schedule</span>
                            <span>Lun - Vie: 07:00 a 20:00 hrs</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-secondary">mail</span>
                            <span>soporte.activos@institucion.edu</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Barra Inferior de Copyright -->
            <div class="pt-6 border-t border-outline-variant/30 flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-xs text-outline">
                <p>&copy; {{ date('Y') }} SIGA Enterprise. Todos los derechos reservados.</p>
                <div class="flex items-center gap-4">
                    <a href="#terminos" class="hover:text-primary transition-colors">Aviso de Privacidad</a>
                    <span>•</span>
                    <a href="#terminos" class="hover:text-primary transition-colors">Términos de Resguardo</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modales Globales -->
    @yield('modals')

    <!-- ========================================================================= -->
    <!-- SCRIPTS BASE: MODO OSCURO Y DRAWER OFFCANVAS                               -->
    <!-- ========================================================================= -->
    <script>
        // Manejo de Tema Claro / Oscuro con persistencia en localStorage
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        const themeToggleIcon = document.getElementById('theme-toggle-icon');

        function applyTheme(isDark) {
            if (isDark) {
                document.documentElement.classList.add('dark');
                if (themeToggleIcon) themeToggleIcon.textContent = 'light_mode';
            } else {
                document.documentElement.classList.remove('dark');
                if (themeToggleIcon) themeToggleIcon.textContent = 'dark_mode';
            }
        }

        // Inicialización del tema
        const savedTheme = localStorage.getItem('siga_theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            applyTheme(true);
        } else {
            applyTheme(false);
        }

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                const isCurrentlyDark = document.documentElement.classList.contains('dark');
                const newTheme = !isCurrentlyDark;
                applyTheme(newTheme);
                localStorage.setItem('siga_theme', newTheme ? 'dark' : 'light');
            });
        }

        // Control del Drawer Móvil
        const drawerToggle = document.getElementById('public-drawer-toggle');
        const drawerClose = document.getElementById('public-drawer-close');
        const drawer = document.getElementById('public-side-drawer');
        const backdrop = document.getElementById('drawer-backdrop');

        function openDrawer() {
            if (!drawer || !backdrop) return;
            drawer.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        }

        function closeDrawer() {
            if (!drawer || !backdrop) return;
            drawer.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }

        if (drawerToggle) drawerToggle.addEventListener('click', openDrawer);
        if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
        if (backdrop) backdrop.addEventListener('click', closeDrawer);

        // Cerrar con Escape
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDrawer();
        });
    </script>

    @stack('scripts')
</body>
</html>
