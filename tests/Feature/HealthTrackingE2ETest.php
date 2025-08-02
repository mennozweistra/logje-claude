<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'test@example.com')->first() ?: User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password')
    ]);
});

it('can complete full health tracking workflow', function () {
    // Login using Livewire component
    $component = \Livewire\Volt\Volt::test('pages.auth.login')
        ->set('form.email', $this->user->email)
        ->set('form.password', 'password');

    $component->call('login');
    $component->assertRedirect(route('dashboard', absolute: false));

    // Access dashboard
    $response = $this->actingAs($this->user)->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('Dashboard');

    // Access reports page
    $response = $this->actingAs($this->user)->get('/reports');
    $response->assertStatus(200);
    $response->assertSee('Reports & Analytics');

    // Access settings page
    $response = $this->actingAs($this->user)->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('Settings & Preferences');

    // Test measurement type data endpoints
    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/reports/glucose-data?start_date=' . now()->subDays(7)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'dailyAverages',
        'fastingReadings', 
        'nonFastingReadings'
    ]);

    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/reports/weight-data?start_date=' . now()->subDays(7)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'weights',
        'trend'
    ]);

    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/reports/exercise-data?start_date=' . now()->subDays(7)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'dailyDuration',
        'exerciseTypes'
    ]);
});

it('can export health data', function () {
    // Test CSV export
    $response = $this->actingAs($this->user)
        ->withSession(['_token' => 'test-token'])
        ->post('/reports/export/csv', [
            '_token' => 'test-token',
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'types' => ['glucose', 'weight', 'exercise', 'notes']
        ]);
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    // Test PDF export
    $response = $this->actingAs($this->user)
        ->withSession(['_token' => 'test-token'])
        ->post('/reports/export/pdf', [
            '_token' => 'test-token',
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'types' => ['glucose', 'weight', 'exercise', 'notes']
        ]);
    
    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
});

it('can update user preferences', function () {
    $response = $this->actingAs($this->user)
        ->withSession(['_token' => 'test-token'])
        ->put('/settings', [
            '_token' => 'test-token',
            'glucose_unit' => 'mg/dL',
            'weight_unit' => 'lbs',
            'date_format' => 'm/d/Y',
            'time_format' => '12',
            'timezone' => 'America/New_York',
            'chart_theme' => 'dark',
            'dashboard_layout' => 'list'
        ]);

    $response->assertStatus(302);
    $response->assertRedirect('/settings');
    $response->assertSessionHas('success');

    // Verify preferences were saved
    $this->user->refresh();
    $preferences = json_decode($this->user->preferences, true);
    expect($preferences['glucose_unit'])->toBe('mg/dL');
    expect($preferences['chart_theme'])->toBe('dark');
});

it('handles authentication properly', function () {
    // Unauthenticated users are redirected to login
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');

    $response = $this->get('/reports');
    $response->assertRedirect('/login');

    $response = $this->get('/settings');
    $response->assertRedirect('/login');

    // API endpoints also require authentication
    $response = $this->get('/reports/glucose-data');
    $response->assertRedirect('/login');
});

it('validates form inputs properly', function () {
    // Test invalid date ranges
    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/reports/glucose-data?start_date=invalid&end_date=' . now()->format('Y-m-d'));
    $response->assertStatus(422);

    // Test invalid export parameters
    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->withSession(['_token' => 'test-token'])
        ->post('/reports/export/csv', [
            '_token' => 'test-token',
            'start_date' => 'invalid',
            'end_date' => now()->format('Y-m-d')
        ]);
    $response->assertStatus(422);

    // Test invalid settings
    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->withSession(['_token' => 'test-token'])
        ->put('/settings', [
            '_token' => 'test-token',
            'glucose_unit' => 'invalid-unit',
            'weight_unit' => 'kg',
            'date_format' => 'Y-m-d',
            'time_format' => '24',
            'timezone' => 'UTC',
            'chart_theme' => 'light',
            'dashboard_layout' => 'grid'
        ]);
    $response->assertStatus(422);
});