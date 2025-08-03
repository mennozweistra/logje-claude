<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard renders mobile responsive layout', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertOk();
    
    // Check that the dashboard page loads and contains Livewire component
    $response->assertSee('Dashboard'); // Page title
    $response->assertSee('wire:'); // Livewire component is loaded
});

test('dashboard has proper responsive classes in HTML', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertOk();
    
    $content = $response->getContent();
    
    // Check for key responsive elements
    expect($content)->toContain('max-w-4xl mx-auto p-4');
    expect($content)->toContain('flex items-center');
    expect($content)->toContain('space-y-6');
});

test('dashboard includes mobile friendly input elements', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertOk();
    
    $content = $response->getContent();
    
    // Check for mobile-friendly elements
    expect($content)->toContain('wire:click="previousDay"');
    expect($content)->toContain('wire:click="nextDay"');
    expect($content)->toContain('wire:click="goToToday"');
});

test('dashboard layout stacks properly on mobile', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertOk();
    
    $content = $response->getContent();
    
    // Check that layout uses mobile-first approach
    expect($content)->toContain('space-y-6'); // Container spacing
    expect($content)->toContain('p-4'); // Mobile padding
    expect($content)->toContain('p-6'); // Card padding
});