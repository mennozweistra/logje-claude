<?php

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password')
    ]);
    
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
});

it('renders dashboard without HTML comment artifacts', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $response->assertStatus(200);
    
    // Check that HTML comments are not rendered as escaped text
    $response->assertDontSee('<\!--');
    $response->assertDontSee('\\!--');
    
    // Should see proper content
    $response->assertSee('Dashboard');
    $response->assertSee('Measurements');
});

it('renders measurement filter UI correctly', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $response->assertStatus(200);
    
    // Should see filter UI elements
    $response->assertSee('Search measurements');
    $response->assertSee('Date range');
    $response->assertSee('Measurement types');
    $response->assertSee('Blood Glucose');
    $response->assertSee('Weight');
    $response->assertSee('Exercise');
    $response->assertSee('Notes');
    
    // Should see sort options
    $response->assertSee('Sort by');
});

it('shows measurements in summary view by default', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $response->assertStatus(200);
    
    // Should show measurement type cards
    $response->assertSee('Glucose');
    $response->assertSee('Weight');
    $response->assertSee('Exercise');
    
    // Should show counts
    $response->assertSee('1'); // Count of measurements per type
});

it('displays proper form elements for filtering', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $content = $response->getContent();
    
    // Check for proper form inputs
    expect($content)->toContain('wire:model.debounce.300ms="search"');
    expect($content)->toContain('wire:model="dateRange"');
    expect($content)->toContain('wire:model="filterTypes"');
    expect($content)->toContain('wire:model="sortBy"');
    
    // Check for proper checkboxes
    expect($content)->toContain('value="glucose"');
    expect($content)->toContain('value="weight"');
    expect($content)->toContain('value="exercise"');
    expect($content)->toContain('value="notes"');
});

it('has proper navigation structure', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $content = $response->getContent();
    
    // Check navigation is properly structured
    expect($content)->toContain('<nav');
    expect($content)->toContain('Dashboard');
    expect($content)->toContain('Reports');
    expect($content)->toContain('Log Out');
    
    // Should not contain escaped HTML
    expect($content)->not->toContain('<\!--');
    expect($content)->not->toContain('\\!--');
});

it('renders date navigation properly', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $content = $response->getContent();
    
    // Should see date navigation elements (Livewire compiles these at runtime)
    expect($content)->toContain('wire:');
    expect($content)->toContain('selectedDate');
    expect($content)->toContain('type="date"');
});

it('has proper Livewire component structure', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $content = $response->getContent();
    
    // Should contain Livewire directives and component references
    expect($content)->toContain('wire:');
    expect($content)->toContain('Dashboard');
    expect($content)->toContain('wire:snapshot');
});

it('renders view toggle properly', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $content = $response->getContent();
    
    // Should see view toggle elements
    expect($content)->toContain('Summary');
    expect($content)->toContain('Detailed');
    expect($content)->toContain('wire:click="toggleView"');
});

it('includes proper styling classes', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $content = $response->getContent();
    
    // Should contain Tailwind CSS classes for proper styling
    expect($content)->toContain('bg-white');
    expect($content)->toContain('rounded-lg');
    expect($content)->toContain('shadow');
    expect($content)->toContain('grid');
    expect($content)->toContain('flex');
    
    // Should have responsive classes
    expect($content)->toContain('sm:');
    expect($content)->toContain('md:');
    expect($content)->toContain('lg:');
});