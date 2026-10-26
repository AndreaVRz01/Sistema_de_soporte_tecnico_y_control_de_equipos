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
    <header class="sticky top-0 z-40 w-full bg-surface dark:bg-primary border-b border-outline-variant/60 dark:border-primary-container shadow-xs transition-colors duration-200 h-16">
        <div class="w-full px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Izquierda: Toggle de Sidebar & Identidad -->
            <div class="flex items-center gap-3">
                <button aria-label="Alternar navegación lateral" class="w-10 h-10 rounded-xl neu-raised flex items-center justify-center text-primary dark:text-surface neu-btn active:scale-95 cursor-pointer" id="sidebar-toggle-btn" type="button">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                
                <a href="{{ url('/dashboard') }}" class="flex flex-col">
                    <span class="font-headline text-lg sm:text-xl font-bold text-primary dark:text-surface tracking-tight leading-none">SIGA Enterprise</span>
                    <span class="font-mono text-[10px] text-secondary dark:text-secondary-fixed uppercase tracking-wider font-semibold">Consola Operativa Institucional</span>
                </a>
            </div>

            <!-- Centro: Buscador Predictivo Global Inteligente (Ctrl + K) -->
            <div class="relative hidden lg:flex items-center flex-1 max-w-xl mx-8">
                <div class="w-full neu-inset rounded-xl flex items-center px-4 py-1.5 focus-within:ring-2 focus-within:ring-secondary/50 transition-all">
                    <span class="material-symbols-outlined text-secondary mr-2 text-xl">search</span>
                    <input 
                        type="text" 
                        id="global-search-input" 
                        class="w-full bg-transparent border-0 font-body text-xs sm:text-sm text-on-surface dark:text-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0" 
                        placeholder="Buscar por serie (SN-...), ticket (#TK-...), técnico o activo..."
                    >
                    <div class="flex items-center gap-1 ml-2">
                        <kbd class="px-2 py-0.5 rounded neu-raised font-mono text-[11px] text-secondary bg-surface-container dark:bg-primary-container border border-outline-variant/30">
                            Ctrl + K
                        </kbd>
                    </div>
                </div>

                <!-- Dropdown de Sugerencias Predictivas -->
                <div class="hidden absolute top-12 left-0 right-0 rounded-2xl bg-surface dark:bg-primary neu-floating p-4 z-50 border border-outline-variant/40 mt-1" id="predictive-search-dropdown">
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/20 mb-2">
                        <span class="font-mono text-[11px] text-secondary uppercase font-bold tracking-wider">Coincidencias Recientes</span>
                        <span class="font-mono text-[11px] text-outline">ESC para salir</span>
                    </div>
                    <div class="space-y-1">
                        <!-- Activo Sugerido -->
                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-surface-container dark:hover:bg-primary-container cursor-pointer transition-colors">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-secondary text-lg">laptop_mac</span>
                                <div>
                                    <p class="font-headline text-xs font-semibold text-primary dark:text-surface">MacBook Pro M2 Max 32GB</p>
                                    <p class="font-mono text-[11px] text-outline">Lab Multimedia • Estante 4</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full font-mono text-[11px] bg-surface-container-high dark:bg-primary-container text-primary dark:text-surface">
                                SN-8921-MBP
                            </span>
                        </div>
                        <!-- Ticket Sugerido -->
                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-surface-container dark:hover:bg-primary-container cursor-pointer transition-colors">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-error text-lg">build</span>
                                <div>
                                    <p class="font-headline text-xs font-semibold text-primary dark:text-surface">TK-8942: Falla de Óptica y Calibración</p>
                                    <p class="font-mono text-[11px] text-outline">Prioridad Crítica • Asignado a Ing. Valenzuela</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full font-mono text-[11px] bg-error-container text-on-error-container font-semibold">
                                En Reparación
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derecha: Selector de Rol, Notificaciones, Modo Oscuro y Perfil -->
            <div class="flex items-center gap-3">
                <!-- Botón Acción Rápida (Ej: Registrar) -->
                @yield('topbar_action')

                <!-- Selector de Rol Activo -->
                <div class="relative hidden sm:block">
                    <button class="flex items-center gap-2 neu-inset px-3 py-1.5 rounded-xl text-xs hover:border-secondary transition-all cursor-pointer" id="role-menu-btn" type="button">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-live-dot"></span>
                        <div class="text-left">
                            <span class="block font-mono text-[10px] text-outline leading-none">Perfil</span>
                            <span class="font-mono text-xs font-bold text-primary dark:text-surface" id="current-role-label">Administrador</span>
                        </div>
                        <span class="material-symbols-outlined text-outline text-base">expand_more</span>
                    </button>
                    <!-- Menú de roles -->
                    <div class="hidden absolute right-0 top-12 w-52 rounded-2xl bg-surface dark:bg-primary neu-floating p-2 z-50 border border-outline-variant/30" id="role-menu-dropdown">
                        <div class="font-mono text-[10px] text-outline px-3 py-1 font-bold uppercase tracking-wider">Cambiar Contexto</div>
                        <button class="w-full text-left px-3 py-1.5 rounded-lg font-body text-xs hover:bg-surface-container dark:hover:bg-primary-container flex items-center justify-between" onclick="switchRole('Administrador')">
                            <span>Administrador</span>
                            <span class="material-symbols-outlined text-secondary text-sm">check</span>
                        </button>
                        <button class="w-full text-left px-3 py-1.5 rounded-lg font-body text-xs hover:bg-surface-container dark:hover:bg-primary-container flex items-center justify-between" onclick="switchRole('Encargado de Activos')">
                            <span>Encargado de Activos</span>
                        </button>
                        <button class="w-full text-left px-3 py-1.5 rounded-lg font-body text-xs hover:bg-surface-container dark:hover:bg-primary-container flex items-center justify-between" onclick="switchRole('Técnico de Soporte')">
                            <span>Técnico de Soporte</span>
                        </button>
                    </div>
                </div>

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
                        <a href="{{ url('/perfil') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg font-body text-xs text-on-surface-variant dark:text-surface hover:bg-surface-container dark:hover:bg-primary-container transition-colors">
                            <span class="material-symbols-outlined text-base text-secondary">person</span>
                            <span>Mi Perfil y Seguridad</span>
                        </a>
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

    <!-- ========================================================================= -->
    <!-- CONTENEDOR PRINCIPAL CON SIDEBAR NAVEGABLE                                -->
    <!-- ========================================================================= -->
    <div class="flex flex-1 relative overflow-x-hidden">

        <!-- Backdrop para dispositivos móviles -->
        <div class="fixed inset-0 z-40 bg-primary/40 backdrop-blur-xs hidden transition-opacity lg:hidden" id="private-sidebar-backdrop"></div>

        <!-- ===================================================================== -->
        <!-- SIDEBAR DE NAVEGACIÓN (Consola Operativa)                             -->
        <!-- ===================================================================== -->
        <aside class="fixed lg:sticky top-16 left-0 h-[calc(100vh-4rem)] w-64 sm:w-72 bg-surface dark:bg-primary-container border-r border-outline-variant/60 dark:border-primary shadow-xl lg:shadow-none z-50 flex flex-col justify-between p-4 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shrink-0 overflow-y-auto" id="private-sidebar">
            
            <div class="space-y-6">
                <!-- Encabezado del Menú Lateral -->
                <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                    <span class="font-mono text-[11px] text-secondary uppercase font-bold tracking-wider">Módulos del Sistema</span>
                    <button class="lg:hidden p-1 text-outline hover:text-primary dark:hover:text-surface" id="sidebar-close-btn" type="button">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Lista de Navegación por Módulos -->
                <nav class="space-y-1.5">
                    <!-- Dashboard -->
                    <a href="{{ url('/dashboard') }}" class="group rounded-xl px-3.5 py-2.5 flex items-center gap-3 transition-all active:scale-[0.98] {{ request()->is('dashboard*') ? 'neu-raised bg-surface-container dark:bg-primary text-primary dark:text-surface font-bold' : 'text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container/60 dark:hover:bg-primary/50 font-medium' }}">
                        <span class="material-symbols-outlined text-xl text-secondary" style="{{ request()->is('dashboard*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                        <span class="font-body text-sm">Dashboard</span>
                    </a>

                    <!-- Activos e Inventario -->
                    <a href="{{ url('/activos') }}" class="group rounded-xl px-3.5 py-2.5 flex items-center gap-3 transition-all active:scale-[0.98] {{ request()->is('activos*') ? 'neu-raised bg-surface-container dark:bg-primary text-primary dark:text-surface font-bold' : 'text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container/60 dark:hover:bg-primary/50 font-medium' }}">
                        <span class="material-symbols-outlined text-xl text-secondary" style="{{ request()->is('activos*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">inventory_2</span>
                        <span class="font-body text-sm">Control de Activos</span>
                    </a>

                    <!-- Préstamos y Devoluciones -->
                    <a href="{{ url('/prestamos') }}" class="group rounded-xl px-3.5 py-2.5 flex items-center gap-3 transition-all active:scale-[0.98] {{ request()->is('prestamos*') ? 'neu-raised bg-surface-container dark:bg-primary text-primary dark:text-surface font-bold' : 'text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container/60 dark:hover:bg-primary/50 font-medium' }}">
                        <span class="material-symbols-outlined text-xl text-secondary" style="{{ request()->is('prestamos*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">handshake</span>
                        <span class="font-body text-sm">Préstamos & Devoluciones</span>
                    </a>

                    <!-- Tickets & Mantenimiento -->
                    <a href="{{ url('/tickets') }}" class="group rounded-xl px-3.5 py-2.5 flex items-center gap-3 transition-all active:scale-[0.98] {{ request()->is('tickets*') || request()->is('mantenimiento*') ? 'neu-raised bg-surface-container dark:bg-primary text-primary dark:text-surface font-bold' : 'text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container/60 dark:hover:bg-primary/50 font-medium' }}">
                        <span class="material-symbols-outlined text-xl text-secondary" style="{{ request()->is('tickets*') || request()->is('mantenimiento*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">build</span>
                        <span class="font-body text-sm">Mantenimiento & Tickets</span>
                    </a>

                    <!-- Usuarios, Roles y Permisos -->
                    <a href="{{ url('/usuarios') }}" class="group rounded-xl px-3.5 py-2.5 flex items-center gap-3 transition-all active:scale-[0.98] {{ request()->is('usuarios*') || request()->is('roles*') ? 'neu-raised bg-surface-container dark:bg-primary text-primary dark:text-surface font-bold' : 'text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container/60 dark:hover:bg-primary/50 font-medium' }}">
                        <span class="material-symbols-outlined text-xl text-secondary" style="{{ request()->is('usuarios*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">manage_accounts</span>
                        <span class="font-body text-sm">Usuarios & Roles</span>
                    </a>

                    <!-- Bitácora y Auditoría Forense -->
                    <a href="{{ url('/bitacora') }}" class="group rounded-xl px-3.5 py-2.5 flex items-center gap-3 transition-all active:scale-[0.98] {{ request()->is('bitacora*') ? 'neu-raised bg-surface-container dark:bg-primary text-primary dark:text-surface font-bold' : 'text-on-surface-variant dark:text-inverse-primary hover:bg-surface-container/60 dark:hover:bg-primary/50 font-medium' }}">
                        <span class="material-symbols-outlined text-xl text-secondary" style="{{ request()->is('bitacora*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">verified_user</span>
                        <span class="font-body text-sm">Bitácora & Auditoría</span>
                    </a>
                </nav>
            </div>

            <!-- Telemetría y Pie del Sidebar -->
            <div class="pt-4 border-t border-outline-variant/30 space-y-3">
                <div class="p-3 rounded-xl neu-inset bg-surface-container-low dark:bg-primary flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-live-dot"></span>
                        <span class="font-mono text-[11px] text-primary dark:text-surface font-semibold">Audit Stream Activo</span>
                    </div>
                    <span class="font-mono text-[10px] text-outline">WS: OK</span>
                </div>
                <p class="font-mono text-[10px] text-outline text-center">
                    SIGA Enterprise • v4.2 PRO
                </p>
            </div>
        </aside>

        <!-- ===================================================================== -->
        <!-- ÁREA DE TRABAJO DINÁMICA                                             -->
        <!-- ===================================================================== -->
        <main class="flex-1 w-full p-4 sm:p-6 lg:p-8 overflow-y-auto">
            
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
    </div>

    <!-- Modales Globales -->
    @yield('modals')

    <!-- ========================================================================= -->
    <!-- SCRIPTS BASE: MODO OSCURO, SIDEBAR TOGGLE & BUSCADOR                      -->
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

        const savedPrivateTheme = localStorage.getItem('siga_theme');
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

        // Toggle del Sidebar en dispositivos móviles
        const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
        const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
        const privateSidebar = document.getElementById('private-sidebar');
        const privateBackdrop = document.getElementById('private-sidebar-backdrop');

        function openPrivateSidebar() {
            if (!privateSidebar || !privateBackdrop) return;
            privateSidebar.classList.remove('-translate-x-full');
            privateBackdrop.classList.remove('hidden');
        }

        function closePrivateSidebar() {
            if (!privateSidebar || !privateBackdrop) return;
            privateSidebar.classList.add('-translate-x-full');
            privateBackdrop.classList.add('hidden');
        }

        if (sidebarToggleBtn) sidebarToggleBtn.addEventListener('click', openPrivateSidebar);
        if (sidebarCloseBtn) sidebarCloseBtn.addEventListener('click', closePrivateSidebar);
        if (privateBackdrop) privateBackdrop.addEventListener('click', closePrivateSidebar);

        // Menú desplegable de perfil de usuario
        const userBtn = document.getElementById('user-profile-menu-btn');
        const userDropdown = document.getElementById('user-profile-dropdown');
        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
        }

        // Menú selector de rol
        const roleBtn = document.getElementById('role-menu-btn');
        const roleDropdown = document.getElementById('role-menu-dropdown');
        if (roleBtn && roleDropdown) {
            roleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                roleDropdown.classList.toggle('hidden');
            });
        }

        function switchRole(roleName) {
            const label = document.getElementById('current-role-label');
            if (label) label.textContent = roleName;
            if (roleDropdown) roleDropdown.classList.add('hidden');
        }

        // Buscador Predictivo (Ctrl + K)
        const searchInput = document.getElementById('global-search-input');
        const searchDropdown = document.getElementById('predictive-search-dropdown');

        if (searchInput && searchDropdown) {
            searchInput.addEventListener('focus', () => searchDropdown.classList.remove('hidden'));
            searchInput.addEventListener('input', (e) => {
                if (e.target.value.trim().length > 0) {
                    searchDropdown.classList.remove('hidden');
                }
            });
        }

        // Atajos globales de teclado
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (searchInput) searchInput.focus();
            }
            if (e.key === 'Escape') {
                if (searchDropdown) searchDropdown.classList.add('hidden');
                if (userDropdown) userDropdown.classList.add('hidden');
                if (roleDropdown) roleDropdown.classList.add('hidden');
                closePrivateSidebar();
            }
        });

        // Cerrar dropdowns al hacer clic fuera
        window.addEventListener('click', (e) => {
            if (userDropdown && !userDropdown.contains(e.target) && userBtn && !userBtn.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
            if (roleDropdown && !roleDropdown.contains(e.target) && roleBtn && !roleBtn.contains(e.target)) {
                roleDropdown.classList.add('hidden');
            }
            if (searchDropdown && !searchDropdown.contains(e.target) && searchInput && !searchInput.contains(e.target)) {
                searchDropdown.classList.add('hidden');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
