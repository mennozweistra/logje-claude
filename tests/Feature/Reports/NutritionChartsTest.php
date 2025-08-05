<?php

namespace Tests\Feature\Reports;

use App\Models\Food;
use App\Models\FoodMeasurement;
use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionChartsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--seed' => true]);
    }

    public function test_nutrition_data_endpoint_requires_authentication()
    {
        $response = $this->get('/reports/nutrition-data');
        
        $response->assertRedirect('/login');
    }

    public function test_nutrition_data_endpoint_returns_valid_json_structure()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/reports/nutrition-data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'dailyCalories',
            'dailyCarbs'
        ]);
    }

    public function test_nutrition_data_calculates_daily_totals_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create foods with known nutritional values
        $apple = Food::factory()->create([
            'name' => 'Apple',
            'calories_per_100g' => 52.0,
            'carbs_per_100g' => 14.0,
            'user_id' => $user->id,
        ]);

        $banana = Food::factory()->create([
            'name' => 'Banana',
            'calories_per_100g' => 89.0,
            'carbs_per_100g' => 23.0,
            'user_id' => $user->id,
        ]);

        // Create food measurement type
        $foodType = MeasurementType::where('name', 'food')->first();

        // Create measurements for today
        $today = Carbon::today();
        $measurement1 = Measurement::factory()->create([
            'user_id' => $user->id,
            'measurement_type_id' => $foodType->id,
            'date' => $today,
            'created_at' => $today,
        ]);

        $measurement2 = Measurement::factory()->create([
            'user_id' => $user->id,
            'measurement_type_id' => $foodType->id,
            'date' => $today,
            'created_at' => $today,
        ]);

        // Create food measurements: 200g apple (104 cal, 28g carbs) + 150g banana (133.5 cal, 34.5g carbs)
        FoodMeasurement::factory()->create([
            'measurement_id' => $measurement1->id,
            'food_id' => $apple->id,
            'grams_consumed' => 200,
            'calculated_calories' => 104.0,
            'calculated_carbs' => 28.0,
        ]);

        FoodMeasurement::factory()->create([
            'measurement_id' => $measurement2->id,
            'food_id' => $banana->id,
            'grams_consumed' => 150,
            'calculated_calories' => 133.5,
            'calculated_carbs' => 34.5,
        ]);

        $response = $this->get('/reports/nutrition-data');

        $response->assertStatus(200);
        $data = $response->json();

        // Should have one day of data with totals: 237.5 calories, 62.5 carbs
        $this->assertCount(1, $data['dailyCalories']);
        $this->assertCount(1, $data['dailyCarbs']);

        $todayString = $today->format('Y-m-d');
        $this->assertEquals($todayString, $data['dailyCalories'][0]['x']);
        $this->assertEquals(238, $data['dailyCalories'][0]['y']); // Rounded to nearest integer
        $this->assertEquals($todayString, $data['dailyCarbs'][0]['x']);
        $this->assertEquals(62.5, $data['dailyCarbs'][0]['y']); // Rounded to 1 decimal
    }

    public function test_nutrition_data_respects_date_range_parameters()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $apple = Food::factory()->create([
            'calories_per_100g' => 52.0,
            'carbs_per_100g' => 14.0,
            'user_id' => $user->id,
        ]);

        $foodType = MeasurementType::where('name', 'food')->first();

        // Create measurements for different dates
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();
        $weekAgo = $today->copy()->subWeek();

        foreach ([$today, $yesterday, $weekAgo] as $date) {
            $measurement = Measurement::factory()->create([
                'user_id' => $user->id,
                'measurement_type_id' => $foodType->id,
                'date' => $date,
                'created_at' => $date,
            ]);

            FoodMeasurement::factory()->create([
                'measurement_id' => $measurement->id,
                'food_id' => $apple->id,
                'grams_consumed' => 100,
                'calculated_calories' => 52.0,
                'calculated_carbs' => 14.0,
            ]);
        }

        // Test with specific date range (last 3 days)
        $startDate = $yesterday->format('Y-m-d');
        $endDate = $today->format('Y-m-d');
        
        $response = $this->get("/reports/nutrition-data?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $data = $response->json();

        // Should have 2 days of data (yesterday and today), not week ago
        $this->assertCount(2, $data['dailyCalories']);
        $this->assertCount(2, $data['dailyCarbs']);
    }

    public function test_nutrition_data_handles_days_parameter()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test default days parameter
        $response = $this->get('/reports/nutrition-data?days=7');
        
        $response->assertStatus(200);
        $data = $response->json();
        
        // Should return data structure even with no measurements
        $this->assertIsArray($data['dailyCalories']);
        $this->assertIsArray($data['dailyCarbs']);
    }

    public function test_nutrition_data_validates_parameters()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test invalid days parameter - Laravel redirects with validation errors for GET requests
        $response = $this->get('/reports/nutrition-data?days=500');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('days');

        // Test invalid date range
        $response = $this->get('/reports/nutrition-data?start_date=2024-12-31&end_date=2024-01-01');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('end_date');

        // Test invalid date format
        $response = $this->get('/reports/nutrition-data?start_date=invalid-date');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('start_date');
    }

    public function test_nutrition_data_handles_empty_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/reports/nutrition-data');

        $response->assertStatus(200);
        $data = $response->json();

        // Should return empty arrays when no food measurements exist
        $this->assertIsArray($data['dailyCalories']);
        $this->assertIsArray($data['dailyCarbs']);
        $this->assertEmpty($data['dailyCalories']);
        $this->assertEmpty($data['dailyCarbs']);
    }

    public function test_nutrition_data_aggregates_multiple_foods_per_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $apple = Food::factory()->create([
            'calories_per_100g' => 50.0,
            'carbs_per_100g' => 10.0,
            'user_id' => $user->id,
        ]);

        $banana = Food::factory()->create([
            'calories_per_100g' => 100.0,
            'carbs_per_100g' => 20.0,
            'user_id' => $user->id,
        ]);

        $foodType = MeasurementType::where('name', 'food')->first();
        $today = Carbon::today();

        // Create multiple measurements for the same day
        for ($i = 0; $i < 3; $i++) {
            $measurement = Measurement::factory()->create([
                'user_id' => $user->id,
                'measurement_type_id' => $foodType->id,
                'date' => $today,
                'created_at' => $today,
            ]);

            FoodMeasurement::factory()->create([
                'measurement_id' => $measurement->id,
                'food_id' => $apple->id,
                'grams_consumed' => 100,
                'calculated_calories' => 50.0,
                'calculated_carbs' => 10.0,
            ]);

            FoodMeasurement::factory()->create([
                'measurement_id' => $measurement->id,
                'food_id' => $banana->id,
                'grams_consumed' => 100,
                'calculated_calories' => 100.0,
                'calculated_carbs' => 20.0,
            ]);
        }

        $response = $this->get('/reports/nutrition-data');

        $response->assertStatus(200);
        $data = $response->json();

        // Should aggregate all foods for the day: 3 * (50 + 100) = 450 calories, 3 * (10 + 20) = 90 carbs
        $this->assertCount(1, $data['dailyCalories']);
        $this->assertEquals(450, $data['dailyCalories'][0]['y']);
        $this->assertEquals(90.0, $data['dailyCarbs'][0]['y']);
    }

    public function test_nutrition_data_only_includes_user_measurements()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create foods for each user
        $food1 = Food::factory()->create(['user_id' => $user1->id, 'calories_per_100g' => 100.0, 'carbs_per_100g' => 20.0]);
        $food2 = Food::factory()->create(['user_id' => $user2->id, 'calories_per_100g' => 200.0, 'carbs_per_100g' => 40.0]);

        $foodType = MeasurementType::where('name', 'food')->first();
        $today = Carbon::today();

        // Create measurements for both users
        foreach ([$user1, $user2] as $index => $user) {
            $measurement = Measurement::factory()->create([
                'user_id' => $user->id,
                'measurement_type_id' => $foodType->id,
                'date' => $today,
                'created_at' => $today,
            ]);

            $food = $index === 0 ? $food1 : $food2;
            FoodMeasurement::factory()->create([
                'measurement_id' => $measurement->id,
                'food_id' => $food->id,
                'grams_consumed' => 100,
                'calculated_calories' => $food->calories_per_100g,
                'calculated_carbs' => $food->carbs_per_100g,
            ]);
        }

        // Test as user1
        $this->actingAs($user1);
        $response = $this->get('/reports/nutrition-data');
        $data = $response->json();

        // Should only see user1's data (100 calories, 20 carbs)
        $this->assertCount(1, $data['dailyCalories']);
        $this->assertEquals(100, $data['dailyCalories'][0]['y']);
        $this->assertEquals(20.0, $data['dailyCarbs'][0]['y']);

        // Test as user2
        $this->actingAs($user2);
        $response = $this->get('/reports/nutrition-data');
        $data = $response->json();

        // Should only see user2's data (200 calories, 40 carbs)
        $this->assertCount(1, $data['dailyCalories']);
        $this->assertEquals(200, $data['dailyCalories'][0]['y']);
        $this->assertEquals(40.0, $data['dailyCarbs'][0]['y']);
    }
}