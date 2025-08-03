<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medications = [
            [
                'name' => 'Rybelsus',
                'description' => 'Oral semaglutide for type 2 diabetes management',
            ],
            [
                'name' => 'Metformine',
                'description' => 'Biguanide medication for type 2 diabetes',
            ],
            [
                'name' => 'Amlodipine',
                'description' => 'Calcium channel blocker for high blood pressure',
            ],
            [
                'name' => 'Kaliumlosartan',
                'description' => 'ARB medication for blood pressure control',
            ],
            [
                'name' => 'Atorvastatine',
                'description' => 'Statin for cholesterol management',
            ],
        ];

        foreach ($medications as $medicationData) {
            Medication::firstOrCreate(
                ['name' => $medicationData['name']],
                $medicationData
            );
        }
    }
}
