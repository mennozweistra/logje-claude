<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user to associate medications with
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No users found. Please create a user first before seeding medications.');
            return;
        }

        $medications = [
            [
                'name' => 'Rybelsus',
                'description' => 'Oral semaglutide for type 2 diabetes management',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Metformine',
                'description' => 'Biguanide medication for type 2 diabetes',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Amlodipine',
                'description' => 'Calcium channel blocker for high blood pressure',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Kaliumlosartan',
                'description' => 'ARB medication for blood pressure control',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Atorvastatine',
                'description' => 'Statin for cholesterol management',
                'user_id' => $user->id,
            ],
        ];

        foreach ($medications as $medicationData) {
            Medication::firstOrCreate(
                [
                    'name' => $medicationData['name'],
                    'user_id' => $medicationData['user_id']
                ],
                $medicationData
            );
        }

        $this->command->info("Created " . count($medications) . " medications for user: {$user->email}");
    }
}
