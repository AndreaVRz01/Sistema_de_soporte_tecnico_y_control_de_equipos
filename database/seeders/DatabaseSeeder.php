<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\IncidentLog;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with roles, categories, equipment, loans, and technical tickets.
     */
    public function run(): void
    {
        $password = Hash::make('password123');

        // 1. Usuarios con todos los cargos requeridos por el sistema
        $admin = User::firstOrCreate(
            ['email' => 'admin@universidad.edu.mx'],
            [
                'name' => 'Dr. Carlos Mendoza Silva',
                'password' => $password,
                'role' => User::ROLE_ADMIN,
                'user_type' => User::TYPE_ADMINISTRATIVO,
                'institutional_id' => 'EMP-00101',
                'career_or_department' => 'Dirección de Tecnologías de Información',
                'phone' => '555-101-0001',
                'status' => 'activo',
            ]
        );

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@universidad.edu.mx'],
            [
                'name' => 'Mtra. Laura Patricia Gómez',
                'password' => $password,
                'role' => User::ROLE_SUPERVISOR,
                'user_type' => User::TYPE_ADMINISTRATIVO,
                'institutional_id' => 'EMP-00102',
                'career_or_department' => 'Supervisión Académica y Auditoría',
                'phone' => '555-101-0002',
                'status' => 'activo',
            ]
        );

        $encargadoActivos = User::firstOrCreate(
            ['email' => 'activos@universidad.edu.mx'],
            [
                'name' => 'Ing. Fernando Castillo Ruiz',
                'password' => $password,
                'role' => User::ROLE_ENCARGADO_ACTIVOS,
                'user_type' => User::TYPE_ADMINISTRATIVO,
                'institutional_id' => 'EMP-00103',
                'career_or_department' => 'Control Patrimonial y Almacén',
                'phone' => '555-101-0003',
                'status' => 'activo',
            ]
        );

        $encargadoPrestamos = User::firstOrCreate(
            ['email' => 'prestamos@universidad.edu.mx'],
            [
                'name' => 'Lic. Mariana Soto Vega',
                'password' => $password,
                'role' => User::ROLE_ENCARGADO_PRESTAMOS,
                'user_type' => User::TYPE_ADMINISTRATIVO,
                'institutional_id' => 'EMP-00104',
                'career_or_department' => 'Módulo Central de Préstamo de Equipos',
                'phone' => '555-101-0004',
                'status' => 'activo',
            ]
        );

        $tecnico = User::firstOrCreate(
            ['email' => 'tecnico@universidad.edu.mx'],
            [
                'name' => 'Téc. Roberto Valenzuela Gil',
                'password' => $password,
                'role' => User::ROLE_TECNICO,
                'user_type' => User::TYPE_ADMINISTRATIVO,
                'institutional_id' => 'EMP-00105',
                'career_or_department' => 'Departamento de Soporte Técnico y Mantenimiento',
                'phone' => '555-101-0005',
                'status' => 'activo',
            ]
        );

        // Docente universitario (Rol: usuario)
        $docente = User::firstOrCreate(
            ['email' => 'docente@universidad.edu.mx'],
            [
                'name' => 'Prof. Alejandro Morales Domínguez',
                'password' => $password,
                'role' => User::ROLE_USUARIO,
                'user_type' => User::TYPE_DOCENTE,
                'institutional_id' => 'DOC-84210',
                'career_or_department' => 'Facultad de Ingeniería y Ciencias',
                'phone' => '555-202-0010',
                'status' => 'activo',
            ]
        );

        // Alumno universitario (Rol: usuario)
        $alumno = User::firstOrCreate(
            ['email' => 'alumno@universidad.edu.mx'],
            [
                'name' => 'Sofía Ramírez Hernández',
                'password' => $password,
                'role' => User::ROLE_USUARIO,
                'user_type' => User::TYPE_ALUMNO,
                'institutional_id' => 'ALU-20240189',
                'career_or_department' => 'Ingeniería en Software',
                'phone' => '555-303-0020',
                'status' => 'activo',
            ]
        );

        // 2. Categorías de equipos institucionales
        $catLaptops = Category::firstOrCreate(
            ['code' => 'LAP'],
            [
                'name' => 'Laptops y Portátiles',
                'description' => 'Computadoras portátiles para prácticas de laboratorio, docencia y presentaciones.',
                'is_active' => true,
            ]
        );

        $catProyectores = Category::firstOrCreate(
            ['code' => 'PROY'],
            [
                'name' => 'Proyectores y Pantallas',
                'description' => 'Equipos de proyección audiovisual para aulas y auditorios.',
                'is_active' => true,
            ]
        );

        $catAudio = Category::firstOrCreate(
            ['code' => 'AUD'],
            [
                'name' => 'Audio y Microfonía',
                'description' => 'Micrófonos inalámbricos, bocinas portátiles e interfaces de audio.',
                'is_active' => true,
            ]
        );

        $catRedes = Category::firstOrCreate(
            ['code' => 'RED'],
            [
                'name' => 'Equipos de Redes y Telecomunicaciones',
                'description' => 'Switches, routers y puntos de acceso para prácticas de laboratorio de redes.',
                'is_active' => true,
            ]
        );

        $catPerifericos = Category::firstOrCreate(
            ['code' => 'PER'],
            [
                'name' => 'Periféricos y Accesorios',
                'description' => 'Teclados, mouse, adaptadores HDMI/USB-C y tabletas digitalizadoras.',
                'is_active' => true,
            ]
        );

        // 3. Equipos físicos con número de serie e inventario universitario
        $lap1 = Equipment::firstOrCreate(
            ['inventory_code' => 'ACT-LAP-001'],
            [
                'category_id' => $catLaptops->id,
                'serial_number' => 'DELL-LAT-87421',
                'name' => 'Dell Latitude 5420 i7 16GB',
                'brand' => 'Dell',
                'model' => 'Latitude 5420',
                'specifications' => 'Intel Core i7 11va Gen, 16GB RAM, SSD 512GB NVMe, Pantalla 14" FHD. Incluye cargador tipo C.',
                'status' => 'disponible',
                'physical_condition' => 'excelente',
                'location' => 'Módulo de Préstamos Edificio A',
                'acquisition_date' => '2024-02-15',
            ]
        );

        $lap2 = Equipment::firstOrCreate(
            ['inventory_code' => 'ACT-LAP-002'],
            [
                'category_id' => $catLaptops->id,
                'serial_number' => 'LEN-THK-99231',
                'name' => 'ThinkPad L14 Gen 2 Ryzen 5',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad L14',
                'specifications' => 'AMD Ryzen 5 Pro, 16GB RAM, SSD 512GB, Cargador original de 65W.',
                'status' => 'prestado',
                'physical_condition' => 'bueno',
                'location' => 'Laboratorio de Cómputo 2',
                'acquisition_date' => '2024-05-10',
            ]
        );

        $lap3 = Equipment::firstOrCreate(
            ['inventory_code' => 'ACT-LAP-003'],
            [
                'category_id' => $catLaptops->id,
                'serial_number' => 'HP-PRO-44219',
                'name' => 'HP ProBook 450 G8',
                'brand' => 'HP',
                'model' => 'ProBook 450 G8',
                'specifications' => 'Intel Core i5, 8GB RAM, SSD 256GB.',
                'status' => 'en_mantenimiento',
                'physical_condition' => 'regular',
                'location' => 'Taller de Soporte Técnico',
                'acquisition_date' => '2023-08-20',
            ]
        );

        $proy1 = Equipment::firstOrCreate(
            ['inventory_code' => 'ACT-PROY-001'],
            [
                'category_id' => $catProyectores->id,
                'serial_number' => 'EPS-PWR-33104',
                'name' => 'Epson PowerLite E20 3400 Lumens',
                'brand' => 'Epson',
                'model' => 'PowerLite E20',
                'specifications' => '3400 Lúmenes, Resolución XGA, Entrada HDMI y VGA. Incluye cable HDMI, cable de poder y control remoto.',
                'status' => 'disponible',
                'physical_condition' => 'excelente',
                'location' => 'Módulo de Préstamos Edificio A',
                'acquisition_date' => '2024-01-20',
            ]
        );

        $proy2 = Equipment::firstOrCreate(
            ['inventory_code' => 'ACT-PROY-002'],
            [
                'category_id' => $catProyectores->id,
                'serial_number' => 'BENQ-MX-5601',
                'name' => 'BenQ MX560 4000 Lumens',
                'brand' => 'BenQ',
                'model' => 'MX560',
                'specifications' => '4000 Lúmenes DLP, Doble HDMI, bocina integrada 10W.',
                'status' => 'disponible',
                'physical_condition' => 'bueno',
                'location' => 'Módulo de Préstamos Edificio B',
                'acquisition_date' => '2024-03-12',
            ]
        );

        $router1 = Equipment::firstOrCreate(
            ['inventory_code' => 'ACT-RED-001'],
            [
                'category_id' => $catRedes->id,
                'serial_number' => 'CIS-ISR-1100X',
                'name' => 'Router Cisco ISR 1100 8 Puertos',
                'brand' => 'Cisco',
                'model' => 'ISR 1100',
                'specifications' => '8 puertos Gigabit Ethernet, Soporte VPN y OSPF para prácticas de telecomunicaciones.',
                'status' => 'disponible',
                'physical_condition' => 'excelente',
                'location' => 'Laboratorio de Redes - Estante 4',
                'acquisition_date' => '2023-11-05',
            ]
        );

        // 4. Préstamo de ejemplo activo
        $prestamoActivo = Loan::firstOrCreate(
            ['folio' => 'PRE-2026-0001'],
            [
                'user_id' => $docente->id,
                'authorized_by_id' => $encargadoPrestamos->id,
                'dispatched_by_id' => $encargadoPrestamos->id,
                'purpose' => 'Clase magistral de Programación Web y taller práctico.',
                'location_of_use' => 'Aula 204 - Edificio de Ingenierías',
                'request_date' => now()->subHours(3),
                'loan_date' => now()->subHours(2),
                'expected_return_date' => now()->addHours(3),
                'status' => 'entregado',
                'observations' => 'Se entregó equipo probado y funcionando correctamente con alumno presente.',
            ]
        );

        LoanItem::firstOrCreate(
            [
                'loan_id' => $prestamoActivo->id,
                'equipment_id' => $lap2->id,
            ],
            [
                'condition_on_loan' => 'bueno',
                'accessories_included' => 'Cargador Lenovo 65W, cable USB-C, funda protectora.',
                'notes_on_loan' => 'Presenta leve rayón en tapa exterior pero pantalla y teclado al 100%.',
            ]
        );

        // 5. Incidencia de ejemplo asignada al técnico de soporte
        $incidente = Incident::firstOrCreate(
            ['ticket_code' => 'INC-2026-0001'],
            [
                'equipment_id' => $lap3->id,
                'reported_by_id' => $encargadoActivos->id,
                'assigned_technician_id' => $tecnico->id,
                'title' => 'Falla en bisagra derecha y calentamiento excesivo en ventilador',
                'description' => 'El equipo presenta sobrecalentamiento después de 20 minutos de uso y la bisagra derecha hace crujido al cerrar la pantalla.',
                'type' => 'daño_fisico',
                'priority' => 'alta',
                'status' => 'en_reparacion',
                'diagnostic' => 'El ventilador requiere limpieza profunda y reemplazo de pasta térmica (Arctic MX-4). La bisagra requiere ajuste de tornillería y lubricación interna.',
                'solution_applied' => null,
                'estimated_cost' => 450.00,
                'reported_at' => now()->subDays(1),
            ]
        );

        IncidentLog::firstOrCreate(
            [
                'incident_id' => $incidente->id,
                'activity' => 'Diagnóstico y desarme en laboratorio técnico',
            ],
            [
                'technician_id' => $tecnico->id,
                'notes' => 'Se desarmó tapa inferior, pasta térmica reseca. Se procedió a limpieza con aire comprimido y alcohol isopropílico.',
                'status_before' => 'asignado',
                'status_after' => 'en_reparacion',
            ]
        );
    }
}
