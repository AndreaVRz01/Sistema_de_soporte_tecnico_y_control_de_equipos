<?php

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public catalog filters by category code and numeric category id', function () {
    $laptops = Category::create(['name' => 'Laptops', 'code' => 'LAP']);
    $projectors = Category::create(['name' => 'Proyectores', 'code' => 'PROY']);

    Equipment::create([
        'category_id' => $laptops->id,
        'inventory_code' => 'LAP-TEST-001',
        'serial_number' => 'LAP-SERIAL-001',
        'name' => 'Equipo portátil de prueba',
        'brand' => 'Marca',
        'model' => 'Modelo',
    ]);

    Equipment::create([
        'category_id' => $projectors->id,
        'inventory_code' => 'PROY-TEST-001',
        'serial_number' => 'PROY-SERIAL-001',
        'name' => 'Proyector de prueba',
        'brand' => 'Marca',
        'model' => 'Modelo',
    ]);

    $this->get(route('public.catalog', ['category' => $laptops->code]))
        ->assertOk()
        ->assertSee('Equipo portátil de prueba')
        ->assertDontSee('Proyector de prueba');

    $this->get(route('public.catalog', ['category' => $projectors->id]))
        ->assertOk()
        ->assertSee('Proyector de prueba')
        ->assertDontSee('Equipo portátil de prueba');
});
