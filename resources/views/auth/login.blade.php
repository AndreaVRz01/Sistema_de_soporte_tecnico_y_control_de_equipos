<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SIGA Enterprise - Inicio de Sesión y Recuperación de Contraseña</title>
    <!-- Material Symbols & Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300..800;1,300..800&family=JetBrains+Mono:ital,wght@0,300..800;1,300..800&family=Space+Grotesk:wght@400..700&display=swap" rel="stylesheet"/>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              colors: {
                "primary": "#001a2b",
                "primary-container": "#003049",
                "on-primary": "#ffffff",
                "on-primary-container": "#7398b6",
                "primary-fixed": "#cae6ff",
                "secondary": "#835500",
                "secondary-container": "#feb956",
                "on-secondary-container": "#734a00",
                "secondary-fixed": "#ffddb4",
                "tertiary": "#675f2b",
                "tertiary-container": "#b6ac6f",
                "surface": "#fff8f7",
                "surface-container": "#ffe9e7",
                "surface-container-low": "#fff0ef",
                "surface-container-lowest": "#ffffff",
                "on-surface": "#410004",
                "on-surface-variant": "#42474d",
                "outline": "#72787e",
                "outline-variant": "#c2c7cd",
                "error": "#ba1a1a",
                "error-container": "#ffdad6",
                "on-error-container": "#93000a",
                "background": "#fff8f7"
              },
              fontFamily: {
                "headline": ["Space Grotesk", "sans-serif"],
                "body": ["Hanken Grotesk", "sans-serif"],
                "mono": ["JetBrains Mono", "monospace"]
              }
            }
          }
        }
    </script>
    <style>
        .material-symbols-outlined {
          font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
          display: inline-block;
          vertical-align: middle;
          line-height: 1;
        }
        
        .neu-raised {
          background: #fff8f7;
          box-shadow: -6px -6px 14px rgba(255, 255, 255, 0.95), 6px 6px 16px rgba(0, 48, 73, 0.08);
          border: 1px solid rgba(255, 255, 255, 0.8);
        }
        .dark .neu-raised {
          background: #001a2b;
          box-shadow: -4px -4px 12px rgba(255, 243, 176, 0.04), 6px 6px 18px rgba(0, 10, 18, 0.7);
          border: 1px solid rgba(224, 159, 62, 0.12);
        }

        .neu-inset {
          background: #fff0ef;
          box-shadow: inset 3px 3px 6px rgba(0, 48, 73, 0.08), inset -3px -3px 6px rgba(255, 255, 255, 0.9);
          border: 1px solid rgba(0, 48, 73, 0.06);
        }
        .dark .neu-inset {
          background: #001422;
          box-shadow: inset 3px 3px 6px rgba(0, 10, 18, 0.8), inset -2px -2px 5px rgba(255, 243, 176, 0.03);
          border: 1px solid rgba(0, 0, 0, 0.6);
        }

        .neu-btn-primary {
          background: #003049;
          color: #ffffff;
          box-shadow: -4px -4px 10px rgba(255, 255, 255, 0.8), 5px 5px 12px rgba(0, 48, 73, 0.25);
          border: 1px solid rgba(255, 243, 176, 0.2);
        }
        .neu-btn-primary:active {
          box-shadow: inset 3px 3px 6px rgba(0, 10, 18, 0.7), inset -2px -2px 5px rgba(255, 243, 176, 0.1);
          transform: scale(0.985);
        }

        .neu-btn-secondary {
          background: #fff8f7;
          color: #003049;
          box-shadow: -4px -4px 10px rgba(255, 255, 255, 0.95), 4px 4px 10px rgba(0, 48, 73, 0.08);
          border: 1px solid rgba(255, 255, 255, 0.7);
        }
        .dark .neu-btn-secondary {
          background: #002238;
          color: #cae6ff;
          box-shadow: -3px -3px 8px rgba(255, 243, 176, 0.04), 4px 4px 10px rgba(0, 10, 18, 0.6);
          border: 1px solid rgba(224, 159, 62, 0.15);
        }
        .neu-btn-secondary:active {
          box-shadow: inset 2px 2px 5px rgba(0, 48, 73, 0.12), inset -2px -2px 5px rgba(255, 255, 255, 0.8);
          transform: scale(0.985);
        }

        .neu-tab-active {
          background: #003049;
          color: #fff8f7;
          box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.35), 0 4px 10px rgba(0, 48, 73, 0.15);
        }
        .dark .neu-tab-active {
          background: #cae6ff;
          color: #001e2f;
          box-shadow: inset 1px 1px 4px rgba(0, 30, 47, 0.2);
        }
    </style>
</head>
<body class="bg-surface dark:bg-primary text-on-surface dark:text-surface antialiased min-h-screen flex flex-col justify-between selection:bg-secondary-container transition-colors duration-200 font-body">

    <!-- HEADER SUPERIOR -->
    <header class="w-full bg-surface/90 dark:bg-primary/95 backdrop-blur-md sticky top-0 z-40 shadow-xs border-b border-outline-variant/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <div class="neu-raised w-10 h-10 rounded-xl flex items-center justify-center text-primary-container dark:text-secondary-fixed">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">terminal</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-headline text-lg sm:text-xl font-bold text-primary dark:text-primary-fixed tracking-tight">SIGA Enterprise</span>
                        <span class="px-2 py-0.5 rounded-full bg-secondary-container/40 text-on-secondary-container font-mono text-[10px] font-bold uppercase">v4.2 PRO</span>
                    </div>
                    <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary hidden sm:block">Consola de Operaciones & Control Relacional</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a class="hidden sm:flex items-center gap-1.5 font-mono text-xs text-on-surface-variant hover:text-primary transition-colors px-3 py-1.5 rounded-lg neu-btn-secondary" href="{{ route('public.catalog') }}">
                    <span class="material-symbols-outlined text-base">menu_book</span>
                    <span>Catálogo Público</span>
                </a>
                <button aria-label="Cambiar tema de color" class="w-9 h-9 rounded-xl neu-btn-secondary flex items-center justify-center text-primary dark:text-primary-fixed cursor-pointer" id="theme-toggle" type="button">
                    <span class="material-symbols-outlined text-base" id="theme-icon">dark_mode</span>
                </button>
            </div>
        </div>
    </header>

    <!-- ÁREA DE CONTENIDO -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 py-8 flex flex-col justify-center">

        <!-- Notificaciones Flash -->
        @if (session('success'))
            <div class="mb-6 max-w-4xl mx-auto w-full p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 neu-raised flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                    <p class="font-body text-sm font-medium">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 cursor-pointer">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 max-w-4xl mx-auto w-full p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container neu-raised flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-error text-xl">error</span>
                    <p class="font-body text-sm font-medium">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-on-error-container hover:opacity-80 cursor-pointer">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 max-w-4xl mx-auto w-full p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container neu-raised shadow-xs">
                <p class="font-headline font-bold text-xs uppercase mb-1">Por favor revise los siguientes errores:</p>
                <ul class="list-disc pl-5 font-body text-xs space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario Centrado de Inicio de Sesión y Recuperación -->
        <div class="max-w-xl mx-auto w-full">
            <div class="neu-raised rounded-2xl p-6 sm:p-8 relative overflow-hidden transition-all duration-200 shadow-md border border-white/70 dark:border-primary-container">
                    
                    <!-- VISTA 1: INICIAR SESIÓN -->
                    <div class="space-y-6 {{ session('token_sent') || old('recovery_mode') ? 'hidden' : '' }}" id="view-login">
                        <div class="border-b border-outline-variant/30 pb-3">
                            <h2 class="font-headline text-xl font-bold text-primary dark:text-primary-fixed">
                                Autenticación de Operador
                            </h2>
                            <p class="font-body text-xs text-on-surface-variant">
                                Ingrese con sus credenciales institucionales registradas en el sistema.
                            </p>
                        </div>

                        <form class="space-y-4" method="POST" action="{{ route('login.post') }}">
                            @csrf
                            
                            <!-- Campo Correo / Matrícula -->
                            <div class="space-y-1">
                                <label class="block font-mono text-xs text-primary dark:text-primary-fixed font-bold" for="login-email">
                                    Correo Institucional o No. de Empleado / Matrícula
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                                        <span class="material-symbols-outlined text-lg">mail</span>
                                    </div>
                                    <input 
                                        class="neu-inset w-full pl-11 pr-4 py-2.5 rounded-xl font-body text-sm text-primary dark:text-primary-fixed placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-secondary transition-all" 
                                        id="login-email" 
                                        name="login" 
                                        placeholder="ej. admin@universidad.edu.mx o EMP-00101" 
                                        required 
                                        type="text" 
                                        value="{{ old('login') }}"
                                    />
                                </div>
                            </div>

                            <!-- Campo Contraseña -->
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <label class="block font-mono text-xs text-primary dark:text-primary-fixed font-bold" for="login-password">
                                        Contraseña
                                    </label>
                                    <button class="font-mono text-[11px] text-secondary hover:underline cursor-pointer" onclick="switchView('recovery')" type="button">
                                        ¿Olvidaste tu contraseña?
                                    </button>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                                        <span class="material-symbols-outlined text-lg">lock</span>
                                    </div>
                                    <input 
                                        class="neu-inset w-full pl-11 pr-11 py-2.5 rounded-xl font-body text-sm text-primary dark:text-primary-fixed placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-secondary transition-all" 
                                        id="login-password" 
                                        name="password" 
                                        placeholder="••••••••••••" 
                                        required 
                                        type="password" 
                                    />
                                    <button class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-on-surface-variant hover:text-primary cursor-pointer" onclick="togglePassVisibility()" type="button">
                                        <span class="material-symbols-outlined text-base" id="toggle-pass-icon">visibility</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Recordar sesión -->
                            <div class="flex items-center justify-between pt-1">
                                <label class="flex items-center gap-2 cursor-pointer font-body text-xs text-on-surface-variant">
                                    <input class="w-4 h-4 rounded text-primary-container focus:ring-secondary cursor-pointer" name="remember" type="checkbox" checked />
                                    <span>Mantener sesión iniciada</span>
                                </label>
                            </div>

                            <!-- Botón de Envío -->
                            <button class="w-full neu-btn-primary py-3 rounded-xl font-headline font-semibold text-sm flex items-center justify-center gap-2 cursor-pointer mt-4" type="submit">
                                <span>Ingresar al Sistema SIGA</span>
                                <span class="material-symbols-outlined text-lg">arrow_forward</span>
                            </button>
                        </form>
                    </div>

                    <!-- VISTA 2: RECUPERACIÓN CON TOKEN -->
                    <div class="space-y-6 {{ session('token_sent') || old('recovery_mode') ? '' : 'hidden' }}" id="view-recovery">
                        <div class="border-b border-outline-variant/30 pb-3">
                            <h2 class="font-headline text-xl font-bold text-primary dark:text-primary-fixed">
                                Recuperación de Acceso con Token
                            </h2>
                            <p class="font-body text-xs text-on-surface-variant">
                                Genere un token alfanumérico institucional y valide sus nuevas credenciales.
                            </p>
                        </div>

                        <!-- PASO 1: SOLICITAR TOKEN -->
                        <form class="space-y-4" method="POST" action="{{ route('password.token') }}">
                            @csrf
                            <input type="hidden" name="recovery_mode" value="1" />
                            <div class="space-y-1">
                                <label class="block font-mono text-xs text-primary dark:text-primary-fixed font-bold">
                                    Paso 1: Solicitar Token de Autorización
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        type="text" 
                                        name="recovery_identifier" 
                                        class="neu-inset flex-1 px-3.5 py-2.5 rounded-xl font-body text-xs text-primary placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-secondary" 
                                        placeholder="Correo institucional o Matrícula" 
                                        required 
                                        value="{{ session('user_email', old('recovery_identifier', '')) }}"
                                    />
                                    <button type="submit" class="neu-btn-secondary px-4 py-2.5 rounded-xl font-mono text-xs font-bold text-secondary flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-base">vpn_key</span>
                                        <span>Generar</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if (session('demo_token'))
                            <div class="p-3 rounded-xl neu-inset bg-amber-50 border border-amber-300 text-amber-900 text-xs">
                                <span class="font-bold">Token generado para demostración:</span>
                                <code class="ml-2 px-2 py-0.5 rounded bg-amber-200 font-mono font-bold text-amber-950 text-sm">{{ session('demo_token') }}</code>
                            </div>
                        @endif

                        <!-- PASO 2: RESTABLECER CON TOKEN -->
                        <form class="space-y-4 pt-2 border-t border-outline-variant/30" method="POST" action="{{ route('password.reset') }}">
                            @csrf
                            <input type="hidden" name="recovery_mode" value="1" />
                            <input type="hidden" name="email" value="{{ session('user_email', old('email', '')) }}" id="recovery-post-email" />

                            <div class="space-y-1">
                                <label class="block font-mono text-xs text-primary font-bold">
                                    Paso 2: Código de Token Recibido (ej: TK-8942-X)
                                </label>
                                <input 
                                    type="text" 
                                    name="token" 
                                    class="w-full text-center tracking-widest font-mono text-lg py-2 rounded-xl neu-inset font-bold text-primary uppercase focus:outline-none focus:ring-2 focus:ring-secondary" 
                                    placeholder="TK-8942-X" 
                                    required 
                                    value="{{ session('demo_token', '') }}"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="block font-mono text-[11px] text-primary font-bold">Nueva Contraseña</label>
                                    <input type="password" name="password" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" placeholder="Mínimo 8 caracteres" required />
                                </div>
                                <div class="space-y-1">
                                    <label class="block font-mono text-[11px] text-primary font-bold">Confirmar Clave</label>
                                    <input type="password" name="password_confirmation" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" placeholder="Repita la clave" required />
                                </div>
                            </div>

                            <button type="submit" class="w-full neu-btn-primary py-2.5 rounded-xl font-headline font-semibold text-xs flex items-center justify-center gap-2 cursor-pointer">
                                <span class="material-symbols-outlined text-base">restart_alt</span>
                                <span>Restablecer Contraseña e Iniciar</span>
                            </button>
                        </form>

                        <div class="text-center pt-2">
                            <button type="button" onclick="switchView('login')" class="font-mono text-xs text-on-surface-variant hover:text-primary cursor-pointer flex items-center justify-center gap-1 mx-auto">
                                <span class="material-symbols-outlined text-sm">arrow_back</span>
                                <span>Volver a Inicio de Sesión</span>
                            </button>
                        </div>
                    </div>

                </div>
        </div>
    </main>

    <!-- FOOTER INSTITUCIONAL -->
    <footer class="w-full py-4 text-center border-t border-outline-variant/30 text-on-surface-variant font-mono text-[11px]">
        <p>SIGA Enterprise • Sistema Institucional de Gestión de Activos, Préstamos y Mantenimiento • UPTex 2026</p>
    </footer>

    <script>
        function switchView(view) {
            const loginView = document.getElementById('view-login');
            const recoveryView = document.getElementById('view-recovery');

            if (view === 'login') {
                loginView.classList.remove('hidden');
                recoveryView.classList.add('hidden');
            } else {
                loginView.classList.add('hidden');
                recoveryView.classList.remove('hidden');
            }
        }

        function togglePassVisibility() {
            const passInput = document.getElementById('login-password');
            const passIcon = document.getElementById('toggle-pass-icon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                passIcon.textContent = 'visibility_off';
            } else {
                passInput.type = 'password';
                passIcon.textContent = 'visibility';
            }
        }

        // Modo Oscuro
        const themeBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        themeBtn.addEventListener('click', () => {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                themeIcon.textContent = 'dark_mode';
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.textContent = 'light_mode';
            }
        });
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            themeIcon.textContent = 'light_mode';
        }
    </script>
</body>
</html>

