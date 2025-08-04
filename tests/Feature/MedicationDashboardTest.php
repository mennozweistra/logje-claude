<?php

use App\Models\User;
use App\Models\MeasurementType;
use App\Models\Medication;
use App\Models\Measurement;
use App\Livewire\Dashboard;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->medicationType = MeasurementType::where('slug', 'medication')->first();
    
    // Create medications for testing - assign to user
    $this->rybelsus = Medication::factory()->create(['name' => 'Rybelsus', 'user_id' => $this->user->id]);
    $this->metformine = Medication::factory()->create(['name' => 'Metformine', 'user_id' => $this->user->id]);
    $this->amlodipine = Medication::factory()->create(['name' => 'Amlodipine', 'user_id' => $this->user->id]);
});

test('it displays medication button on dashboard', function () {
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->assertSee('💊')
        ->assertSee('Medication');
});

// Filter functionality removed - test skipped

test('it displays single medication measurement correctly', function () {
    // Create measurement with single medication
    $measurement = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->medicationType->id,
        'notes' => 'Morning dose',
        'date' => Carbon::today(),
        'created_at' => Carbon::today()->setTime(8, 0),
    ]);
    
    $measurement->medications()->attach($this->rybelsus);
    
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->assertSee('💊')
        ->assertSee('08:00')
        ->assertSee('Medication:')
        ->assertSee('Rybelsus')
        ->assertSee('Morning dose');
});

test('it displays multiple medications alphabetically', function () {
    // Create measurement with multiple medications
    $measurement = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->medicationType->id,
        'notes' => 'Evening medications',
        'date' => Carbon::today(),
        'created_at' => Carbon::today()->setTime(20, 0),
    ]);
    
    // Attach medications in non-alphabetical order
    $measurement->medications()->attach([$this->rybelsus->id, $this->amlodipine->id, $this->metformine->id]);
    
    $component = Livewire::actingAs($this->user)
        ->test(Dashboard::class);
        
    // Should display medications alphabetically: Amlodipine, Metformine, Rybelsus
    $component->assertSee('Amlodipine, Metformine, Rybelsus');
});

test('it opens medication modal when clicking medication button', function () {
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->call('openAddMeasurement', 'medication')
        ->assertDispatched('open-add-measurement', 'medication');
});

// Filter functionality removed - test skipped

test('it displays medication measurements without notes', function () {
    $measurement = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->medicationType->id,
        'notes' => null,
        'date' => Carbon::today(),
        'created_at' => Carbon::today()->setTime(8, 0),
    ]);
    
    $measurement->medications()->attach($this->rybelsus);
    
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->assertSee('💊')
        ->assertSee('Medication:')
        ->assertSee('Rybelsus');
});

test('it opens edit modal when clicking medication measurement', function () {
    $measurement = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->medicationType->id,
        'date' => Carbon::today(),
        'created_at' => Carbon::today()->setTime(8, 0),
    ]);
    
    $measurement->medications()->attach($this->rybelsus);
    
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->call('openEditMeasurement', $measurement->id)
        ->assertDispatched('open-edit-measurement', $measurement->id);
});