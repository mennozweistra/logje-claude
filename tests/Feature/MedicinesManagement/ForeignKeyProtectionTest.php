<?php

namespace Tests\Feature\MedicinesManagement;

use App\Models\User;
use App\Models\Medication;
use App\Models\Measurement;
use App\Models\MeasurementType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ForeignKeyProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Medication $medication;
    protected MeasurementType $medicationType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->medication = Medication::factory()->create(['user_id' => $this->user->id]);
        $this->medicationType = MeasurementType::firstOrCreate(['name' => 'Medication'], [
            'slug' => 'medication',
            'description' => 'Medication intake tracking'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_delete_medication_when_referenced_by_measurements()
    {
        // Create a measurement that references this medication
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->medicationType->id,
            'value' => null,
        ]);
        
        // Attach medication to measurement
        $measurement->medications()->attach($this->medication->id);

        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('confirmDelete', $this->medication->id);

        $component->assertSee('Cannot delete');
        $component->assertSee('because it is used in');
        $component->assertSee('medication measurement(s)');
        
        // Medication should still exist
        $this->assertDatabaseHas('medications', [
            'id' => $this->medication->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_delete_medication_when_not_referenced_by_measurements()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('confirmDelete', $this->medication->id)
            ->call('delete');

        $this->assertDatabaseMissing('medications', [
            'id' => $this->medication->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function deletion_blocked_message_includes_count()
    {
        // Create multiple measurements that reference this medication
        $measurement1 = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->medicationType->id,
            'value' => null,
        ]);
        
        $measurement2 = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->medicationType->id,
            'value' => null,
        ]);

        // Attach medication to both measurements
        $measurement1->medications()->attach($this->medication->id);
        $measurement2->medications()->attach($this->medication->id);

        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('confirmDelete', $this->medication->id);

        $component->assertSee('used in 2 medication measurement(s)');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function helpful_deletion_error_message_provides_guidance()
    {
        // Create a measurement that references this medication
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->medicationType->id,
            'value' => null,
        ]);
        
        $measurement->medications()->attach($this->medication->id);

        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\MedicinesManagement::class)
            ->call('confirmDelete', $this->medication->id);

        $component->assertSee('Please remove the medication from those measurements first');
    }
}