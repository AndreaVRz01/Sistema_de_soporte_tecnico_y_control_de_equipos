<?php

use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('private equipment suggestions include fuzzy matches and remain role protected', function () {
    $category = Category::create(['name' => 'Computadoras', 'code' => 'LAP']);
    Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => 'LAP-PRIVATE-001',
        'serial_number' => 'LAP-PRIVATE-SERIAL',
        'name' => 'Laptop ThinkPad',
        'brand' => 'Lenovo',
        'model' => 'T14',
    ]);

    $this->actingAs(User::factory()->create(['role' => User::ROLE_USUARIO]))
        ->getJson(route('equipment.suggestions', ['q' => 'laptpo']))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
        ->getJson(route('equipment.suggestions', ['q' => 'laptpo']))
        ->assertOk()
        ->assertJsonPath('0.name', 'Laptop ThinkPad')
        ->assertJsonPath('0.is_similar', true);
});

test('private equipment list falls back to close matches when a search has a typo', function () {
    $category = Category::create(['name' => 'Computadoras', 'code' => 'LAP']);
    Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => 'LAP-PRIVATE-002',
        'serial_number' => 'LAP-PRIVATE-SERIAL-2',
        'name' => 'Laptop ThinkPad',
        'brand' => 'Lenovo',
        'model' => 'T14',
    ]);

    $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
        ->get(route('equipment.index', ['search' => 'laptpo']))
        ->assertOk()
        ->assertSee('Laptop ThinkPad')
        ->assertSee('mostramos activos similares');
});
