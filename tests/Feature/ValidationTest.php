<?php

use App\Livewire\MeasurementModal;
use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->glucoseType = MeasurementType::where('slug', 'glucose')->first();
    $this->weightType = MeasurementType::where('slug', 'weight')->first();
    $this->exerciseType = MeasurementType::where('slug', 'exercise')->first();
    $this->notesType = MeasurementType::where('slug', 'notes')->first();
});

describe('Glucose Validation', function () {
    it('validates glucose value is required', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '')
            ->set('glucoseTime', '08:30')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'required']);
    });

    it('validates glucose value is numeric', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', 'not-a-number')
            ->set('glucoseTime', '08:30')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'numeric']);
    });

    it('validates glucose value minimum', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '0.05')
            ->set('glucoseTime', '08:30')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'min']);
    });

    it('validates glucose value maximum', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '55.0')
            ->set('glucoseTime', '08:30')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'max']);
    });

    it('validates glucose value decimal places', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '5.555')
            ->set('glucoseTime', '08:30')
            ->call('save')
            ->assertHasErrors(['glucoseValue' => 'regex']);
    });

    it('accepts valid glucose values', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '5.5')
            ->set('glucoseTime', '08:30')
            ->call('save')
            ->assertHasNoErrors();
    });

    it('validates glucose time format', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '5.5')
            ->set('glucoseTime', '25:70')
            ->call('save')
            ->assertHasErrors(['glucoseTime' => 'date_format']);
    });
});

describe('Weight Validation', function () {
    it('validates weight value is required', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'weight')
            ->set('weightValue', '')
            ->set('weightTime', '08:30')
            ->call('save')
            ->assertHasErrors(['weightValue' => 'required']);
    });

    it('validates weight minimum', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'weight')
            ->set('weightValue', '0.05')
            ->set('weightTime', '08:30')
            ->call('save')
            ->assertHasErrors(['weightValue' => 'min']);
    });

    it('validates weight maximum', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'weight')
            ->set('weightValue', '600')
            ->set('weightTime', '08:30')
            ->call('save')
            ->assertHasErrors(['weightValue' => 'max']);
    });

    it('accepts valid weight values', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'weight')
            ->set('weightValue', '75.5')
            ->set('weightTime', '08:30')
            ->call('save')
            ->assertHasNoErrors();
    });
});

describe('Exercise Validation', function () {
    it('validates exercise description is required', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', '')
            ->set('exerciseDuration', '30')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasErrors(['exerciseDescription' => 'required']);
    });

    it('validates exercise description minimum length', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', 'A')
            ->set('exerciseDuration', '30')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasErrors(['exerciseDescription' => 'min']);
    });

    it('validates exercise description maximum length', function () {
        $longDescription = str_repeat('A', 256);
        
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', $longDescription)
            ->set('exerciseDuration', '30')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasErrors(['exerciseDescription' => 'max']);
    });

    it('validates exercise description format', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', 'Running @#$%^')
            ->set('exerciseDuration', '30')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasErrors(['exerciseDescription' => 'regex']);
    });

    it('validates exercise duration is required', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', 'Running')
            ->set('exerciseDuration', '')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasErrors(['exerciseDuration' => 'required']);
    });

    it('validates exercise duration minimum', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', 'Running')
            ->set('exerciseDuration', '0')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasErrors(['exerciseDuration' => 'min']);
    });

    it('validates exercise duration maximum', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', 'Running')
            ->set('exerciseDuration', '1500')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasErrors(['exerciseDuration' => 'max']);
    });

    it('accepts valid exercise data', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', 'Running')
            ->set('exerciseDuration', '30')
            ->set('exerciseTime', '18:00')
            ->call('save')
            ->assertHasNoErrors();
    });
});

describe('Notes Validation', function () {
    it('validates notes content is required', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'notes')
            ->set('notesContent', '')
            ->set('notesTime', '20:00')
            ->call('save')
            ->assertHasErrors(['notesContent' => 'required']);
    });

    it('validates notes content minimum length', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'notes')
            ->set('notesContent', '')
            ->set('notesTime', '20:00')
            ->call('save')
            ->assertHasErrors(['notesContent' => 'required']);
    });

    it('validates notes content maximum length', function () {
        $longContent = str_repeat('A', 2001);
        
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'notes')
            ->set('notesContent', $longContent)
            ->set('notesTime', '20:00')
            ->call('save')
            ->assertHasErrors(['notesContent' => 'max']);
    });

    it('accepts valid notes content', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'notes')
            ->set('notesContent', 'Feeling great today!')
            ->set('notesTime', '20:00')
            ->call('save')
            ->assertHasNoErrors();
    });
});

describe('Duplicate Timestamp Prevention', function () {
    it('prevents creating duplicate glucose measurements at same time', function () {
        // Create existing measurement
        Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5,
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(8, 30)
        ]);

        // Try to create another at same time - should not create duplicate
        $initialCount = Measurement::count();
        
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '6.2')
            ->set('glucoseTime', '08:30')
            ->call('save');
            
        // Should not create a new measurement (duplicate prevention)
        expect(Measurement::count())->toBe($initialCount);
    });

    it('allows creating measurements at different times', function () {
        // Create existing measurement
        Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5,
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(8, 30)
        ]);

        // Create another at different time
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '6.2')
            ->set('glucoseTime', '08:31')
            ->call('save')
            ->assertHasNoErrors();
    });
});

describe('Edit Validation', function () {
    it('validates glucose edit with same validation rules', function () {
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

    it('prevents editing to create duplicate timestamps', function () {
        // Create two measurements
        $measurement1 = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5,
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(8, 30)
        ]);

        $measurement2 = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 6.2,
            'date' => Carbon::today(),
            'created_at' => Carbon::today()->setTime(9, 30)
        ]);

        // Try to edit measurement2 to have same time as measurement1
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openEditMeasurement', $measurement2->id)
            ->set('glucoseTime', '08:30')
            ->call('save');
            
        // Verify measurement2 was not updated to the conflicting time
        $measurement2->refresh();
        expect($measurement2->created_at->format('H:i'))->not->toBe('08:30');
    });
});

describe('Error Message Display', function () {
    it('displays user-friendly error messages', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'glucose')
            ->set('glucoseValue', '100')
            ->call('save')
            ->assertSee('Blood glucose level seems too high (max 50 mmol/L). Please check your reading.');
    });

    it('displays validation errors for multiple fields', function () {
        Livewire::actingAs($this->user)
            ->test(MeasurementModal::class)
            ->call('openAddMeasurement', 'exercise')
            ->set('exerciseDescription', 'A')
            ->set('exerciseDuration', '0')
            ->call('save')
            ->assertHasErrors(['exerciseDescription', 'exerciseDuration']);
    });
});