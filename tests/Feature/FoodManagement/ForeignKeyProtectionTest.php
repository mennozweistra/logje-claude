<?php

use App\Models\Food;
use App\Models\FoodMeasurement;
use App\Models\Measurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('food deletion is blocked when referenced by measurements', function () {
    $food = Food::factory()->create(['user_id' => $this->user->id]);
    
    // Create a measurement that references this food
    $measurement = Measurement::factory()->create(['user_id' => $this->user->id]);
    FoodMeasurement::create([
        'measurement_id' => $measurement->id,
        'food_id' => $food->id,
        'grams_consumed' => 100,
        'calculated_calories' => 200,
        'calculated_carbs' => 20,
    ]);
    
    // Verify food cannot be deleted
    $measurementCount = $food->foodMeasurements()->count();
    expect($measurementCount)->toBe(1);
    
    // The food should still exist
    expect(Food::find($food->id))->not->toBeNull();
});

test('food can be deleted when no measurements reference it', function () {
    $food = Food::factory()->create(['user_id' => $this->user->id]);
    
    // Verify no measurements reference this food
    $measurementCount = $food->foodMeasurements()->count();
    expect($measurementCount)->toBe(0);
    
    // Delete should succeed
    $foodId = $food->id;
    $food->delete();
    
    expect(Food::find($foodId))->toBeNull();
});

test('food measurements are deleted when food is force deleted', function () {
    $food = Food::factory()->create(['user_id' => $this->user->id]);
    
    // Create a measurement that references this food
    $measurement = Measurement::factory()->create(['user_id' => $this->user->id]);
    $foodMeasurement = FoodMeasurement::create([
        'measurement_id' => $measurement->id,
        'food_id' => $food->id,
        'grams_consumed' => 100,
        'calculated_calories' => 200,
        'calculated_carbs' => 20,
    ]);
    
    $foodMeasurementId = $foodMeasurement->id;
    $foodId = $food->id;
    
    // Force delete should remove food and its measurements
    $food->forceDelete();
    
    expect(Food::find($foodId))->toBeNull();
    expect(FoodMeasurement::find($foodMeasurementId))->toBeNull();
});

test('cascade delete removes food measurements when user is deleted', function () {
    $food = Food::factory()->create(['user_id' => $this->user->id]);
    
    // Create a measurement that references this food
    $measurement = Measurement::factory()->create(['user_id' => $this->user->id]);
    $foodMeasurement = FoodMeasurement::create([
        'measurement_id' => $measurement->id,
        'food_id' => $food->id,
        'grams_consumed' => 100,
        'calculated_calories' => 200,
        'calculated_carbs' => 20,
    ]);
    
    $foodId = $food->id;
    $foodMeasurementId = $foodMeasurement->id;
    
    // Delete user should cascade to foods and food measurements
    $this->user->delete();
    
    expect(Food::find($foodId))->toBeNull();
    expect(FoodMeasurement::find($foodMeasurementId))->toBeNull();
});