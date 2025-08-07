<?php

namespace Tests\Feature;

use App\Livewire\HealthIndicator;
use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\Medication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HealthIndicatorFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Set up measurement types and medications
        $this->setupTestData();
    }

    private function setupTestData()
    {
        // Get or create measurement types
        MeasurementType::firstOrCreate(['slug' => 'glucose'], ['name' => 'Glucose', 'unit' => 'mmol/L']);
        MeasurementType::firstOrCreate(['slug' => 'exercise'], ['name' => 'Exercise', 'unit' => 'minutes']);
        MeasurementType::firstOrCreate(['slug' => 'medication'], ['name' => 'Medication', 'unit' => null]);
        
        // Create medications for this user
        Medication::create(['name' => 'Rybelsus', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Metformine', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Amlodipine', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Kaliumlosartan', 'user_id' => $this->user->id]);
        Medication::create(['name' => 'Atorvastatine', 'user_id' => $this->user->id]);
    }

    public function test_health_indicator_displays_correctly_with_no_measurements()
    {
        // Mock time to early morning when no rules are active yet
        Carbon::setTestNow(Carbon::today()->setTime(8, 0));
        
        $component = Livewire::test(HealthIndicator::class);
        
        // Should be healthy since no rules are active yet
        $component->assertSet('isHealthy', true)
                 ->assertSee('😊');
        
        Carbon::setTestNow(); // Reset
    }

    public function test_health_indicator_displays_unhealthy_with_missed_rules()
    {
        // Mock time to after 9am when rules are active
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        
        $component = Livewire::test(HealthIndicator::class);
        
        // Should be unhealthy since 9am rules are not met
        $component->assertSet('isHealthy', false)
                 ->assertSee('😔');
        
        Carbon::setTestNow(); // Reset
    }

    public function test_health_indicator_updates_when_measurements_added()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(10, 0));
        
        $component = Livewire::test(HealthIndicator::class);
        
        // Initially unhealthy
        $component->assertSet('isHealthy', false);
        
        // Add morning requirements
        $this->addMorningRequirements($today);
        
        // Dispatch measurement saved event
        $component->dispatch('measurement-saved');
        
        // Should now be healthy
        $component->assertSet('isHealthy', true)
                 ->assertSee('😊');
        
        Carbon::setTestNow(); // Reset
    }

    public function test_modal_shows_detailed_rule_status()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(15, 0)); // 3 PM
        
        // Add some measurements to test different rule states
        $this->addMorningRequirements($today);
        
        $component = Livewire::test(HealthIndicator::class);
        
        // Open modal
        $component->call('toggleModal')
                 ->assertSet('modalVisible', true)
                 ->assertSee('Daily Health Rules')
                 ->assertSee('09:00')
                 ->assertSee('Rybelsus medication taken')
                 ->assertSee('Not Active Yet') // Should see some inactive rules
                 ->assertSee('Completed'); // Should see some completed rules
    }

    public function test_modal_displays_proper_rule_status_indicators()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(12, 0)); // Noon
        
        // Add morning requirements only
        $this->addMorningRequirements($today);
        
        $component = Livewire::test(HealthIndicator::class);
        
        $component->call('toggleModal')
                 ->assertSet('modalVisible', true);
        
        // Check that we have the expected rule statuses
        $ruleStatuses = $component->get('ruleStatuses');
        
        $this->assertIsArray($ruleStatuses);
        $this->assertArrayHasKey('rybelsus_morning', $ruleStatuses);
        $this->assertTrue($ruleStatuses['rybelsus_morning']['active']);
        $this->assertTrue($ruleStatuses['rybelsus_morning']['met']);
        
        $this->assertArrayHasKey('glucose_fasting', $ruleStatuses);
        $this->assertTrue($ruleStatuses['glucose_fasting']['active']);
        $this->assertTrue($ruleStatuses['glucose_fasting']['met']);
        
        // Evening rules should not be active yet
        $this->assertArrayHasKey('medications_evening', $ruleStatuses);
        $this->assertFalse($ruleStatuses['medications_evening']['active']);
        
        Carbon::setTestNow(); // Reset
    }

    public function test_modal_closes_when_date_changes()
    {
        $component = Livewire::test(HealthIndicator::class);
        
        // Open modal
        $component->call('toggleModal')
                 ->assertSet('modalVisible', true);
        
        // Change date
        $component->set('selectedDate', '2025-08-06')
                 ->assertSet('modalVisible', false);
    }

    public function test_component_responds_to_dashboard_date_changes()
    {
        $newDate = '2025-08-05';
        
        $component = Livewire::test(HealthIndicator::class);
        
        $originalDate = $component->get('selectedDate');
        
        // Simulate dashboard date change
        $component->dispatch('dashboard-date-changed', $newDate)
                 ->assertSet('selectedDate', $newDate);
        
        $this->assertNotEquals($originalDate, $newDate);
    }

    public function test_modal_shows_different_status_for_past_vs_current_day()
    {
        $yesterday = Carbon::yesterday();
        
        $component = Livewire::test(HealthIndicator::class, ['selectedDate' => $yesterday->format('Y-m-d')]);
        
        $component->call('toggleModal')
                 ->assertSet('modalVisible', true)
                 ->assertSee('Based on complete daily requirements');
        
        // Test current day
        $today = Carbon::today();
        $component->set('selectedDate', $today->format('Y-m-d'))
                 ->call('toggleModal')
                 ->assertSee('Based on active rules for current time');
    }

    public function test_overall_status_display_in_modal()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(10, 0));
        
        // Create a healthy scenario for this time (only morning requirements needed)
        $this->addMorningRequirements($today);
        
        $component = Livewire::test(HealthIndicator::class);
        
        // First check if the component is actually healthy
        $component->assertSet('isHealthy', true);
        
        $component->call('toggleModal')
                 ->assertSet('modalVisible', true)
                 ->assertSee('healthy day') // Simplified assertion
                 ->assertSee('😊');
        
        Carbon::setTestNow(); // Reset
    }

    private function addMorningRequirements(Carbon $date)
    {
        $medicationType = MeasurementType::where('slug', 'medication')->first();
        $glucoseType = MeasurementType::where('slug', 'glucose')->first();
        
        // Add Rybelsus medication
        $medicationMeasurement = Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $medicationType->id,
            'date' => $date,
            'created_at' => $date->copy()->setTime(9, 0)
        ]);
        
        $rybelsus = Medication::where('name', 'Rybelsus')->first();
        $medicationMeasurement->medications()->attach($rybelsus->id);
        
        // Add fasting glucose
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $glucoseType->id,
            'value' => 5.2,
            'is_fasting' => true,
            'date' => $date,
            'created_at' => $date->copy()->setTime(9, 15)
        ]);
    }

    private function addCompleteHealthyDay(Carbon $date)
    {
        $medicationType = MeasurementType::where('slug', 'medication')->first();
        $glucoseType = MeasurementType::where('slug', 'glucose')->first();
        $exerciseType = MeasurementType::where('slug', 'exercise')->first();
        
        // Morning: Rybelsus + fasting glucose (already added by addMorningRequirements)
        $this->addMorningRequirements($date);
        
        // 11:00: Morning medications
        $morningMedicationMeasurement = Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $medicationType->id,
            'date' => $date,
            'created_at' => $date->copy()->setTime(11, 0)
        ]);
        
        $metformine = Medication::where('name', 'Metformine')->first();
        $amlodipine = Medication::where('name', 'Amlodipine')->first();
        $kaliumLosartan = Medication::where('name', 'Kaliumlosartan')->first();
        
        $morningMedicationMeasurement->medications()->attach([
            $metformine->id, 
            $amlodipine->id, 
            $kaliumLosartan->id
        ]);
        
        // 13:30: Second glucose
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $glucoseType->id,
            'value' => 7.1,
            'is_fasting' => false,
            'date' => $date,
            'created_at' => $date->copy()->setTime(13, 30)
        ]);
        
        // 14:30: Exercise
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $exerciseType->id,
            'description' => 'Walking',
            'duration' => 30,
            'date' => $date,
            'created_at' => $date->copy()->setTime(14, 30)
        ]);
        
        // 18:15: Third glucose
        Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $glucoseType->id,
            'value' => 6.8,
            'is_fasting' => false,
            'date' => $date,
            'created_at' => $date->copy()->setTime(18, 15)
        ]);
        
        // 20:00: Evening medications
        $eveningMedicationMeasurement = Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $medicationType->id,
            'date' => $date,
            'created_at' => $date->copy()->setTime(20, 0)
        ]);
        
        $atorvastatine = Medication::where('name', 'Atorvastatine')->first();
        $eveningMedicationMeasurement->medications()->attach([
            $metformine->id, 
            $atorvastatine->id
        ]);
    }
}