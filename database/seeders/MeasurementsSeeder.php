<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MeasurementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $measurements = [];
        $today = Carbon::today();
        
        // Get user IDs
        $testUser = DB::table('users')->where('email', 'test@example.com')->value('id');
        $sarahUser = DB::table('users')->where('email', 'sarah@example.com')->value('id');
        
        if (!$testUser || !$sarahUser) {
            $this->command->error('Required users not found. Please run UserSeeder first.');
            return;
        }
        
        // Get measurement type IDs
        $glucoseTypeId = DB::table('measurement_types')->where('slug', 'glucose')->value('id');
        $weightTypeId = DB::table('measurement_types')->where('slug', 'weight')->value('id');
        $exerciseTypeId = DB::table('measurement_types')->where('slug', 'exercise')->value('id');
        $notesTypeId = DB::table('measurement_types')->where('slug', 'notes')->value('id');
        
        // Create measurements for the last 7 days for test user
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            
            // Morning glucose reading
            $measurements[] = [
                'user_id' => $testUser,
                'measurement_type_id' => $glucoseTypeId,
                'value' => rand(90, 120),
                'is_fasting' => true,
                'description' => null,
                'duration' => null,
                'date' => $date,
                'notes' => $i === 0 ? 'Feeling good today' : null,
                'created_at' => $date->copy()->addHours(7),
                'updated_at' => $date->copy()->addHours(7),
            ];
            
            // Evening weight
            $measurements[] = [
                'user_id' => $testUser,
                'measurement_type_id' => $weightTypeId,
                'value' => round(75.0 + (rand(-10, 10) / 10), 1),
                'is_fasting' => null,
                'description' => null,
                'duration' => null,
                'date' => $date,
                'notes' => null,
                'created_at' => $date->copy()->addHours(19),
                'updated_at' => $date->copy()->addHours(19),
            ];
            
            // Exercise (skip some days randomly)
            if (rand(0, 100) > 30) {
                $exercises = [
                    ['desc' => 'Morning jog', 'duration' => rand(20, 45)],
                    ['desc' => 'Yoga session', 'duration' => rand(15, 30)],
                    ['desc' => 'Weight training', 'duration' => rand(45, 90)],
                    ['desc' => 'Bike ride', 'duration' => rand(30, 60)],
                ];
                $exercise = $exercises[array_rand($exercises)];
                
                $measurements[] = [
                    'user_id' => $testUser,
                    'measurement_type_id' => $exerciseTypeId,
                    'value' => null,
                    'is_fasting' => null,
                    'description' => $exercise['desc'],
                    'duration' => $exercise['duration'],
                    'date' => $date,
                    'notes' => rand(0, 100) > 50 ? 'Felt energetic' : null,
                    'created_at' => $date->copy()->addHours(rand(9, 17)),
                    'updated_at' => $date->copy()->addHours(rand(9, 17)),
                ];
            }
            
            // Daily notes (occasionally)
            if (rand(0, 100) > 60) {
                $notes = [
                    'Had a great day overall',
                    'Feeling a bit tired today',
                    'Energy levels were high',
                    'Slept well last night',
                    'Stress levels manageable',
                ];
                
                $measurements[] = [
                    'user_id' => $testUser,
                    'measurement_type_id' => $notesTypeId,
                    'value' => null,
                    'is_fasting' => null,
                    'description' => null,
                    'duration' => null,
                    'date' => $date,
                    'notes' => $notes[array_rand($notes)],
                    'created_at' => $date->copy()->addHours(21),
                    'updated_at' => $date->copy()->addHours(21),
                ];
            }
        }
        
        // Add some measurements for user 2 (Sarah Johnson) for today
        $measurements[] = [
            'user_id' => $sarahUser,
            'measurement_type_id' => $glucoseTypeId,
            'value' => 105,
            'is_fasting' => true,
            'description' => null,
            'duration' => null,
            'date' => $today,
            'notes' => 'Morning reading',
            'created_at' => $today->copy()->addHours(8),
            'updated_at' => $today->copy()->addHours(8),
        ];
        
        $measurements[] = [
            'user_id' => $sarahUser,
            'measurement_type_id' => $exerciseTypeId,
            'value' => null,
            'is_fasting' => null,
            'description' => 'Pilates class',
            'duration' => 60,
            'date' => $today,
            'notes' => 'Great instructor',
            'created_at' => $today->copy()->addHours(10),
            'updated_at' => $today->copy()->addHours(10),
        ];
        
        DB::table('measurements')->insert($measurements);
    }
}
