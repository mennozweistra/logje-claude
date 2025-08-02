<?php

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Carbon\Carbon;

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
});

it('can access reports index page when authenticated', function () {
    $response = $this->actingAs($this->user)->get('/reports');

    $response->assertStatus(200);
    $response->assertViewIs('reports.index');
});

it('redirects to login when accessing reports unauthenticated', function () {
    $response = $this->get('/reports');

    $response->assertRedirect('/login');
});

it('returns glucose data for charts', function () {
    // Create test glucose measurements
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 5.5,
        'is_fasting' => true,
        'date' => Carbon::today()->subDays(2)
    ]);

    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 6.2,
        'is_fasting' => false,
        'date' => Carbon::today()->subDays(2)
    ]);

    $response = $this->actingAs($this->user)->get('/reports/glucose-data?' . http_build_query([
        'start_date' => Carbon::today()->subDays(7)->format('Y-m-d'),
        'end_date' => Carbon::today()->format('Y-m-d')
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'dailyAverages',
        'fastingReadings',
        'nonFastingReadings'
    ]);

    $data = $response->json();
    expect($data['dailyAverages'])->toHaveCount(1);
    expect($data['fastingReadings'])->toHaveCount(1);
    expect($data['nonFastingReadings'])->toHaveCount(1);
});

it('returns weight data for charts', function () {
    // Create test weight measurements
    Measurement::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->weightType->id,
        'value' => 75.0,
        'date' => Carbon::today()->subDays(rand(1, 5))
    ]);

    $response = $this->actingAs($this->user)->get('/reports/weight-data?' . http_build_query([
        'start_date' => Carbon::today()->subDays(7)->format('Y-m-d'),
        'end_date' => Carbon::today()->format('Y-m-d')
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'weights',
        'trend'
    ]);

    $data = $response->json();
    expect($data['weights'])->toHaveCount(3);
});

it('returns exercise data for charts', function () {
    // Create test exercise measurements
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->exerciseType->id,
        'duration' => 30,
        'description' => 'Running',
        'date' => Carbon::today()->subDays(2)
    ]);

    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->exerciseType->id,
        'duration' => 45,
        'description' => 'Cycling',
        'date' => Carbon::today()->subDays(1)
    ]);

    $response = $this->actingAs($this->user)->get('/reports/exercise-data?' . http_build_query([
        'start_date' => Carbon::today()->subDays(7)->format('Y-m-d'),
        'end_date' => Carbon::today()->format('Y-m-d')
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'dailyDuration',
        'exerciseTypes'
    ]);

    $data = $response->json();
    expect($data['dailyDuration'])->toHaveCount(2);
    expect($data['exerciseTypes'])->toHaveKey('Running');
    expect($data['exerciseTypes'])->toHaveKey('Cycling');
});

it('validates date range parameters', function () {
    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/reports/glucose-data?' . http_build_query([
            'start_date' => 'invalid-date',
            'end_date' => Carbon::today()->format('Y-m-d')
        ]));

    $response->assertStatus(422);
});

it('validates end date is after start date', function () {
    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/reports/glucose-data?' . http_build_query([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::yesterday()->format('Y-m-d')
        ]));

    $response->assertStatus(422);
});

it('can export data as CSV', function () {
    // Create test measurements
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 5.5,
        'date' => Carbon::today()->subDays(1)
    ]);

    $response = $this->actingAs($this->user)
        ->withSession(['_token' => 'test-token'])
        ->post('/reports/export/csv', [
            '_token' => 'test-token',
            'start_date' => Carbon::today()->subDays(7)->format('Y-m-d'),
            'end_date' => Carbon::today()->format('Y-m-d'),
            'types' => ['glucose']
        ]);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('can export data as PDF', function () {
    // Create test measurements
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->weightType->id,
        'value' => 75.0,
        'date' => Carbon::today()->subDays(1)
    ]);

    $response = $this->actingAs($this->user)
        ->withSession(['_token' => 'test-token'])
        ->post('/reports/export/pdf', [
            '_token' => 'test-token',
            'start_date' => Carbon::today()->subDays(7)->format('Y-m-d'),
            'end_date' => Carbon::today()->format('Y-m-d'),
            'types' => ['weight']
        ]);

    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('validates export parameters', function () {
    $response = $this->actingAs($this->user)
        ->withHeaders(['Accept' => 'application/json'])
        ->withSession(['_token' => 'test-token'])
        ->post('/reports/export/csv', [
            '_token' => 'test-token',
            'start_date' => 'invalid-date',
            'end_date' => Carbon::today()->format('Y-m-d')
        ]);

    $response->assertStatus(422);
});

it('only returns current user data', function () {
    $otherUser = User::factory()->create();
    
    // Create measurement for current user
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 5.5,
        'date' => Carbon::today()->subDays(1)
    ]);

    // Create measurement for other user
    Measurement::factory()->create([
        'user_id' => $otherUser->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 6.5,
        'date' => Carbon::today()->subDays(1)
    ]);

    $response = $this->actingAs($this->user)->get('/reports/glucose-data?' . http_build_query([
        'start_date' => Carbon::today()->subDays(7)->format('Y-m-d'),
        'end_date' => Carbon::today()->format('Y-m-d')
    ]));

    $response->assertStatus(200);
    $data = $response->json();
    
    // Should only return data for current user
    expect($data['dailyAverages'])->toHaveCount(1);
    expect($data['dailyAverages'][0]['y'])->toBe(5.5);
});