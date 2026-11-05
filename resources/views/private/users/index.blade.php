@extends('layouts.app')

@section('title', 'Administración de Usuarios y Privilegios')

@section('header')
    <div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight">
            Administración de Usuarios y Privilegios
        </h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">
            Control de identidades universitarias, roles RBAC, estatus de suspensión y matriz de acceso.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="#matriz-permisos" class="neu-btn-secondary px-3.5 py-2 rounded-xl font-mono text-xs font-bold text-secondary flex items-center gap-1.5 hover:text-primary transition-all">
            <span class="material-symbols-outlined text-base">vpn_key</span>
            <span>Matriz de Privilegios</span>
        </a>
        <button type="button" onclick="openCreateUserModal()" class="neu-btn-primary px-4 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">person_add</span>
            <span>+ Registrar Usuario</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tarjetas de Conteo de Usuarios -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('users.index') }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ !request('role') && !request('status') ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Total Comunidad</span>
            <span class="font-headline text-xl font-bold text-primary dark:text-surface mt-1">{{ $counts['total'] }}</span>
        </a>
        <a href="{{ route('users.index', ['status' => 'activo']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'activo' ? 'border-2 border-emerald-500' : '' }}">
            <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-live-dot"></span> Cuentas Activas
            </span>
            <span class="font-headline text-xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $counts['activos'] }}</span>
        </a>
        <a href="{{ route('users.index', ['role' => 'tecnico']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('role') === 'tecnico' ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-secondary uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span> Técnicos Taller
            </span>
            <span class="font-headline text-xl font-bold text-secondary mt-1">{{ $counts['tecnicos'] }}</span>
        </a>
        <a href="{{ route('users.index', ['status' => 'sancionado']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'sancionado' ? 'border-2 border-error' : '' }}">
            <span class="font-mono text-[10px] text-error uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-error"></span> Sancionados
            </span>
            <span class="font-headline text-xl font-bold text-error mt-1">{{ $counts['sancionados'] }}</span>
        </a>
    </div>

    <!-- Filtros de Búsqueda de Usuarios -->
    <div class="neu-raised rounded-2xl p-4">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <div class="sm:col-span-5 neu-inset rounded-xl flex items-center px-3 py-1.5">
                <span class="material-symbols-outlined text-secondary mr-2 text-lg">search</span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Nombre, correo, matrícula/empleado..." 
                    class="w-full bg-transparent border-0 font-body text-xs text-on-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0"
                />
            </div>

            <div class="sm:col-span-3">
                <select name="role" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Todos los Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="encargado_activos" {{ request('role') === 'encargado_activos' ? 'selected' : '' }}>Encargado de Activos</option>
                    <option value="encargado_prestamos" {{ request('role') === 'encargado_prestamos' ? 'selected' : '' }}>Encargado de Préstamos</option>
                    <option value="tecnico" {{ request('role') === 'tecnico' ? 'selected' : '' }}>Técnico de Taller</option>
                    <option value="usuario" {{ request('role') === 'usuario' ? 'selected' : '' }}>Usuario General (Alumno/Docente)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Cualquier Estado</option>
                    <option value="activo" {{ request('status') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('status') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    <option value="sancionado" {{ request('status') === 'sancionado' ? 'selected' : '' }}>Sancionado</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full neu-btn-primary py-1.5 px-3 rounded-xl font-headline text-xs font-semibold">
                    Filtrar
                </button>
                @if (request()->hasAny(['search', 'role', 'status', 'user_type']))
                    <a href="{{ route('users.index') }}" class="neu-btn-secondary p-1.5 rounded-xl text-outline hover:text-error" title="Limpiar filtros">
                        <span class="material-symbols-outlined text-base">filter_alt_off</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Usuarios y Privilegios -->
    <div class="neu-raised rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-body text-xs">
                <thead>
                    <tr class="border-b border-outline-variant/40 bg-surface-container-low dark:bg-primary-container/40">
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Usuario / Correo Institucional</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">No. Identificación</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Rol Asignado</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Carrera / Área</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Estado Operativo</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($users as $user)
                        <tr class="hover:bg-surface-container/50 dark:hover:bg-primary/40 transition-colors">
                            <!-- Usuario / Correo -->
                            <td class="p-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-primary-container text-secondary flex items-center justify-center font-bold font-mono text-xs">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-headline font-semibold text-primary dark:text-surface">{{ $user->name }}</p>
                                        <p class="font-mono text-[11px] text-outline">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Identificación -->
                            <td class="p-3.5">
                                <span class="font-mono font-bold text-primary dark:text-surface block">{{ $user->institutional_id }}</span>
                                <span class="font-mono text-[10px] text-outline capitalize">{{ $user->user_type }}</span>
                            </td>

                            <!-- Rol -->
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full font-mono text-[10px] font-bold uppercase
                                    @if ($user->role === 'admin') bg-purple-100 text-purple-900 border border-purple-300
                                    @elseif ($user->role === 'tecnico') bg-blue-100 text-blue-900 border border-blue-300
                                    @elseif ($user->role === 'encargado_prestamos') bg-amber-100 text-amber-900 border border-amber-300
                                    @elseif ($user->role === 'encargado_activos') bg-emerald-100 text-emerald-900 border border-emerald-300
                                    @else bg-surface-container text-primary @endif">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                            </td>

                            <!-- Departamento -->
                            <td class="p-3.5 font-body text-xs text-on-surface-variant">
                                {{ $user->career_or_department ?? 'Sin Asignar' }}
                            </td>

                            <!-- Estado -->
                            <td class="p-3.5">
                                @if ($user->status === 'activo')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-live-dot"></span> Activo
                                    </span>
                                @elseif ($user->status === 'sancionado')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container border border-error/40 font-mono text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-error"></span> Sancionado
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 font-mono text-[10px]">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Alternar Estatus -->
                                    <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors" title="{{ $user->status === 'activo' ? 'Suspender acceso' : 'Reactivar acceso' }}">
                                            <span class="material-symbols-outlined text-base">{{ $user->status === 'activo' ? 'toggle_on' : 'toggle_off' }}</span>
                                        </button>
                                    </form>

                                    <!-- Reset provisional -->
                                    <form method="POST" action="{{ route('users.reset-credentials', $user) }}" class="inline" onsubmit="return confirm('¿Emitir credencial provisional temporal para este usuario?')">
                                        @csrf
                                        <button type="submit" class="p-1 rounded-lg hover:bg-amber-50 text-on-surface-variant hover:text-secondary transition-colors" title="Generar clave temporal">
                                            <span class="material-symbols-outlined text-base">password</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-outline">
                                <span class="material-symbols-outlined text-3xl mb-1 block">manage_accounts</span>
                                <p class="font-headline font-semibold text-sm">No se encontraron usuarios coincidentes.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-3.5 border-t border-outline-variant/30 flex justify-between items-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Matriz de Privilegios por Rol (RBAC) -->
    <div id="matriz-permisos" class="neu-raised rounded-2xl p-6 space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30">
            <span class="material-symbols-outlined text-secondary text-xl">security</span>
            <h3 class="font-headline text-base font-bold text-primary dark:text-surface">
                Matriz Institucional de Privilegios por Rol (RBAC)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-center font-mono text-xs border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/30">
                        <th class="p-2.5 text-left text-secondary font-bold">Rol Operativo</th>
                        <th class="p-2.5 text-secondary">Dashboard</th>
                        <th class="p-2.5 text-secondary">Activos CRUD</th>
                        <th class="p-2.5 text-secondary">Préstamos</th>
                        <th class="p-2.5 text-secondary">Tickets</th>
                        <th class="p-2.5 text-secondary">Baja Lógica</th>
                        <th class="p-2.5 text-secondary">Bitácora Forense</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @foreach ($permissionsMatrix as $rKey => $pms)
                        <tr>
                            <td class="p-2.5 text-left font-bold text-primary capitalize">{{ str_replace('_', ' ', $rKey) }}</td>
                            <td class="p-2.5">{!! $pms['dashboard'] ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-outline">✗</span>' !!}</td>
                            <td class="p-2.5">{!! $pms['activos'] ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-outline">✗</span>' !!}</td>
                            <td class="p-2.5">{!! $pms['prestamos'] ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-outline">✗</span>' !!}</td>
                            <td class="p-2.5">{!! $pms['tickets'] ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-outline">✗</span>' !!}</td>
                            <td class="p-2.5">{!! $pms['baja'] ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-outline">✗</span>' !!}</td>
                            <td class="p-2.5">{!! $pms['bitacora'] ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-outline">✗</span>' !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTRAR NUEVO USUARIO INSTITUCIONAL                             -->
<!-- ========================================================================= -->
<div id="modalCreateUser" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">person_add</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">Registrar Usuario Institucional</h3>
            </div>
            <button type="button" onclick="closeCreateUserModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Nombre Completo *</label>
                <input type="text" name="name" required placeholder="Ej. Dra. Carmen Salgado / Juan Pérez López" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Matrícula o No. Empleado *</label>
                    <input type="text" name="institutional_id" required placeholder="Ej. ALU-20240999 o EMP-00109" class="neu-inset w-full px-3 py-2 rounded-xl text-xs font-mono" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Tipo Institucional *</label>
                    <select name="user_type" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="alumno">Alumno Universitario</option>
                        <option value="docente">Docente / Investigador</option>
                        <option value="administrativo">Personal Administrativo</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Correo Electrónico *</label>
                    <input type="email" name="email" required placeholder="ejemplo@universidad.edu.mx" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Teléfono de Contacto</label>
                    <input type="text" name="phone" placeholder="555-000-0000" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Rol en el Sistema *</label>
                    <select name="role" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="usuario">Usuario (Préstamos únicamente)</option>
                        <option value="tecnico">Técnico de Taller y Soporte</option>
                        <option value="encargado_prestamos">Encargado de Préstamos</option>
                        <option value="encargado_activos">Encargado de Activos</option>
                        <option value="supervisor">Supervisor de Operaciones</option>
                        <option value="admin">Administrador Total</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Estado Inicial *</label>
                    <select name="status" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="activo" selected>Activo (Habilitado)</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="sancionado">Sancionado</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Carrera o Departamento</label>
                    <input type="text" name="career_or_department" placeholder="Ej. Ingeniería en Software" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Contraseña de Acceso *</label>
                    <input type="password" name="password" required placeholder="Mínimo 8 caracteres" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeCreateUserModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>Guardar Usuario</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateUserModal() {
        document.getElementById('modalCreateUser').classList.remove('hidden');
    }
    function closeCreateUserModal() {
        document.getElementById('modalCreateUser').classList.add('hidden');
    }
</script>
@endsection

