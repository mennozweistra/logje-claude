<?php

namespace Tests\Feature;

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
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
}