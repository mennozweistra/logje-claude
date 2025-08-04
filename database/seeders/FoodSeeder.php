<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foods = [
            // Fruits
            [
                'name' => 'Apple',
                'description' => 'Fresh apple with skin',
                'carbs_per_100g' => 14.0,
                'calories_per_100g' => 52.0,
            ],
            [
                'name' => 'Banana',
                'description' => 'Ripe banana',
                'carbs_per_100g' => 23.0,
                'calories_per_100g' => 89.0,
            ],
            [
                'name' => 'Orange',
                'description' => 'Fresh orange',
                'carbs_per_100g' => 12.0,
                'calories_per_100g' => 47.0,
            ],
            
            // Vegetables
            [
                'name' => 'Broccoli',
                'description' => 'Fresh broccoli',
                'carbs_per_100g' => 7.0,
                'calories_per_100g' => 34.0,
            ],
            [
                'name' => 'Carrot',
                'description' => 'Fresh carrot',
                'carbs_per_100g' => 10.0,
                'calories_per_100g' => 41.0,
            ],
            [
                'name' => 'Potato',
                'description' => 'Boiled potato',
                'carbs_per_100g' => 17.0,
                'calories_per_100g' => 77.0,
            ],
            
            // Grains & Bread
            [
                'name' => 'White Rice',
                'description' => 'Cooked white rice',
                'carbs_per_100g' => 28.0,
                'calories_per_100g' => 130.0,
            ],
            [
                'name' => 'Brown Rice',
                'description' => 'Cooked brown rice',
                'carbs_per_100g' => 23.0,
                'calories_per_100g' => 111.0,
            ],
            [
                'name' => 'White Bread',
                'description' => 'Slice of white bread',
                'carbs_per_100g' => 49.0,
                'calories_per_100g' => 265.0,
            ],
            [
                'name' => 'Whole Wheat Bread',
                'description' => 'Slice of whole wheat bread',
                'carbs_per_100g' => 43.0,
                'calories_per_100g' => 247.0,
            ],
            
            // Proteins
            [
                'name' => 'Chicken Breast',
                'description' => 'Grilled chicken breast, no skin',
                'carbs_per_100g' => 0.0,
                'calories_per_100g' => 165.0,
            ],
            [
                'name' => 'Salmon',
                'description' => 'Grilled salmon fillet',
                'carbs_per_100g' => 0.0,
                'calories_per_100g' => 208.0,
            ],
            [
                'name' => 'Eggs',
                'description' => 'Whole eggs, scrambled',
                'carbs_per_100g' => 1.1,
                'calories_per_100g' => 155.0,
            ],
            
            // Dairy
            [
                'name' => 'Milk',
                'description' => 'Whole milk',
                'carbs_per_100g' => 4.8,
                'calories_per_100g' => 61.0,
            ],
            [
                'name' => 'Greek Yogurt',
                'description' => 'Plain Greek yogurt',
                'carbs_per_100g' => 4.0,
                'calories_per_100g' => 59.0,
            ],
            [
                'name' => 'Cheddar Cheese',
                'description' => 'Cheddar cheese',
                'carbs_per_100g' => 1.3,
                'calories_per_100g' => 403.0,
            ],
            
            // Pasta
            [
                'name' => 'Spaghetti',
                'description' => 'Cooked spaghetti pasta',
                'carbs_per_100g' => 25.0,
                'calories_per_100g' => 131.0,
            ],
            [
                'name' => 'Penne',
                'description' => 'Cooked penne pasta',
                'carbs_per_100g' => 25.0,
                'calories_per_100g' => 131.0,
            ],
            
            // Nuts & Seeds
            [
                'name' => 'Almonds',
                'description' => 'Raw almonds',
                'carbs_per_100g' => 22.0,
                'calories_per_100g' => 579.0,
            ],
            [
                'name' => 'Walnuts',
                'description' => 'Raw walnuts',
                'carbs_per_100g' => 14.0,
                'calories_per_100g' => 654.0,
            ],
        ];

        // Get the first user to assign foods to (for development/testing)
        $firstUser = User::first();
        
        if (!$firstUser) {
            // No users exist, skip seeding foods
            return;
        }

        foreach ($foods as $food) {
            // Check if this user already has a food with this name
            $existingFood = DB::table('foods')
                ->where('name', $food['name'])
                ->where('user_id', $firstUser->id)
                ->first();
            
            if (!$existingFood) {
                $food['user_id'] = $firstUser->id;
                $food['created_at'] = now();
                $food['updated_at'] = now();
                DB::table('foods')->insert($food);
            }
        }
    }
}