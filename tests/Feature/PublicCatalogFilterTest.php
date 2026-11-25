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

test('public catalog only lists available equipment and counts available items by category', function () {
    $laptops = Category::create(['name' => 'Laptops', 'code' => 'LAP']);

    Equipment::create([
        'category_id' => $laptops->id,
        'inventory_code' => 'LAP-AVAILABLE-001',
        'serial_number' => 'LAP-SERIAL-AVAILABLE',
        'name' => 'Laptop disponible',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'status' => 'disponible',
    ]);

    Equipment::create([
        'category_id' => $laptops->id,
        'inventory_code' => 'LAP-LOANED-001',
        'serial_number' => 'LAP-SERIAL-LOANED',
        'name' => 'Laptop prestada',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'status' => 'prestado',
    ]);

    $this->get(route('public.catalog', ['category' => $laptops->code]))
        ->assertOk()
        ->assertSee('Laptop disponible')
        ->assertDontSee('Laptop prestada')
        ->assertSee('Catálogo institucional')
        ->assertSee('1 resultado');
});

test('public catalog suggestions match category names and tolerate transposed letters', function () {
    $category = Category::create(['name' => 'Computadoras portátiles', 'code' => 'LAP']);

    Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => 'LAP-SUGGEST-001',
        'serial_number' => 'LAP-SUGGEST-SERIAL',
        'name' => 'Laptop ThinkPad',
        'brand' => 'Lenovo',
        'model' => 'T14',
    ]);

    $this->getJson(route('public.catalog.suggestions', ['q' => 'computadoras']))
        ->assertOk()
        ->assertJsonPath('0.name', 'Laptop ThinkPad')
        ->assertJsonPath('0.is_similar', false);

    $this->getJson(route('public.catalog.suggestions', ['q' => 'laptpo']))
        ->assertOk()
        ->assertJsonPath('0.name', 'Laptop ThinkPad')
        ->assertJsonPath('0.is_similar', true);
});

test('public catalog displays nearby results when the search has a typo', function () {
    $category = Category::create(['name' => 'Computadoras', 'code' => 'LAP']);

    Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => 'LAP-FUZZY-001',
        'serial_number' => 'LAP-FUZZY-SERIAL',
        'name' => 'Laptop ThinkPad',
        'brand' => 'Lenovo',
        'model' => 'T14',
    ]);

    $this->get(route('public.catalog', ['q' => 'laptpo']))
        ->assertOk()
        ->assertSee('Laptop ThinkPad')
        ->assertSee('mostramos equipos similares');
});
