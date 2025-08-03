<?php

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can create a food', function () {
    $food = Food::factory()->create([
        'name' => 'Apple',
        'description' => 'Fresh apple',
        'carbs_per_100g' => 14.0,
        'calories_per_100g' => 52.0,
    ]);

    expect($food->name)->toBe('Apple');
    expect($food->carbs_per_100g)->toBe('14.00');
    expect($food->calories_per_100g)->toBe('52.00');
});

test('it has fillable attributes', function () {
    $food = new Food();
    
    expect($food->getFillable())->toContain('name');
    expect($food->getFillable())->toContain('description');
    expect($food->getFillable())->toContain('carbs_per_100g');
    expect($food->getFillable())->toContain('calories_per_100g');
});

test('it can calculate calories for given grams', function () {
    $food = Food::factory()->create([
        'calories_per_100g' => 100.0,
    ]);

    expect($food->calculateCalories(50))->toBe(50.0);
    expect($food->calculateCalories(200))->toBe(200.0);
    expect($food->calculateCalories(33.5))->toBe(33.5);
});

test('it can calculate carbs for given grams', function () {
    $food = Food::factory()->create([
        'carbs_per_100g' => 20.0,
    ]);

    expect($food->calculateCarbs(50))->toBe(10.0);
    expect($food->calculateCarbs(200))->toBe(40.0);
    expect($food->calculateCarbs(25))->toBe(5.0);
});

test('it can search foods by name', function () {
    Food::factory()->create(['name' => 'Apple']);
    Food::factory()->create(['name' => 'Banana']);
    Food::factory()->create(['name' => 'Orange']);

    $results = Food::search('app');
    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Apple');

    $results = Food::search('a');
    expect($results)->toHaveCount(3); // Apple, Banana, Orange all contain 'a'
});