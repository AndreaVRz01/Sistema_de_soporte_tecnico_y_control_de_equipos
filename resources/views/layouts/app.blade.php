<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Consola de Operaciones') | SIGA Enterprise</title>

    <!-- Tipografías Oficiales del Sistema: Space Grotesk, Hanken Grotesk, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300..900;1,300..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Carga de Tailwind CSS (CDN con plugins + integración Vite) -->
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
                        "primary-fixed": "#cae6ff",
                        "primary-fixed-dim": "#a5cbea",
                        secondary: "#835500",
                        "secondary-container": "#feb956",
                        "secondary-fixed": "#ffddb4",
                        "secondary-fixed-dim": "#feb956",
                        "on-secondary": "#ffffff",
                        "on-secondary-container": "#734a00",
                        tertiary: "#675f2b",
                        "tertiary-container": "#b6ac6f",
                        surface: "#fff8f7",
                        "surface-dim": "#ffcfcb",
                        "surface-bright": "#fff8f7",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#fff0ef",
                        "surface-container": "#ffe9e7",
                        "surface-container-high": "#ffe1df",
                        "surface-container-highest": "#ffdad7",
                        "on-surface": "#410004",
                        "on-surface-variant": "#42474d",
                        outline: "#72787e",
                        "outline-variant": "#c2c7cd",
                        error: "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
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
        .dark body { color: #eef4f7; }
        .dark .text-primary:not([class~="dark:text-surface"]):not([class~="dark:text-primary"]):not([class~="dark:text-primary-fixed"]):not([class~="dark:text-inverse-primary"]):not([class~="bg-surface-container"]):not([class~="bg-surface-container-low"]):not([class~="bg-secondary-container"]):not([class~="bg-error-container"]):not([class~="bg-amber-50"]):not([class~="bg-blue-50"]):not([class~="bg-emerald-50"]):not([class~="bg-gray-100"]):not(.neu-btn-primary):not(.neu-btn-secondary) { color: #e8f1f5 !important; }
        .dark .text-on-surface:not([class~="bg-surface-container"]):not([class~="bg-surface-container-low"]):not([class~="bg-secondary-container"]):not([class~="bg-error-container"]) { color: #e8f1f5 !important; }
        .dark .text-on-surface-variant:not([class~="dark:text-inverse-primary"]):not([class~="bg-surface-container"]):not([class~="bg-surface-container-low"]):not([class~="bg-error-container"]) { color: #c9d5dc !important; }
        .dark .text-outline { color: #a9bac5 !important; }
        .dark .text-secondary:not([class~="dark:text-secondary-fixed"]):not([class~="bg-secondary-container"]):not([class~="bg-amber-50"]):not(.neu-btn-secondary) { color: #ffd27b !important; }
        .dark input::placeholder, .dark textarea::placeholder { color: #a9bac5 !important; opacity: 1; }
        .dark select, .dark input, .dark textarea { color-scheme: dark; }
        .font-headline { font-family: 'Space Grotesk', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Neumorphic Tokens basados en el Design System Tactile */
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

        .neu-btn {
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -4px -4px 10px rgba(255, 255, 255, 0.95), 4px 4px 12px rgba(0, 48, 73, 0.1);
        }
        .neu-btn:active {
            transform: scale(0.98);
            box-shadow: inset 3px 3px 6px rgba(0, 48, 73, 0.18), inset -3px -3px 6px rgba(255, 255, 255, 0.8);
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

        .neu-card-interactive {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .neu-card-interactive:hover {
            transform: translateY(-2px);
            box-shadow: -8px -8px 18px rgba(255, 255, 255, 1), 8px 8px 20px rgba(0, 48, 73, 0.12);
        }

        @keyframes live-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.15); }
        }
        .animate-live-dot {
            animation: live-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #c2c7cd;
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #003049;
        }

    </style>

    @stack('styles')
</head>
<body class="bg-surface dark:bg-primary text-on-surface dark:text-surface antialiased transition-colors duration-200 min-h-screen flex flex-col font-body">

    <!-- ========================================================================= -->
    <!-- TOP APP BAR INSTITUCIONAL (Barra Superior Fija)                           -->
    <!-- ========================================================================= -->
    <header id="private-topbar" class="sticky top-0 z-40 w-full bg-surface dark:bg-primary border-b border-outline-variant/60 dark:border-primary-container shadow-xs transition-colors duration-200 min-h-16 lg:ml-72 lg:w-[calc(100%-18rem)]">
        <div class="w-full px-4 sm:px-6 lg:px-8 min-h-16 flex items-center justify-between">
            
            <!-- Identidad institucional -->
            <div class="flex items-center gap-3">
                <button aria-label="Cerrar menú de navegación" aria-controls="private-side-drawer" aria-expanded="true" class="fixed left-4 top-3 z-[60] p-2 rounded-xl text-primary dark:text-inverse-primary hover:bg-surface-container neu-raised" id="private-drawer-toggle" type="button" title="Mostrar u ocultar menú">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <a href="{{ Auth::user()->role === 'tecnico' ? route('incidents.index') : route('dashboard') }}" class="flex flex-col pl-12">
                    <span class="font-headline text-lg sm:text-xl font-bold text-primary dark:text-surface tracking-tight leading-none">SIGA Enterprise</span>
                    <span class="font-mono text-[10px] text-secondary dark:text-secondary-fixed uppercase tracking-wider font-semibold">Consola Operativa Institucional</span>
                </a>
            </div>

            <!-- Navegación horizontal por rol -->
            @php
                $roleNavigation = match (Auth::user()->role) {
                    'admin' => [
                        ['label' => 'Panel', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
                        ['label' => 'Activos', 'route' => 'equipment.index', 'active' => 'equipment.*', 'icon' => 'inventory_2'],
                        ['label' => 'Préstamos', 'route' => 'loans.index', 'active' => 'loans.*', 'icon' => 'handshake'],
                        ['label' => 'Tickets', 'route' => 'incidents.index', 'active' => 'incidents.*', 'icon' => 'build'],
                        ['label' => 'Usuarios', 'route' => 'users.index', 'active' => 'users.*', 'icon' => 'manage_accounts'],
                        ['label' => 'Bitácora', 'route' => 'audit.index', 'active' => 'audit.*', 'icon' => 'verified_user'],
                        ['label' => 'Sanciones', 'route' => 'sanctions.index', 'active' => 'sanctions.*', 'icon' => 'gavel'],
                    ],
                    'supervisor' => [
                        ['label' => 'Panel', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
                        ['label' => 'Activos', 'route' => 'equipment.index', 'active' => 'equipment.*', 'icon' => 'inventory_2'],
                        ['label' => 'Préstamos', 'route' => 'loans.index', 'active' => 'loans.*', 'icon' => 'handshake'],
                        ['label' => 'Tickets', 'route' => 'incidents.index', 'active' => 'incidents.*', 'icon' => 'build'],
                        ['label' => 'Bitácora', 'route' => 'audit.index', 'active' => 'audit.*', 'icon' => 'verified_user'],
                        ['label' => 'Sanciones', 'route' => 'sanctions.index', 'active' => 'sanctions.*', 'icon' => 'gavel'],
                    ],
                    'encargado_activos' => [
                        ['label' => 'Panel', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
                        ['label' => 'Activos', 'route' => 'equipment.index', 'active' => 'equipment.*', 'icon' => 'inventory_2'],
                        ['label' => 'Tickets', 'route' => 'incidents.index', 'active' => 'incidents.*', 'icon' => 'build'],
                        ['label' => 'Sanciones', 'route' => 'sanctions.index', 'active' => 'sanctions.*', 'icon' => 'gavel'],
                    ],
                    'encargado_prestamos' => [
                        ['label' => 'Panel', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
                        ['label' => 'Préstamos', 'route' => 'loans.index', 'active' => 'loans.*', 'icon' => 'handshake'],
                    ],
                    'tecnico' => [
                        ['label' => 'Tickets', 'route' => 'incidents.index', 'active' => 'incidents.*', 'icon' => 'build'],
                    ],
                    'usuario' => [
                        ['label' => 'Mi panel', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
                        ['label' => 'Catálogo', 'route' => 'public.catalog', 'active' => 'public.*', 'icon' => 'inventory_2'],
                        ['label' => 'Mi bitácora', 'route' => 'portal.activity', 'active' => 'portal.*', 'icon' => 'history'],
                    ],
                    default => [],
                };
            @endphp
            <!-- Acciones de sesión -->
            <div class="ml-auto flex shrink-0 items-center gap-3">
                <!-- Botón Acción Rápida (Ej: Registrar) -->
                @yield('topbar_action')

                <!-- Botón Conmutador de Tema -->
                <button aria-label="Cambiar tema visual" class="w-9 h-9 rounded-xl neu-raised flex items-center justify-center text-primary dark:text-secondary-container neu-btn cursor-pointer" id="private-theme-toggle" type="button" title="Alternar tema">
                    <span class="material-symbols-outlined text-lg" id="private-theme-icon">dark_mode</span>
                </button>

                <!-- Notificaciones con Alerta -->
                <div class="relative">
                    <button aria-label="Notificaciones del sistema" class="w-9 h-9 rounded-xl neu-raised flex items-center justify-center text-primary dark:text-surface neu-btn cursor-pointer">
                        <span class="material-symbols-outlined text-lg">notifications</span>
                    </button>
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-error ring-2 ring-surface"></span>
                </div>

                <!-- Perfil y Menú de Usuario -->
                <div class="relative pl-2 border-l border-outline-variant/30">
                    <button class="flex items-center gap-2 cursor-pointer group" id="user-profile-menu-btn" type="button">
                        <span class="hidden max-w-32 text-right sm:block">
                            <span class="block truncate font-body text-xs font-semibold text-primary dark:text-surface">{{ Auth::user()->name ?? 'Usuario' }}</span>
                            <span class="block font-mono text-[10px] text-outline">Mi cuenta</span>
                        </span>
                        <div class="w-9 h-9 rounded-xl bg-primary-container text-secondary-container flex items-center justify-center font-headline font-bold text-sm ring-2 ring-secondary/40 neu-raised">
                            {{ substr(Auth::user()->name ?? 'Admin', 0, 1) }}
                        </div>
                    </button>
                    <!-- Menú de Usuario -->
                    <div class="hidden absolute right-0 top-12 w-56 rounded-2xl bg-surface dark:bg-primary neu-floating p-3 z-50 border border-outline-variant/30" id="user-profile-dropdown">
                        <div class="pb-2 border-b border-outline-variant/20 mb-2">
                            <p class="font-headline text-xs font-bold text-primary dark:text-surface">{{ Auth::user()->name ?? 'Administrador General' }}</p>
                            <p class="font-mono text-[11px] text-outline truncate">{{ Auth::user()->email ?? 'admin@institucion.edu' }}</p>
                        </div>
                        <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg font-body text-xs text-on-surface-variant dark:text-surface hover:bg-surface-container dark:hover:bg-primary-container transition-colors">
                            <span class="material-symbols-outlined text-base text-secondary">public</span>
                            <span>Ver Portal Público</span>
                        </a>
                        <div class="pt-2 border-t border-outline-variant/20 mt-2">
                            @if (Route::has('logout'))
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg font-body text-xs text-error hover:bg-error-container/40 transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-base">logout</span>
                                        <span>Cerrar Sesión</span>
                                    </button>
                                </form>
                            @else
                                <a href="{{ url('/logout') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg font-body text-xs text-error hover:bg-error-container/40 transition-colors">
                                    <span class="material-symbols-outlined text-base">logout</span>
                                    <span>Cerrar Sesión</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="fixed inset-0 z-40 hidden bg-primary/45 backdrop-blur-sm lg:hidden" id="private-drawer-backdrop"></div>
    <aside aria-label="Menú principal de navegación" aria-hidden="true" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-outline-variant/50 bg-surface p-5 shadow-2xl transition-transform duration-300 dark:border-primary-container dark:bg-primary-container lg:translate-x-0" id="private-side-drawer">
        <div class="flex items-center justify-between border-b border-outline-variant/40 pb-5 dark:border-primary">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-container text-secondary-container dark:bg-primary">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
                <div>
                    <p class="font-headline text-sm font-bold text-primary dark:text-surface">SIGA Enterprise</p>
                    <p class="font-mono text-[10px] uppercase tracking-wider text-secondary">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button aria-label="Fijar menú" aria-pressed="false" class="rounded-lg p-2 text-outline hover:bg-surface-container hover:text-secondary" id="private-drawer-pin" type="button" title="Fijar menú">
                    <span class="material-symbols-outlined">push_pin</span>
                </button>
                <button aria-label="Cerrar menú de navegación" class="rounded-lg p-2 text-outline hover:bg-surface-container lg:hidden" id="private-drawer-close" type="button">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        <nav aria-label="Secciones del sistema" class="mt-7 flex-1 space-y-2 overflow-y-auto">
            @foreach ($roleNavigation as $item)
                <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-xl border px-3 py-3 transition-colors {{ request()->routeIs($item['active']) ? 'border-secondary/50 bg-surface-container text-primary dark:bg-primary dark:text-surface' : 'border-transparent text-on-surface-variant hover:border-outline-variant/50 hover:bg-surface-container/70 dark:text-inverse-primary dark:hover:bg-primary/60' }}">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-surface-container-low text-secondary dark:bg-primary-container">
                        <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="mt-0.5 block truncate font-body text-sm font-semibold">{{ $item['label'] }}</span>
                    </span>
                    <span class="material-symbols-outlined text-base text-outline">chevron_right</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-5 border-t border-outline-variant/40 pt-4 dark:border-primary">
            <p class="font-mono text-[10px] uppercase tracking-widest text-outline">Consola Operativa</p>
            <p class="mt-1 truncate font-body text-xs font-semibold text-primary dark:text-surface">{{ Auth::user()->name }}</p>
        </div>
    </aside>

    <main id="private-main" class="ml-0 w-full flex-1 overflow-y-auto p-4 sm:p-6 lg:ml-72 lg:w-[calc(100%-18rem)] lg:p-8">
            
            <!-- Encabezado de Página (Si se define en la vista) -->
            @hasSection('header')
                <div class="mb-6 pb-4 border-b border-outline-variant/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    @yield('header')
                </div>
            @endif

            <!-- Notificaciones Flash del Sistema (Alertas) -->
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 neu-raised flex items-center gap-3 shadow-xs">
                    <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                    <p class="font-body text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container neu-raised flex items-center gap-3 shadow-xs">
                    <span class="material-symbols-outlined text-error text-xl">error</span>
                    <p class="font-body text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container neu-raised shadow-xs">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <span class="material-symbols-outlined text-error text-xl">warning</span>
                        <span class="font-body text-sm">Por favor verifique los siguientes errores:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs font-body space-y-1 ml-6">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Contenido Inyectado de la Vista -->
            @yield('content')
    </main>

    <!-- Modales Globales -->
    @yield('modals')

    <!-- ========================================================================= -->
    <!-- SCRIPTS BASE: TEMA Y PERFIL                                              -->
    <!-- ========================================================================= -->
    <script>
        // Manejo de Tema Claro/Oscuro
        const privateThemeToggle = document.getElementById('private-theme-toggle');
        const privateThemeIcon = document.getElementById('private-theme-icon');

        function applyPrivateTheme(isDark) {
            if (isDark) {
                document.documentElement.classList.add('dark');
                if (privateThemeIcon) privateThemeIcon.textContent = 'light_mode';
            } else {
                document.documentElement.classList.remove('dark');
                if (privateThemeIcon) privateThemeIcon.textContent = 'dark_mode';
            }
        }

        const savedPrivateTheme = localStorage.getItem('siga_theme') || localStorage.getItem('theme');
        if (savedPrivateTheme) localStorage.setItem('siga_theme', savedPrivateTheme);
        if (savedPrivateTheme === 'dark' || (!savedPrivateTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            applyPrivateTheme(true);
        } else {
            applyPrivateTheme(false);
        }

        if (privateThemeToggle) {
            privateThemeToggle.addEventListener('click', () => {
                const isCurrentlyDark = document.documentElement.classList.contains('dark');
                const newTheme = !isCurrentlyDark;
                applyPrivateTheme(newTheme);
                localStorage.setItem('siga_theme', newTheme ? 'dark' : 'light');
            });
        }

        const privateDrawerToggle = document.getElementById('private-drawer-toggle');
        const privateDrawerClose = document.getElementById('private-drawer-close');
        const privateDrawerPin = document.getElementById('private-drawer-pin');
        const privateDrawer = document.getElementById('private-side-drawer');
        const privateDrawerBackdrop = document.getElementById('private-drawer-backdrop');
        const privateTopbar = document.getElementById('private-topbar');
        const privateMain = document.getElementById('private-main');
        const privateDesktopMenu = window.matchMedia('(min-width: 1024px)');
        const privatePinnedMenuKey = 'siga_private_menu_pinned';
        const privateOpenMenuKey = 'siga_private_menu_open';
        let privateMenuPinned = localStorage.getItem(privatePinnedMenuKey) === 'true';

        function setPrivateDrawerOpen(isOpen, persist = true) {
            if (!privateDrawer || !privateDrawerBackdrop) return;
            privateDrawer.classList.toggle('-translate-x-full', !isOpen);
            privateDrawer.style.translate = isOpen ? '0 0' : '-100% 0';
            privateDrawer.setAttribute('aria-hidden', String(!isOpen));
            privateDrawerToggle?.setAttribute('aria-expanded', String(isOpen));
            privateDrawerToggle?.setAttribute('aria-label', isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
            if (privateDesktopMenu.matches) {
                document.body.classList.toggle('private-menu-collapsed', !isOpen);
                [privateTopbar, privateMain].forEach((element) => {
                    if (!element) return;
                    element.style.marginLeft = isOpen ? '18rem' : '0';
                    element.style.width = isOpen ? 'calc(100% - 18rem)' : '100%';
                });
                privateDrawerBackdrop.classList.add('hidden');
                document.body.style.overflow = '';
            } else {
                [privateTopbar, privateMain].forEach((element) => {
                    if (!element) return;
                    element.style.marginLeft = '';
                    element.style.width = '';
                });
                privateDrawerBackdrop.classList.toggle('hidden', !isOpen);
                document.body.style.overflow = isOpen ? 'hidden' : '';
            }
            if (persist) localStorage.setItem(privateOpenMenuKey, String(isOpen));
        }

        function isPrivateDrawerOpen() {
            return privateDesktopMenu.matches
                ? !document.body.classList.contains('private-menu-collapsed')
                : !privateDrawer.classList.contains('-translate-x-full');
        }

        function updatePrivatePinButton() {
            privateDrawerPin?.setAttribute('aria-pressed', String(privateMenuPinned));
            privateDrawerPin?.setAttribute('aria-label', privateMenuPinned ? 'Quitar fijado al menú' : 'Fijar menú');
            privateDrawerPin?.setAttribute('title', privateMenuPinned ? 'Quitar fijado al menú' : 'Fijar menú');
            privateDrawerPin?.classList.toggle('text-secondary', privateMenuPinned);
        }

        const savedPrivateMenuOpen = localStorage.getItem(privateOpenMenuKey);
        setPrivateDrawerOpen(privateDesktopMenu.matches && (privateMenuPinned || savedPrivateMenuOpen !== 'false'), false);
        updatePrivatePinButton();

        if (privateDrawerToggle) privateDrawerToggle.addEventListener('click', () => setPrivateDrawerOpen(!isPrivateDrawerOpen()));
        if (privateDrawerClose) privateDrawerClose.addEventListener('click', () => setPrivateDrawerOpen(false));
        if (privateDrawerBackdrop) privateDrawerBackdrop.addEventListener('click', () => setPrivateDrawerOpen(false));
        if (privateDrawerPin) privateDrawerPin.addEventListener('click', () => {
            privateMenuPinned = !privateMenuPinned;
            localStorage.setItem(privatePinnedMenuKey, String(privateMenuPinned));
            updatePrivatePinButton();
            if (privateMenuPinned) setPrivateDrawerOpen(true);
        });
        if (privateDrawer) privateDrawer.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
            if (!privateDesktopMenu.matches || !privateMenuPinned) setPrivateDrawerOpen(false);
        }));
        window.addEventListener('resize', () => {
            if (privateDesktopMenu.matches) setPrivateDrawerOpen(privateMenuPinned || localStorage.getItem(privateOpenMenuKey) !== 'false', false);
            else setPrivateDrawerOpen(false, false);
        });
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isPrivateDrawerOpen()) setPrivateDrawerOpen(false);
        });

        // Menú desplegable de perfil de usuario
        const userBtn = document.getElementById('user-profile-menu-btn');
        const userDropdown = document.getElementById('user-profile-dropdown');
        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
        }

        // Cerrar dropdowns al hacer clic fuera
        window.addEventListener('click', (e) => {
            if (userDropdown && !userDropdown.contains(e.target) && userBtn && !userBtn.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
