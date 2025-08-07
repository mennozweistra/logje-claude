<?php

namespace Tests\Unit\Services;

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\Medication;
use App\Models\User;
use App\Services\HealthyDayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthyDayServiceTest extends TestCase
{
    use RefreshDatabase;

    private HealthyDayService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new HealthyDayService();
        $this->user = User::factory()->create();
        
        // Get or create measurement types
        MeasurementType::firstOrCreate(
            ['slug' => 'glucose'],
            ['name' => 'Glucose', 'unit' => 'mmol/L']
        );
        MeasurementType::firstOrCreate(
            ['slug' => 'exercise'], 
            ['name' => 'Exercise', 'unit' => 'minutes']
        );
        MeasurementType::firstOrCreate(
            ['slug' => 'medication'],
            ['name' => 'Medication', 'unit' => null]
        );
        
        // Create medications for this specific user
        Medication::create(['name' => 'Rybelsus', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Metformine', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Amlodipine', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Kaliumlosartan', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Atorvastatine', 'user_id' => $this->user->id]);
    }

    public function test_medication_counting_single_occurrence()
    {
        $date = Carbon::today();
        
        // Add Rybelsus medication once
        $measurement = $this->createMedicationMeasurement($date, ['Rybelsus']);
        
        $statuses = $this->service->getRuleStatuses($this->user, $date);
        
        $this->assertTrue($statuses['rybelsus_morning']['met']);
    }

    public function test_medication_counting_double_metformine()
    {
        $date = Carbon::today();
        
        // Add Metformine twice
        $this->createMedicationMeasurement($date, ['Metformine', 'Amlodipine', 'Kaliumlosartan']);
        $this->createMedicationMeasurement($date->copy()->addHours(8), ['Metformine', 'Atorvastatine']);
        
        $statuses = $this->service->getRuleStatuses($this->user, $date);
        
        $this->assertTrue($statuses['medications_morning']['met']);
        $this->assertTrue($statuses['medications_evening']['met']);
    }

    public function test_glucose_measurement_counting_with_fasting()
    {
        $date = Carbon::today();
        $glucoseType = MeasurementType::where('slug', 'glucose')->first();
        
        // Add fasting glucose
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $glucoseType->id,
            'value' => 5.2,
            'is_fasting' => true,
            'date' => $date,
            'created_at' => $date->copy()->addHours(9)
        ]);
        
        // Add second glucose (non-fasting)
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $glucoseType->id,
            'value' => 7.1,
            'is_fasting' => false,
            'date' => $date,
            'created_at' => $date->copy()->addHours(14)
        ]);
        
        // Add third glucose
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $glucoseType->id,
            'value' => 6.8,
            'is_fasting' => false,
            'date' => $date,
            'created_at' => $date->copy()->addHours(19)
        ]);
        
        $statuses = $this->service->getRuleStatuses($this->user, $date);
        
        $this->assertTrue($statuses['glucose_fasting']['met']);
        $this->assertTrue($statuses['glucose_second']['met']);
        $this->assertTrue($statuses['glucose_third']['met']);
    }

    public function test_exercise_activity_checking()
    {
        $date = Carbon::today();
        $exerciseType = MeasurementType::where('slug', 'exercise')->first();
        
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $exerciseType->id,
            'description' => 'Walking',
            'duration' => 30,
            'date' => $date,
            'created_at' => $date->copy()->addHours(15)
        ]);
        
        $statuses = $this->service->getRuleStatuses($this->user, $date);
        
        $this->assertTrue($statuses['exercise']['met']);
    }

    public function test_time_based_rule_activation()
    {
        $date = Carbon::today();
        
        // Mock current time to 10:00 (only 09:00 rules should be active)
        Carbon::setTestNow($date->copy()->setTime(10, 0));
        
        $statuses = $this->service->getRuleStatuses($this->user, $date);
        
        // 09:00 rules should be active
        $this->assertTrue($statuses['rybelsus_morning']['active']);
        $this->assertTrue($statuses['glucose_fasting']['active']);
        
        // 11:00+ rules should be inactive
        $this->assertFalse($statuses['medications_morning']['active']);
        $this->assertFalse($statuses['glucose_second']['active']);
        $this->assertFalse($statuses['exercise']['active']);
        $this->assertFalse($statuses['glucose_third']['active']);
        $this->assertFalse($statuses['medications_evening']['active']);
        
        Carbon::setTestNow(); // Reset
    }

    public function test_complete_rule_evaluation_for_past_dates()
    {
        $pastDate = Carbon::yesterday();
        
        $statuses = $this->service->getRuleStatuses($this->user, $pastDate);
        
        // All rules should be active for past dates
        foreach ($statuses as $status) {
            $this->assertTrue($status['active']);
        }
    }

    public function test_partial_rule_evaluation_for_current_day()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(12, 0)); // Noon
        
        $statuses = $this->service->getRuleStatuses($this->user, $today);
        
        // Rules up to 11:00 should be active
        $this->assertTrue($statuses['rybelsus_morning']['active']);
        $this->assertTrue($statuses['glucose_fasting']['active']);
        $this->assertTrue($statuses['medications_morning']['active']);
        
        // Rules after 12:00 should be inactive
        $this->assertFalse($statuses['glucose_second']['active']);
        $this->assertFalse($statuses['exercise']['active']);
        $this->assertFalse($statuses['glucose_third']['active']);
        $this->assertFalse($statuses['medications_evening']['active']);
        
        Carbon::setTestNow(); // Reset
    }

    public function test_healthy_day_with_no_measurements()
    {
        $date = Carbon::today();
        
        $isHealthy = $this->service->isHealthyDay($this->user, $date);
        
        // Should return true if no rules are active yet (early morning)
        Carbon::setTestNow($date->copy()->setTime(8, 0));
        $isHealthy = $this->service->isHealthyDay($this->user, $date);
        $this->assertTrue($isHealthy);
        
        // Should return false if rules are active but not met
        Carbon::setTestNow($date->copy()->setTime(10, 0));
        $isHealthy = $this->service->isHealthyDay($this->user, $date);
        $this->assertFalse($isHealthy);
        
        Carbon::setTestNow(); // Reset
    }

    public function test_healthy_day_with_full_compliance()
    {
        $date = Carbon::today();
        
        // Add all required measurements and medications
        $this->createCompleteHealthyDay($date);
        
        $isHealthy = $this->service->isHealthyDay($this->user, $date);
        
        $this->assertTrue($isHealthy);
    }

    public function test_healthy_day_with_partial_compliance()
    {
        $date = Carbon::today();
        
        // Only add morning requirements
        $this->createMedicationMeasurement($date, ['Rybelsus']);
        $this->createGlucoseMeasurement($date, 5.2, true);
        
        Carbon::setTestNow($date->copy()->setTime(15, 0)); // Afternoon
        
        $isHealthy = $this->service->isHealthyDay($this->user, $date);
        
        $this->assertFalse($isHealthy); // Missing 11:00 and 13:00+ requirements
        
        Carbon::setTestNow(); // Reset
    }

    private function createMedicationMeasurement(Carbon $date, array $medicationNames): Measurement
    {
        $medicationType = MeasurementType::where('slug', 'medication')->first();
        
        $measurement = Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $medicationType->id,
            'date' => $date,
            'created_at' => $date
        ]);
        
        foreach ($medicationNames as $name) {
            $medication = Medication::where('name', $name)->first();
            if ($medication) {
                $measurement->medications()->attach($medication->id);
            }
        }
        
        return $measurement;
    }

    private function createGlucoseMeasurement(Carbon $date, float $value, bool $isFasting = false): Measurement
    {
        $glucoseType = MeasurementType::where('slug', 'glucose')->first();
        
        return Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $glucoseType->id,
            'value' => $value,
            'is_fasting' => $isFasting,
            'date' => $date,
            'created_at' => $date
        ]);
    }

    private function createExerciseMeasurement(Carbon $date): Measurement
    {
        $exerciseType = MeasurementType::where('slug', 'exercise')->first();
        
        return Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $exerciseType->id,
            'description' => 'Walking',
            'duration' => 30,
            'date' => $date,
            'created_at' => $date
        ]);
    }

    private function createCompleteHealthyDay(Carbon $date): void
    {
        // Morning: Rybelsus + fasting glucose
        $this->createMedicationMeasurement($date->copy()->setTime(9, 0), ['Rybelsus']);
        $this->createGlucoseMeasurement($date->copy()->setTime(9, 15), 5.2, true);
        
        // 11:00: Morning medications
        $this->createMedicationMeasurement($date->copy()->setTime(11, 0), ['Metformine', 'Amlodipine', 'Kaliumlosartan']);
        
        // 13:30: Second glucose
        $this->createGlucoseMeasurement($date->copy()->setTime(13, 30), 7.1, false);
        
        // 14:30: Exercise
        $this->createExerciseMeasurement($date->copy()->setTime(14, 30));
        
        // 18:15: Third glucose
        $this->createGlucoseMeasurement($date->copy()->setTime(18, 15), 6.8, false);
        
        // 20:00: Evening medications (second Metformine + Atorvastatine)
        $this->createMedicationMeasurement($date->copy()->setTime(20, 0), ['Metformine', 'Atorvastatine']);
    }
}