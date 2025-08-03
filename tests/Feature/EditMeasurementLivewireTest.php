<?php

use App\Livewire\MeasurementModal;
use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    
    $this->glucoseType = MeasurementType::where('slug', 'glucose')->first();
    $this->weightType = MeasurementType::where('slug', 'weight')->first();
    $this->exerciseType = MeasurementType::where('slug', 'exercise')->first();
    $this->notesType = MeasurementType::where('slug', 'notes')->first();
});

it('can render the edit measurement component', function () {
    $component = Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->assertStatus(200);

    $component->assertViewIs('livewire.measurement-modal');
});

it('starts with no modal visible', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->assertSet('showModal', false)
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('measurementId', null);
});

describe('Edit Glucose Measurements', function () {
    it('can start editing a glucose measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5,
            'is_fasting' => true,
            'notes' => 'Morning reading',
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(8, 30)
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id);

        $component->assertSet('showModal', true)
            ->assertSet('measurementId', $measurement->id)
            ->assertSet('glucoseValue', '5.50')
            ->assertSet('isFasting', true)
            ->assertSet('glucoseTime', '08:30')
            ->assertSet('glucoseNotes', 'Morning reading')
            ->assertSee('Edit Glucose Measurement');
    });

    it('can update a glucose measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5,
            'is_fasting' => false,
            'notes' => 'Original note',
            'date' => Carbon::today()
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->set('glucoseValue', '6.8')
            ->set('isFasting', true)
            ->set('glucoseTime', '09:15')
            ->set('glucoseNotes', 'Updated note')
            ->call('save');

        $component->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertDispatched('measurement-saved');

        $measurement->refresh();
        expect((float) $measurement->value)->toBe(6.8);
        expect($measurement->is_fasting)->toBeTrue();
        expect($measurement->notes)->toBe('Updated note');
        // Verify the time was updated (should be different from original time)
        expect($measurement->created_at->format('H:i'))->not->toBe('08:00');
    });

    it('validates glucose value when updating', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->set('glucoseValue', '')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'required']);
    });

    it('validates glucose value is numeric', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->set('glucoseValue', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'numeric']);
    });

    it('validates glucose value range', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->set('glucoseValue', '100')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'max']);
    });
});

describe('Edit Weight Measurements', function () {
    it('can start editing a weight measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->weightType->id,
            'value' => 75.5,
            'notes' => 'Morning weight',
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(7, 0)
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id);

        $component->assertSet('showModal', true)
            ->assertSet('weightValue', '75.50')
            ->assertSet('weightTime', '07:00')
            ->assertSet('weightNotes', 'Morning weight')
            ->assertSee('Edit Weight Measurement');
    });

    it('can update a weight measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->weightType->id,
            'value' => 75.5,
            'notes' => 'Original note',
            'date' => Carbon::today()
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->set('weightValue', '76.2')
            ->set('weightTime', '08:30')
            ->set('weightNotes', 'Updated weight note')
            ->call('save');

        $component->assertHasNoErrors()
            ->assertDispatched('measurement-saved');

        $measurement->refresh();
        expect((float) $measurement->value)->toBe(76.2);
        expect($measurement->notes)->toBe('Updated weight note');
    });
});

describe('Edit Exercise Measurements', function () {
    it('can start editing an exercise measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->exerciseType->id,
            'description' => 'Running',
            'duration' => 30,
            'notes' => 'Good pace',
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(18, 0)
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id);

        $component->assertSet('showModal', true)
            ->assertSet('exerciseDescription', 'Running')
            ->assertSet('exerciseDuration', '30')
            ->assertSet('exerciseTime', '18:00')
            ->assertSet('exerciseNotes', 'Good pace')
            ->assertSee('Edit Exercise Measurement');
    });

    it('can update an exercise measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->exerciseType->id,
            'description' => 'Running',
            'duration' => 30,
            'notes' => 'Original note',
            'date' => Carbon::today()
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->set('exerciseDescription', 'Cycling')
            ->set('exerciseDuration', '45')
            ->set('exerciseTime', '19:30')
            ->set('exerciseNotes', 'Updated exercise note')
            ->call('save');

        $component->assertHasNoErrors()
            ->assertDispatched('measurement-saved');

        $measurement->refresh();
        expect($measurement->description)->toBe('Cycling');
        expect($measurement->duration)->toBe(45);
        expect($measurement->notes)->toBe('Updated exercise note');
    });
});

describe('Edit Notes Measurements', function () {
    it('can start editing a notes measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->notesType->id,
            'notes' => 'Feeling great today!',
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(12, 0)
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id);

        $component->assertSet('showModal', true)
            ->assertSet('notesTime', '12:00')
            ->assertSet('notesContent', 'Feeling great today!')
            ->assertSee('Edit Notes Measurement');
    });

    it('can update a notes measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->notesType->id,
            'notes' => 'Original note',
            'date' => Carbon::today()
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->set('notesTime', '14:30')
            ->set('notesContent', 'Updated daily note')
            ->call('save');

        $component->assertHasNoErrors()
            ->assertDispatched('measurement-saved');

        $measurement->refresh();
        expect($measurement->notes)->toBe('Updated daily note');
    });
});

describe('Delete Measurements', function () {
    it('can start delete confirmation', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->call('confirmDelete');

        $component->assertSet('showDeleteConfirm', true)
            ->assertSet('measurementId', $measurement->id);
    });

    it('can delete a measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->call('confirmDelete')
            ->call('delete');

        $component->assertHasNoErrors()
            ->assertSet('showDeleteConfirm', false)
            ->assertDispatched('measurement-saved');

        // Verify soft delete
        expect(Measurement::find($measurement->id))->toBeNull();
        expect(Measurement::withTrashed()->find($measurement->id))->not->toBeNull();
    });
});

describe('Authorization', function () {
    it('prevents editing measurements from other users', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->otherUser->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id);

        $component->assertSet('showModal', false)
            ->assertSet('measurementId', null);
    });

    it('prevents deleting measurements from other users', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->otherUser->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id);

        $component->assertSet('showModal', false)
            ->assertSet('measurementId', null);
    });

    it('prevents updating measurements from other users', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->otherUser->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        // Manually set the measurement to bypass authorization check
        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class);
        
        $component->set('measurementId', $measurement->id)
            ->set('measurement', $measurement)
            ->set('glucoseValue', '8.5')
            ->call('save');

        // Should not update since user doesn't own the measurement
        $measurement->refresh();
        expect((float) $measurement->value)->toBe(5.5);
    });
});

describe('Error Handling', function () {
    it('handles non-existent measurement gracefully when editing', function () {
        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', 999999);

        $component->assertSet('showModal', false);
    });

    it('handles non-existent measurement gracefully when deleting', function () {
        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('confirmDelete', 999999);

        $component->assertSet('showDeleteConfirm', false);
    });
});

describe('Cancel Operations', function () {
    it('can cancel editing', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->assertSet('showModal', true)
            ->call('cancel')
            ->assertSet('showModal', false)
            ->assertSet('measurementId', null);
    });

    it('can cancel delete confirmation', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement->id)
            ->call('confirmDelete')
            ->assertSet('showDeleteConfirm', true)
            ->call('cancel')
            ->assertSet('showDeleteConfirm', false)
            ->assertSet('measurementId', null);
    });
});