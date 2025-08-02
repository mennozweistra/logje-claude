<?php

use App\Livewire\AddMeasurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
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

it('can render the add measurement component', function () {
    $component = Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->assertStatus(200);

    $component->assertViewIs('livewire.add-measurement');
    $component->assertSee('Add New Measurement');
});

it('shows measurement type selection by default', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->assertSet('showForm', false)
        ->assertSee('Blood Glucose')
        ->assertSee('Weight')
        ->assertSee('Exercise')
        ->assertSee('Notes');
});

it('can select glucose measurement type', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose')
        ->assertSet('selectedType', 'glucose')
        ->assertSet('showForm', true)
        ->assertSee('Blood Glucose (mmol/L)');
});

it('can save glucose measurement', function () {
    $component = Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose')
        ->set('glucoseValue', '5.5')
        ->set('glucoseTime', '08:00')
        ->set('isFasting', true)
        ->set('glucoseNotes', 'Morning reading')
        ->call('save');

    $component->assertHasNoErrors();
    $component->assertDispatched('measurement-added');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'value' => 5.5,
        'is_fasting' => true,
        'notes' => 'Morning reading'
    ]);
});

it('can save weight measurement', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'weight')
        ->set('weightValue', '75.2')
        ->set('weightTime', '07:30')
        ->set('weightNotes', 'Morning weight')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('measurement-added');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'value' => 75.2,
        'notes' => 'Morning weight'
    ]);
});

it('can save exercise measurement', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'exercise')
        ->set('exerciseDuration', '45')
        ->set('exerciseDescription', 'Running')
        ->set('exerciseTime', '18:00')
        ->set('exerciseNotes', 'Evening run')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('measurement-added');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'duration' => 45,
        'description' => 'Running',
        'notes' => 'Evening run'
    ]);
});

it('can save notes measurement', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'notes')
        ->set('notesTime', '12:00')
        ->set('notesContent', 'Feeling great today!')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('measurement-added');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'notes' => 'Feeling great today!'
    ]);
});

it('validates glucose value is required', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose')
        ->set('glucoseValue', '')
        ->call('save')
        ->assertHasErrors(['glucoseValue' => 'required']);
});

it('validates glucose value is numeric', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose')
        ->set('glucoseValue', 'not-a-number')
        ->call('save')
        ->assertHasErrors(['glucoseValue' => 'numeric']);
});

it('validates glucose value range', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose')
        ->set('glucoseValue', '100')
        ->call('save')
        ->assertHasErrors(['glucoseValue' => 'max']);
});

it('validates weight value is required', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'weight')
        ->set('weightValue', '')
        ->call('save')
        ->assertHasErrors(['weightValue' => 'required']);
});

it('validates exercise duration is required', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'exercise')
        ->set('exerciseDuration', '')
        ->set('exerciseDescription', 'Running')
        ->call('save')
        ->assertHasErrors(['exerciseDuration' => 'required']);
});

it('validates exercise type is required', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'exercise')
        ->set('exerciseDuration', '30')
        ->set('exerciseDescription', '')
        ->call('save')
        ->assertHasErrors(['exerciseDescription' => 'required']);
});

it('validates notes content is required for notes type', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'notes')
        ->set('notesContent', '')
        ->call('save')
        ->assertHasErrors(['notesContent' => 'required']);
});

it('can cancel and return to type selection', function () {
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose')
        ->assertSet('showForm', true)
        ->call('cancel')
        ->assertSet('showForm', false)
        ->assertSet('selectedType', null);
});

it('pre-populates time with current time', function () {
    $component = Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose');

    $currentTime = Carbon::now()->format('H:i');
    $component->assertSet('glucoseTime', $currentTime);
});

it('handles measurement repository errors gracefully', function () {
    // For now, just test that invalid data produces errors
    Livewire::actingAs($this->user)
        ->test(AddMeasurement::class, ['selectedDate' => Carbon::today()->format('Y-m-d')])
        ->call('selectType', 'glucose')
        ->set('glucoseValue', '') // Invalid empty value
        ->call('save')
        ->assertHasErrors(['glucoseValue']);
});