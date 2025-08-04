<?php

use App\Models\Food;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
});

test('foods table has user_id foreign key column', function () {
    $food = Food::factory()->create(['user_id' => $this->user1->id]);
    
    expect($food->user_id)->toBe($this->user1->id);
    expect($food->user)->toBeInstanceOf(User::class);
    expect($food->user->id)->toBe($this->user1->id);
});

test('medications table has user_id foreign key column', function () {
    $medication = Medication::factory()->create(['user_id' => $this->user1->id]);
    
    expect($medication->user_id)->toBe($this->user1->id);
    expect($medication->user)->toBeInstanceOf(User::class);
    expect($medication->user->id)->toBe($this->user1->id);
});

test('user relationships work correctly', function () {
    Food::factory()->count(2)->create(['user_id' => $this->user1->id]);
    Medication::factory()->count(3)->create(['user_id' => $this->user1->id]);
    
    expect($this->user1->fresh()->foods)->toHaveCount(2);
    expect($this->user1->fresh()->medications)->toHaveCount(3);
});

test('global scope filters foods by authenticated user', function () {
    // Create foods for different users
    Food::factory()->create(['user_id' => $this->user1->id, 'name' => 'User 1 Food']);
    Food::factory()->create(['user_id' => $this->user2->id, 'name' => 'User 2 Food']);
    
    // Act as user 1
    $this->actingAs($this->user1);
    
    $foods = Food::all();
    expect($foods)->toHaveCount(1);
    expect($foods->first()->name)->toBe('User 1 Food');
    
    // Act as user 2
    $this->actingAs($this->user2);
    
    $foods = Food::all();
    expect($foods)->toHaveCount(1);
    expect($foods->first()->name)->toBe('User 2 Food');
});

test('global scope filters medications by authenticated user', function () {
    // Create medications for different users
    Medication::factory()->create(['user_id' => $this->user1->id, 'name' => 'User 1 Med']);
    Medication::factory()->create(['user_id' => $this->user2->id, 'name' => 'User 2 Med']);
    
    // Act as user 1
    $this->actingAs($this->user1);
    
    $medications = Medication::all();
    expect($medications)->toHaveCount(1);
    expect($medications->first()->name)->toBe('User 1 Med');
    
    // Act as user 2
    $this->actingAs($this->user2);
    
    $medications = Medication::all();
    expect($medications)->toHaveCount(1);
    expect($medications->first()->name)->toBe('User 2 Med');
});

test('automatic user_id assignment on creation', function () {
    $this->actingAs($this->user1);
    
    $food = Food::create([
        'name' => 'Test Food',
        'description' => 'Test Description',
        'carbs_per_100g' => 10.5,
        'calories_per_100g' => 150.0,
    ]);
    
    expect($food->user_id)->toBe($this->user1->id);
    
    $medication = Medication::create([
        'name' => 'Test Medicine',
        'description' => 'Test Description',
    ]);
    
    expect($medication->user_id)->toBe($this->user1->id);
});

test('search method respects user scoping', function () {
    // Create foods for different users
    Food::factory()->create(['user_id' => $this->user1->id, 'name' => 'Apple Juice']);
    Food::factory()->create(['user_id' => $this->user2->id, 'name' => 'Apple Pie']);
    
    $this->actingAs($this->user1);
    
    $results = Food::search('Apple');
    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Apple Juice');
});