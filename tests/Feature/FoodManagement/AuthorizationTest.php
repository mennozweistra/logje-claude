<?php

use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
});

test('user cannot access another users food via direct ID access', function () {
    $user2Food = Food::factory()->create(['user_id' => $this->user2->id]);
    
    // Acting as user 1, should not be able to find user 2's food
    $this->actingAs($this->user1);
    
    $found = Food::find($user2Food->id);
    expect($found)->toBeNull();
});

test('user can only query their own foods', function () {
    Food::factory()->count(3)->create(['user_id' => $this->user1->id]);
    Food::factory()->count(2)->create(['user_id' => $this->user2->id]);
    
    // User 1 should only see their 3 foods
    $this->actingAs($this->user1);
    expect(Food::all())->toHaveCount(3);
    expect(Food::count())->toBe(3);
    
    // User 2 should only see their 2 foods
    $this->actingAs($this->user2);
    expect(Food::all())->toHaveCount(2);
    expect(Food::count())->toBe(2);
});

test('user cannot update another users food', function () {
    $user2Food = Food::factory()->create([
        'user_id' => $this->user2->id,
        'name' => 'Original Name'
    ]);
    
    $this->actingAs($this->user1);
    
    // User 1 cannot find user 2's food to update it
    $found = Food::find($user2Food->id);
    expect($found)->toBeNull();
    
    // Verify the original food still exists with original name
    $this->actingAs($this->user2);
    $original = Food::find($user2Food->id);
    expect($original)->not->toBeNull();
    expect($original->name)->toBe('Original Name');
});

test('user cannot delete another users food', function () {
    $user2Food = Food::factory()->create(['user_id' => $this->user2->id]);
    
    $this->actingAs($this->user1);
    
    // User 1 cannot find user 2's food to delete it
    $found = Food::find($user2Food->id);
    expect($found)->toBeNull();
    
    // Verify the food still exists
    $this->actingAs($this->user2);
    $stillExists = Food::find($user2Food->id);
    expect($stillExists)->not->toBeNull();
});

test('food queries automatically filter by authenticated user', function () {
    Food::factory()->create(['user_id' => $this->user1->id, 'name' => 'User1 Apple']);
    Food::factory()->create(['user_id' => $this->user2->id, 'name' => 'User2 Apple']);
    
    // User 1's queries are automatically scoped
    $this->actingAs($this->user1);
    $apple = Food::where('name', 'like', '%Apple%')->first();
    expect($apple->name)->toBe('User1 Apple');
    
    // User 2's queries are automatically scoped
    $this->actingAs($this->user2);
    $apple = Food::where('name', 'like', '%Apple%')->first();
    expect($apple->name)->toBe('User2 Apple');
});

test('unauthenticated access returns all foods', function () {
    Food::factory()->count(5)->create(['user_id' => $this->user1->id]);
    
    // No authenticated user - global scope doesn't apply, should return all foods
    auth()->logout();
    expect(Food::all())->toHaveCount(5);
    expect(Food::count())->toBe(5);
});