<?php

namespace Tests\Feature;

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Livewire\Livewire;
use Tests\TestCase;

class MeasurementTimePreservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected MeasurementType $weightType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->weightType = MeasurementType::where('slug', 'weight')->first() 
            ?? MeasurementType::factory()->create([
                'name' => 'Weight Test',
                'slug' => 'weight-test'
            ]);
    }

    #[Test]
    public function measurement_preserves_custom_created_at_timestamp()
    {
        // Set a specific time (not current time)
        $customTime = Carbon::createFromFormat('Y-m-d H:i', '2025-08-03 10:30');
        
        // Create measurement with custom timestamp
        $measurement = Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->weightType->id,
            'value' => 75.5,
            'date' => '2025-08-03',
            'notes' => 'Test measurement',
            'created_at' => $customTime,
        ]);

        // Verify the custom timestamp was preserved
        $this->assertEquals($customTime->format('Y-m-d H:i:s'), $measurement->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals('10:30', $measurement->created_at->format('H:i'));
    }

    #[Test]
    public function measurement_update_preserves_custom_created_at_timestamp()
    {
        // Create measurement with initial time
        $initialTime = Carbon::createFromFormat('Y-m-d H:i', '2025-08-03 10:30');
        $measurement = Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->weightType->id,
            'value' => 75.5,
            'date' => '2025-08-03',
            'notes' => 'Test measurement',
            'created_at' => $initialTime,
        ]);

        // Update with new time
        $newTime = Carbon::createFromFormat('Y-m-d H:i', '2025-08-03 14:15');
        $measurement->update([
            'value' => 76.0,
            'created_at' => $newTime,
        ]);

        // Verify the updated timestamp was preserved
        $measurement->refresh();
        $this->assertEquals($newTime->format('Y-m-d H:i:s'), $measurement->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals('14:15', $measurement->created_at->format('H:i'));
    }

    #[Test]
    public function ui_preserves_custom_time_during_glucose_measurement_creation()
    {
        $this->actingAs($this->user);
        
        // Set a custom time (not current time)
        $customTime = '10:30';
        
        $component = Livewire::test(\App\Livewire\MeasurementModal::class);
        
        $component->call('openAddMeasurement', 'glucose', today()->format('Y-m-d'))
                  ->set('glucoseValue', 6.5)
                  ->set('glucoseTime', $customTime)
                  ->call('save');

        // Verify measurement was created with custom time
        $measurement = Measurement::where('user_id', $this->user->id)->first();
        $this->assertNotNull($measurement);
        $this->assertEquals($customTime, $measurement->created_at->format('H:i'));
    }

    #[Test]
    public function ui_preserves_custom_time_during_weight_measurement_creation()
    {
        $this->actingAs($this->user);
        
        $customTime = '14:45';
        
        $component = Livewire::test(\App\Livewire\MeasurementModal::class);
        
        $component->call('openAddMeasurement', 'weight', today()->format('Y-m-d'))
                  ->set('weightValue', 75.5)
                  ->set('weightTime', $customTime)
                  ->call('save');

        // Verify measurement was created with custom time
        $measurement = Measurement::where('user_id', $this->user->id)->first();
        $this->assertNotNull($measurement);
        $this->assertEquals($customTime, $measurement->created_at->format('H:i'));
    }

    #[Test]
    public function ui_preserves_custom_time_during_measurement_editing()
    {
        $this->actingAs($this->user);
        
        // Create initial measurement
        $measurement = Measurement::create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->weightType->id,
            'value' => 75.0,
            'date' => today(),
            'created_at' => Carbon::createFromFormat('H:i', '10:00')->setDateFrom(today()),
        ]);

        // Edit with new custom time
        $newCustomTime = '16:20';
        
        $component = Livewire::test(\App\Livewire\MeasurementModal::class);
        
        $component->call('openEditMeasurement', $measurement->id)
                  ->set('weightValue', 76.0)
                  ->set('weightTime', $newCustomTime)
                  ->call('save');

        // Verify measurement was updated with new custom time
        $measurement->refresh();
        $this->assertEquals($newCustomTime, $measurement->created_at->format('H:i'));
        $this->assertEquals(76.0, $measurement->value);
    }

    #[Test]
    public function ui_time_preservation_works_across_different_measurement_types()
    {
        $this->actingAs($this->user);
        
        $testCases = [
            ['type' => 'glucose', 'field' => 'glucoseValue', 'time_field' => 'glucoseTime', 'value' => 7.2, 'time' => '08:15'],
            ['type' => 'weight', 'field' => 'weightValue', 'time_field' => 'weightTime', 'value' => 80.5, 'time' => '09:30'],
            ['type' => 'exercise', 'field' => 'exerciseDescription', 'time_field' => 'exerciseTime', 'value' => 'Running', 'time' => '18:45'],
        ];

        foreach ($testCases as $i => $testCase) {
            $component = Livewire::test(\App\Livewire\MeasurementModal::class);
            
            $component->call('openAddMeasurement', $testCase['type'], today()->format('Y-m-d'))
                      ->set($testCase['field'], $testCase['value'])
                      ->set($testCase['time_field'], $testCase['time']);
            
            // Add duration for exercise
            if ($testCase['type'] === 'exercise') {
                $component->set('exerciseDuration', 30);
            }
            
            $component->call('save');

            // Verify each measurement preserves its custom time
            $measurement = Measurement::where('user_id', $this->user->id)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
            
            $this->assertEquals($testCase['time'], $measurement->created_at->format('H:i'), 
                "Failed for measurement type: {$testCase['type']}");
        }
    }
}