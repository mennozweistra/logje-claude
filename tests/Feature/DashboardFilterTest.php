<?php

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    
    // Create measurement types
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
    
    // Create test measurements
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 5.5,
        'date' => Carbon::today(),
        'notes' => 'Morning glucose reading'
    ]);
    
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->weightType->id,
        'value' => 75.0,
        'date' => Carbon::today(),
        'notes' => 'Daily weigh-in'
    ]);
    
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->exerciseType->id,
        'duration' => 30,
        'description' => 'Running',
        'date' => Carbon::today(),
        'notes' => 'Morning jog'
    ]);
    
    // Create old measurements for date range testing
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 6.0,
        'date' => Carbon::today()->subDays(3),
        'notes' => 'Old glucose reading'
    ]);
});

it('shows only today measurements by default', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class);
    
    // Should show 3 measurements from today - check summary view content
    $component->assertSee('Glucose')
        ->assertSee('Weight') 
        ->assertSee('Exercise');
        
    // In detailed view, should show the notes
    $component->set('detailedView', true)
        ->assertSee('Morning glucose reading')
        ->assertSee('Daily weigh-in') 
        ->assertSee('Morning jog')
        ->assertDontSee('Old glucose reading');
});

it('can filter by measurement type', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('filterTypes', ['glucose'])
        ->set('detailedView', true);
    
    // Should only show glucose measurements in detailed view
    $component->assertSee('Morning glucose reading')
        ->assertDontSee('Daily weigh-in')
        ->assertDontSee('Morning jog');
});

it('can filter by multiple measurement types', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('filterTypes', ['glucose', 'weight'])
        ->set('detailedView', true);
    
    // Should show glucose and weight measurements
    $component->assertSee('Morning glucose reading')
        ->assertSee('Daily weigh-in')
        ->assertDontSee('Morning jog');
});

it('can search measurements by notes', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('search', 'glucose')
        ->set('detailedView', true);
    
    // Should show measurements with 'glucose' in notes
    $component->assertSee('Morning glucose reading')
        ->assertDontSee('Daily weigh-in')
        ->assertDontSee('Morning jog');
});

it('can search measurements by description', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('search', 'Running')
        ->set('detailedView', true);
    
    // Should show exercise measurement with 'Running' description
    $component->assertSee('Morning jog')
        ->assertDontSee('Morning glucose reading')
        ->assertDontSee('Daily weigh-in');
});

it('can filter by date range', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('dateRange', 7)
        ->set('detailedView', true);
    
    // Should show measurements from last 7 days including old one
    $component->assertSee('Morning glucose reading')
        ->assertSee('Old glucose reading')
        ->assertSee('Daily weigh-in')
        ->assertSee('Morning jog');
});

it('can combine filters', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('filterTypes', ['glucose'])
        ->set('dateRange', 7)
        ->set('detailedView', true);
    
    // Should show glucose measurements from last 7 days
    $component->assertSee('Morning glucose reading')
        ->assertSee('Old glucose reading')
        ->assertDontSee('Daily weigh-in')
        ->assertDontSee('Morning jog');
});

it('can clear all filters', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('filterTypes', ['glucose'])
        ->set('search', 'test')
        ->set('dateRange', 7)
        ->set('sortBy', 'oldest');
    
    // Clear filters
    $component->call('clearFilters');
    
    // Should reset all filter values
    $component->assertSet('filterTypes', [])
        ->assertSet('search', '')
        ->assertSet('dateRange', 1)
        ->assertSet('sortBy', 'newest');
});

it('can sort measurements', function () {
    // Create measurements with different values
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 8.0,
        'date' => Carbon::today(),
        'notes' => 'High glucose'
    ]);
    
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('dateRange', 7)
        ->set('sortBy', 'value_high')
        ->set('detailedView', true);
    
    // Should show high value first
    $component->assertSee('High glucose');
});

it('can toggle between summary and detailed view', function () {
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class);
    
    // Should start in summary view
    $component->assertSet('detailedView', false);
    
    // Toggle to detailed view
    $component->call('toggleView')
        ->assertSet('detailedView', true);
    
    // Toggle back to summary view
    $component->call('toggleView')
        ->assertSet('detailedView', false);
});

it('only shows current users measurements', function () {
    $otherUser = User::factory()->create();
    
    // Create measurement for other user
    Measurement::factory()->create([
        'user_id' => $otherUser->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 9.0,
        'date' => Carbon::today(),
        'notes' => 'Other user measurement'
    ]);
    
    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Dashboard::class)
        ->set('dateRange', 7);
    
    // Should not see other user's measurements
    $component->assertDontSee('Other user measurement');
});