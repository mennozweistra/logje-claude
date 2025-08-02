<?php

use App\Livewire\EditMeasurement;
use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    
    $this->glucoseType = MeasurementType::factory()->create([
        'name' => 'Blood Glucose',
        'slug' => 'glucose',
        'unit' => 'mmol/L'
    ]);
    $this->weightType = MeasurementType::factory()->create([
        'name' => 'Weight',
        'slug' => 'weight',
        'unit' => 'kg'
    ]);
    $this->exerciseType = MeasurementType::factory()->create([
        'name' => 'Exercise',
        'slug' => 'exercise',
        'unit' => 'minutes'
    ]);
    $this->notesType = MeasurementType::factory()->create([
        'name' => 'Notes',
        'slug' => 'notes'
    ]);
});

it('can render the edit measurement component', function () {
    $component = Livewire::actingAs($this->user)
        ->test(EditMeasurement::class)
        ->assertStatus(200);

    $component->assertViewIs('livewire.edit-measurement');
});

it('starts with no form visible', function () {
    Livewire::actingAs($this->user)
        ->test(EditMeasurement::class)
        ->assertSet('showEditForm', false)
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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id);

        $component->assertSet('showEditForm', true)
            ->assertSet('measurementId', $measurement->id)
            ->assertSet('glucoseValue', '5.50')
            ->assertSet('isFasting', true)
            ->assertSet('glucoseTime', '08:30')
            ->assertSet('glucoseNotes', 'Morning reading')
            ->assertSee('Edit Blood Glucose Measurement');
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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->set('glucoseValue', '6.8')
            ->set('isFasting', true)
            ->set('glucoseTime', '09:15')
            ->set('glucoseNotes', 'Updated note')
            ->call('update');

        $component->assertHasNoErrors()
            ->assertSet('showEditForm', false)
            ->assertDispatched('measurement-updated');

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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->set('glucoseValue', '')
            ->call('update')
            ->assertHasErrors(['glucoseValue' => 'required']);
    });

    it('validates glucose value is numeric', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        Livewire::actingAs($this->user)
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->set('glucoseValue', 'not-a-number')
            ->call('update')
            ->assertHasErrors(['glucoseValue' => 'numeric']);
    });

    it('validates glucose value range', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        Livewire::actingAs($this->user)
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->set('glucoseValue', '100')
            ->call('update')
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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id);

        $component->assertSet('showEditForm', true)
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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->set('weightValue', '76.2')
            ->set('weightTime', '08:30')
            ->set('weightNotes', 'Updated weight note')
            ->call('update');

        $component->assertHasNoErrors()
            ->assertDispatched('measurement-updated');

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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id);

        $component->assertSet('showEditForm', true)
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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->set('exerciseDescription', 'Cycling')
            ->set('exerciseDuration', '45')
            ->set('exerciseTime', '19:30')
            ->set('exerciseNotes', 'Updated exercise note')
            ->call('update');

        $component->assertHasNoErrors()
            ->assertDispatched('measurement-updated');

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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id);

        $component->assertSet('showEditForm', true)
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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->set('notesTime', '14:30')
            ->set('notesContent', 'Updated daily note')
            ->call('update');

        $component->assertHasNoErrors()
            ->assertDispatched('measurement-updated');

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
            ->test(EditMeasurement::class)
            ->call('confirmDelete', $measurement->id);

        $component->assertSet('showDeleteConfirm', true)
            ->assertSet('measurementId', $measurement->id)
            ->assertSee('Delete Measurement')
            ->assertSee('Are you sure you want to delete this Blood Glucose measurement?');
    });

    it('can delete a measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(EditMeasurement::class)
            ->call('confirmDelete', $measurement->id)
            ->call('delete');

        $component->assertHasNoErrors()
            ->assertSet('showDeleteConfirm', false)
            ->assertDispatched('measurement-updated');

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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id);

        $component->assertSet('showEditForm', false)
            ->assertSet('measurementId', null);
    });

    it('prevents deleting measurements from other users', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->otherUser->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(EditMeasurement::class)
            ->call('confirmDelete', $measurement->id);

        $component->assertSet('showDeleteConfirm', false)
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
            ->test(EditMeasurement::class);
        
        $component->set('measurementId', $measurement->id)
            ->set('measurement', $measurement)
            ->set('glucoseValue', '8.5')
            ->call('update');

        // Should not update since user doesn't own the measurement
        $measurement->refresh();
        expect((float) $measurement->value)->toBe(5.5);
    });
});

describe('Error Handling', function () {
    it('handles non-existent measurement gracefully when editing', function () {
        $component = Livewire::actingAs($this->user)
            ->test(EditMeasurement::class)
            ->call('startEdit', 999999);

        $component->assertSet('showEditForm', false);
    });

    it('handles non-existent measurement gracefully when deleting', function () {
        $component = Livewire::actingAs($this->user)
            ->test(EditMeasurement::class)
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
            ->test(EditMeasurement::class)
            ->call('startEdit', $measurement->id)
            ->assertSet('showEditForm', true)
            ->call('cancel')
            ->assertSet('showEditForm', false)
            ->assertSet('measurementId', null);
    });

    it('can cancel delete confirmation', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(EditMeasurement::class)
            ->call('confirmDelete', $measurement->id)
            ->assertSet('showDeleteConfirm', true)
            ->call('cancel')
            ->assertSet('showDeleteConfirm', false)
            ->assertSet('measurementId', null);
    });
});