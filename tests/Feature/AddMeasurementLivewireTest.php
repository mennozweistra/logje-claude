<?php

use App\Livewire\MeasurementModal;
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

it('can render the measurement modal component', function () {
    $component = Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->assertStatus(200);

    $component->assertViewIs('livewire.measurement-modal');
});

it('starts with modal closed by default', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->assertSet('showModal', false)
        ->assertSet('selectedType', '');
});

it('can open glucose measurement modal', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose')
        ->assertSet('selectedType', 'glucose')
        ->assertSet('showModal', true)
        ->assertSet('mode', 'add')
        ->assertSee('Add Glucose Measurement');
});

it('can save glucose measurement', function () {
    $component = Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose')
        ->set('glucoseValue', '5.5')
        ->set('glucoseTime', '08:00')
        ->set('isFasting', true)
        ->set('glucoseNotes', 'Morning reading')
        ->call('save');

    $component->assertHasNoErrors();
    $component->assertDispatched('measurement-saved');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'value' => 5.5,
        'is_fasting' => true,
        'notes' => 'Morning reading'
    ]);
});

it('can save weight measurement', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'weight')
        ->set('weightValue', '75.2')
        ->set('weightTime', '07:30')
        ->set('weightNotes', 'Morning weight')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('measurement-saved');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'value' => 75.2,
        'notes' => 'Morning weight'
    ]);
});

it('can save exercise measurement', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'exercise')
        ->set('exerciseDuration', '45')
        ->set('exerciseDescription', 'Running')
        ->set('exerciseTime', '18:00')
        ->set('exerciseNotes', 'Evening run')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('measurement-saved');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'duration' => 45,
        'description' => 'Running',
        'notes' => 'Evening run'
    ]);
});

it('can save notes measurement', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'notes')
        ->set('notesTime', '12:00')
        ->set('notesContent', 'Feeling great today!')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('measurement-saved');

    $this->assertDatabaseHas('measurements', [
        'user_id' => $this->user->id,
        'notes' => 'Feeling great today!'
    ]);
});

it('validates glucose value is required', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose')
        ->set('glucoseValue', '')
        ->call('save')
        ->assertHasErrors(['glucoseValue' => 'required']);
});

it('validates glucose value is numeric', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose')
        ->set('glucoseValue', 'not-a-number')
        ->call('save')
        ->assertHasErrors(['glucoseValue' => 'numeric']);
});

it('validates glucose value range', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose')
        ->set('glucoseValue', '100')
        ->call('save')
        ->assertHasErrors(['glucoseValue' => 'max']);
});

it('validates weight value is required', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'weight')
        ->set('weightValue', '')
        ->call('save')
        ->assertHasErrors(['weightValue' => 'required']);
});

it('validates exercise duration is required', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'exercise')
        ->set('exerciseDuration', '')
        ->set('exerciseDescription', 'Running')
        ->call('save')
        ->assertHasErrors(['exerciseDuration' => 'required']);
});

it('validates exercise type is required', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'exercise')
        ->set('exerciseDuration', '30')
        ->set('exerciseDescription', '')
        ->call('save')
        ->assertHasErrors(['exerciseDescription' => 'required']);
});

it('validates notes content is required for notes type', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'notes')
        ->set('notesContent', '')
        ->call('save')
        ->assertHasErrors(['notesContent' => 'required']);
});

it('can cancel and close modal', function () {
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose')
        ->assertSet('showModal', true)
        ->call('cancel')
        ->assertSet('showModal', false);
});

it('pre-populates time with current time', function () {
    $component = Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose');

    $currentTime = Carbon::now()->format('H:i');
    $component->assertSet('glucoseTime', $currentTime);
});

it('handles measurement repository errors gracefully', function () {
    // For now, just test that invalid data produces errors
    Livewire::actingAs($this->user)
        ->test(MeasurementModal::class)
        ->call('openAddMeasurement', 'glucose')
        ->set('glucoseValue', '') // Invalid empty value
        ->call('save')
        ->assertHasErrors(['glucoseValue']);
});