<?php

use App\Models\Food;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;

uses(RefreshDatabase::class);

test('cascade delete removes food records when user is deleted', function () {
    $user = User::factory()->create();
    $food = Food::factory()->create(['user_id' => $user->id]);
    
    $foodId = $food->id;
    
    // Delete user should cascade to food records
    $user->delete();
    
    expect(Food::find($foodId))->toBeNull();
});

test('cascade delete removes medication records when user is deleted', function () {
    $user = User::factory()->create();
    $medication = Medication::factory()->create(['user_id' => $user->id]);
    
    $medicationId = $medication->id;
    
    // Delete user should cascade to medication records
    $user->delete();
    
    expect(Medication::find($medicationId))->toBeNull();
});

test('cascade delete works properly when user is deleted', function () {
    $user = User::factory()->create();
    $food = Food::factory()->create(['user_id' => $user->id]);
    $medication = Medication::factory()->create(['user_id' => $user->id]);
    
    $foodId = $food->id;
    $medicationId = $medication->id;
    
    // Force delete should cascade to foods and medications
    $user->forceDelete();
    
    expect(Food::find($foodId))->toBeNull();
    expect(Medication::find($medicationId))->toBeNull();
});

test('cannot create food without valid user_id', function () {
    expect(fn() => Food::create([
        'name' => 'Test Food',
        'description' => 'Test Description', 
        'carbs_per_100g' => 10.5,
        'calories_per_100g' => 150.0,
        'user_id' => 999999, // Non-existent user ID
    ]))->toThrow(QueryException::class);
});

test('cannot create medication without valid user_id', function () {
    expect(fn() => Medication::create([
        'name' => 'Test Medicine',
        'description' => 'Test Description',
        'user_id' => 999999, // Non-existent user ID
    ]))->toThrow(QueryException::class);
});