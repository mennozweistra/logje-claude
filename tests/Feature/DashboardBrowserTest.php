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
    
    // Get existing measurement types
    $this->glucoseType = MeasurementType::where('slug', 'glucose')->first();
    $this->weightType = MeasurementType::where('slug', 'weight')->first();
    $this->exerciseType = MeasurementType::where('slug', 'exercise')->first();
    $this->notesType = MeasurementType::where('slug', 'notes')->first();
    
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

// Filter functionality was removed - test skipped

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

// Filter form elements removed - test skipped

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
    expect($content)->toContain('type="text"');
    expect($content)->toContain('wire:click="previousDay"');
    expect($content)->toContain('wire:click="nextDay"');
});

it('has proper Livewire component structure', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $content = $response->getContent();
    
    // Should contain Livewire directives and component references
    expect($content)->toContain('wire:');
    expect($content)->toContain('Dashboard');
    expect($content)->toContain('wire:snapshot');
});

// View toggle removed - test skipped

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