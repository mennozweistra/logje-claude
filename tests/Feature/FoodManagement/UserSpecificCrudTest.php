<?php

use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
});

test('users can only see their own foods in management interface', function () {
    // Create foods for different users
    $user1Food = Food::factory()->create(['user_id' => $this->user1->id, 'name' => 'User 1 Food']);
    $user2Food = Food::factory()->create(['user_id' => $this->user2->id, 'name' => 'User 2 Food']);
    
    // User 1 should only see their food
    $this->actingAs($this->user1);
    $foods = Food::all();
    expect($foods)->toHaveCount(1);
    expect($foods->first()->name)->toBe('User 1 Food');
    
    // User 2 should only see their food
    $this->actingAs($this->user2);
    $foods = Food::all();
    expect($foods)->toHaveCount(1);
    expect($foods->first()->name)->toBe('User 2 Food');
});

test('food creation associates with correct user automatically', function () {
    $this->actingAs($this->user1);
    
    $food = Food::create([
        'name' => 'Test Food',
        'description' => 'Test Description',
        'carbs_per_100g' => 10.5,
        'calories_per_100g' => 150.0,
    ]);
    
    expect($food->user_id)->toBe($this->user1->id);
    
    // Verify user 2 cannot see this food
    $this->actingAs($this->user2);
    expect(Food::find($food->id))->toBeNull();
});

test('food editing only works for owned foods', function () {
    $user1Food = Food::factory()->create(['user_id' => $this->user1->id, 'name' => 'User 1 Food']);
    $user2Food = Food::factory()->create(['user_id' => $this->user2->id, 'name' => 'User 2 Food']);
    
    // User 1 can edit their own food
    $this->actingAs($this->user1);
    $found = Food::find($user1Food->id);
    expect($found)->not->toBeNull();
    expect($found->name)->toBe('User 1 Food');
    
    // User 1 cannot access user 2's food
    $notFound = Food::find($user2Food->id);
    expect($notFound)->toBeNull();
    
    // User 2 can edit their own food
    $this->actingAs($this->user2);
    $found = Food::find($user2Food->id);
    expect($found)->not->toBeNull();
    expect($found->name)->toBe('User 2 Food');
    
    // User 2 cannot access user 1's food
    $notFound = Food::find($user1Food->id);
    expect($notFound)->toBeNull();
});

test('duplicate food names allowed across different users', function () {
    // Both users can have foods with the same name
    $user1Food = Food::factory()->create([
        'user_id' => $this->user1->id, 
        'name' => 'Apple'
    ]);
    
    $user2Food = Food::factory()->create([
        'user_id' => $this->user2->id, 
        'name' => 'Apple'
    ]);
    
    expect($user1Food->name)->toBe('Apple');
    expect($user2Food->name)->toBe('Apple');
    expect($user1Food->id)->not->toBe($user2Food->id);
    
    // Each user sees only their own apple
    $this->actingAs($this->user1);
    $user1Foods = Food::where('name', 'Apple')->get();
    expect($user1Foods)->toHaveCount(1);
    expect($user1Foods->first()->id)->toBe($user1Food->id);
    
    $this->actingAs($this->user2);
    $user2Foods = Food::where('name', 'Apple')->get();
    expect($user2Foods)->toHaveCount(1);
    expect($user2Foods->first()->id)->toBe($user2Food->id);
});

test('food search respects user scoping', function () {
    Food::factory()->create(['user_id' => $this->user1->id, 'name' => 'Apple Juice']);
    Food::factory()->create(['user_id' => $this->user2->id, 'name' => 'Apple Pie']);
    
    $this->actingAs($this->user1);
    $results = Food::search('Apple');
    expect($results)->toHaveCount(1);
    expect($results->first()['name'])->toBe('Apple Juice');
    
    $this->actingAs($this->user2);
    $results = Food::search('Apple');
    expect($results)->toHaveCount(1);
    expect($results->first()['name'])->toBe('Apple Pie');
});