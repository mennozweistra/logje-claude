<?php

namespace Tests\Unit\Models;

use App\Models\Food;
use App\Models\FoodMeasurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--seed' => true]);
    }

    public function test_food_can_calculate_calories_correctly()
    {
        // Create a food with known nutritional values
        $food = Food::factory()->create([
            'calories_per_100g' => 250.50,
            'carbs_per_100g' => 30.25,
        ]);

        // Test various gram amounts
        $this->assertEquals(125.25, $food->calculateCalories(50.0));
        $this->assertEquals(250.50, $food->calculateCalories(100.0));
        $this->assertEquals(501.0, $food->calculateCalories(200.0));
        $this->assertEquals(0.0, $food->calculateCalories(0.0));
    }

    public function test_food_can_calculate_carbs_correctly()
    {
        // Create a food with known nutritional values
        $food = Food::factory()->create([
            'calories_per_100g' => 250.50,
            'carbs_per_100g' => 30.25,
        ]);

        // Test various gram amounts
        $this->assertEquals(15.13, $food->calculateCarbs(50.0));
        $this->assertEquals(30.25, $food->calculateCarbs(100.0));
        $this->assertEquals(60.5, $food->calculateCarbs(200.0));
        $this->assertEquals(0.0, $food->calculateCarbs(0.0));
    }

    public function test_food_calculation_methods_handle_decimals()
    {
        $food = Food::factory()->create([
            'calories_per_100g' => 123.45,
            'carbs_per_100g' => 67.89,
        ]);

        // Test decimal gram amounts
        $this->assertEquals(61.73, $food->calculateCalories(50.0));
        $this->assertEquals(33.95, $food->calculateCarbs(50.0));

        // Test fractional amounts
        $this->assertEquals(12.35, $food->calculateCalories(10.0));
        $this->assertEquals(6.79, $food->calculateCarbs(10.0));
    }

    public function test_food_belongs_to_user()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $food->user);
        $this->assertEquals($user->id, $food->user->id);
    }

    public function test_food_has_many_food_measurements()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $food = Food::factory()->create(['user_id' => $user->id]);
        
        // Create measurements first (needed for food measurements)
        $measurement1 = \App\Models\Measurement::factory()->create(['user_id' => $user->id]);
        $measurement2 = \App\Models\Measurement::factory()->create(['user_id' => $user->id]);
        
        // Create food measurements associated with this food
        $foodMeasurement1 = FoodMeasurement::factory()->create([
            'measurement_id' => $measurement1->id,
            'food_id' => $food->id,
        ]);
        $foodMeasurement2 = FoodMeasurement::factory()->create([
            'measurement_id' => $measurement2->id,
            'food_id' => $food->id,
        ]);

        $this->assertCount(2, $food->foodMeasurements);
        $this->assertTrue($food->foodMeasurements->contains($foodMeasurement1));
        $this->assertTrue($food->foodMeasurements->contains($foodMeasurement2));
    }

    public function test_food_search_finds_matching_foods()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create foods with different names
        Food::factory()->create(['name' => 'Apple Pie', 'user_id' => $user->id]);
        Food::factory()->create(['name' => 'Green Apple', 'user_id' => $user->id]);
        Food::factory()->create(['name' => 'Orange Juice', 'user_id' => $user->id]);
        Food::factory()->create(['name' => 'Banana Bread', 'user_id' => $user->id]);

        // Test partial matching
        $appleResults = Food::search('apple');
        $this->assertCount(2, $appleResults);
        $this->assertTrue($appleResults->pluck('name')->contains('Apple Pie'));
        $this->assertTrue($appleResults->pluck('name')->contains('Green Apple'));

        // Test case insensitive
        $bananaResults = Food::search('BANANA');
        $this->assertCount(1, $bananaResults);
        $this->assertTrue($bananaResults->pluck('name')->contains('Banana Bread'));

        // Test no matches
        $noResults = Food::search('chocolate');
        $this->assertCount(0, $noResults);
    }

    public function test_food_search_returns_alphabetically_ordered_results()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create foods that will match search in non-alphabetical creation order
        Food::factory()->create(['name' => 'Zebra Cake', 'user_id' => $user->id]);
        Food::factory()->create(['name' => 'Apple Cake', 'user_id' => $user->id]);
        Food::factory()->create(['name' => 'Banana Cake', 'user_id' => $user->id]);

        $results = Food::search('cake');
        
        $this->assertCount(3, $results);
        $this->assertEquals('Apple Cake', $results->first()->name);
        $this->assertEquals('Zebra Cake', $results->last()->name);
    }

    public function test_food_automatically_associates_with_authenticated_user_on_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $food = Food::factory()->create(['user_id' => null]); // Don't explicitly set user_id

        $this->assertEquals($user->id, $food->user_id);
    }

    public function test_food_global_scope_filters_by_authenticated_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create foods for different users
        $food1 = Food::factory()->create(['user_id' => $user1->id, 'name' => 'User 1 Food']);
        $food2 = Food::factory()->create(['user_id' => $user2->id, 'name' => 'User 2 Food']);

        // Test as user 1
        $this->actingAs($user1);
        $foods = Food::all();
        $this->assertCount(1, $foods);
        $this->assertEquals('User 1 Food', $foods->first()->name);

        // Test as user 2
        $this->actingAs($user2);
        $foods = Food::all();
        $this->assertCount(1, $foods);
        $this->assertEquals('User 2 Food', $foods->first()->name);
    }

    public function test_food_casts_nutritional_values_to_decimal()
    {
        $food = Food::factory()->create([
            'calories_per_100g' => '123.456789',
            'carbs_per_100g' => '67.891234',
        ]);

        // Values should be cast to decimal with 2 places
        $this->assertEquals('123.46', $food->calories_per_100g);
        $this->assertEquals('67.89', $food->carbs_per_100g);
    }

    public function test_food_fillable_attributes()
    {
        $user = User::factory()->create();
        
        $foodData = [
            'name' => 'Test Food',
            'description' => 'Test Description',
            'carbs_per_100g' => 25.50,
            'calories_per_100g' => 150.75,
            'user_id' => $user->id,
        ];

        $food = Food::create($foodData);

        $this->assertEquals('Test Food', $food->name);
        $this->assertEquals('Test Description', $food->description);
        $this->assertEquals('25.50', $food->carbs_per_100g);
        $this->assertEquals('150.75', $food->calories_per_100g);
        $this->assertEquals($user->id, $food->user_id);
    }
}