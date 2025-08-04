<?php

use App\Models\User;
use App\Models\MeasurementType;
use App\Models\Medication;
use App\Livewire\MeasurementModal;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->medicationType = MeasurementType::where('slug', 'medication')->first();
    
    // Create medications for testing - assign to user
    $this->medications = collect([
        Medication::factory()->create(['name' => 'Rybelsus', 'user_id' => $this->user->id]),
        Medication::factory()->create(['name' => 'Metformine', 'user_id' => $this->user->id]),
        Medication::factory()->create(['name' => 'Amlodipine', 'user_id' => $this->user->id]),
        Medication::factory()->create(['name' => 'Kaliumlosartan', 'user_id' => $this->user->id]),
        Medication::factory()->create(['name' => 'Atorvastatine', 'user_id' => $this->user->id]),
    ]);
});

test('it can open medication modal', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication')
        ->assertSet('showModal', true)
        ->assertSet('selectedType', 'medication')
        ->assertSee('Add Medication Measurement');
});

test('it displays all 5 medications in modal', function () {
    $component = Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication');
        
    foreach ($this->medications as $medication) {
        $component->assertSee($medication->name)
                 ->assertSee($medication->description);
    }
});

test('it validates that at least one medication is selected', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication')
        ->set('medicationTime', '10:00')
        ->set('selectedMedications', [])
        ->call('save')
        ->assertHasErrors(['selectedMedications' => 'required']);
});

test('it validates medication time is required', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication')
        ->set('selectedMedications', [$this->medications->first()->id])
        ->set('medicationTime', '')
        ->call('save')
        ->assertHasErrors(['medicationTime' => 'required']);
});

test('it validates medication time format', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication')
        ->set('selectedMedications', [$this->medications->first()->id])
        ->set('medicationTime', 'invalid-time')
        ->call('save')
        ->assertHasErrors(['medicationTime']);
});

test('it validates notes length', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication')
        ->set('selectedMedications', [$this->medications->first()->id])
        ->set('medicationTime', '10:00')
        ->set('medicationNotes', str_repeat('a', 1001))
        ->call('save')
        ->assertHasErrors(['medicationNotes']);
});

test('it can save medication measurement with single medication', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication', Carbon::today()->format('Y-m-d'))
        ->set('selectedMedications', [$this->medications->first()->id])
        ->set('medicationTime', '08:00')
        ->set('medicationNotes', 'Morning dose')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false)
        ->assertDispatched('measurement-saved');
        
    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->medicationType->id,
        'notes' => 'Morning dose',
    ]);
    
    $this->assertDatabaseHas('measurement_medication', [
        'medication_id' => $this->medications->first()->id,
    ]);
});

test('it can save medication measurement with multiple medications', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication', Carbon::today()->format('Y-m-d'))
        ->set('selectedMedications', [$this->medications->get(0)->id, $this->medications->get(1)->id])
        ->set('medicationTime', '08:00')
        ->set('medicationNotes', 'Morning medications')
        ->call('save')
        ->assertSet('showModal', false);
        
    $this->assertDatabaseHas('measurement_medication', [
        'medication_id' => $this->medications->first()->id,
    ]);
    
    $this->assertDatabaseHas('measurement_medication', [
        'medication_id' => $this->medications->get(1)->id,
    ]);
});

test('it sets current time by default when opening modal', function () {
    $currentTime = Carbon::now()->format('H:i');
    
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication')
        ->assertSet('medicationTime', $currentTime);
});

test('it can save medication without notes', function () {
    $rybelsus = $this->medications->where('name', 'Rybelsus')->first();
    
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication', Carbon::today()->format('Y-m-d'))
        ->set('selectedMedications', [$rybelsus->id])
        ->set('medicationTime', '08:00')
        ->set('medicationNotes', '')
        ->call('save')
        ->assertSet('showModal', false);
        
    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->medicationType->id,
        'notes' => '',
    ]);
});

it('prevents duplicate timestamps for same measurement type', function () {
    // Create first measurement at 08:00
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication', Carbon::today()->format('Y-m-d'))
        ->set('selectedMedications', [$this->medications->first()->id])
        ->set('medicationTime', '08:00')
        ->call('save');
        
    // Try to create second measurement at same time - should fail
    $initialCount = \App\Models\Measurement::count();
    
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'medication', Carbon::today()->format('Y-m-d'))
        ->set('selectedMedications', [$this->medications->first()->id])
        ->set('medicationTime', '08:00')
        ->call('save');
        
    // Duplicate prevention should prevent creating the second measurement
    expect(\App\Models\Measurement::count())->toBe($initialCount); // Should stay the same (no duplicate created)
});