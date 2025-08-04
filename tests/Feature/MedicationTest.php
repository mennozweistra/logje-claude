<?php

use App\Models\Medication;
use App\Models\Measurement;
use App\Models\User;
use App\Models\MeasurementType;

test('it can create a medication', function () {
    $medication = Medication::create([
        'name' => 'Test Medication',
        'description' => 'Test description',
    ]);

    expect($medication)->toBeInstanceOf(Medication::class);
    expect($medication->name)->toBe('Test Medication');
    expect($medication->description)->toBe('Test description');
});

test('it has fillable attributes', function () {
    $medication = new Medication();
    
    expect($medication->getFillable())->toEqual(['name', 'description', 'user_id']);
});

test('it can be associated with measurements', function () {
    $user = User::factory()->create();
    $medicationType = MeasurementType::where('slug', 'medication')->first();
    
    $measurement = Measurement::factory()->create([
        'user_id' => $user->id,
        'measurement_type_id' => $medicationType->id,
    ]);

    $medication = Medication::factory()->create();

    $measurement->medications()->attach($medication);

    expect($measurement->medications->contains($medication))->toBeTrue();
    expect($medication->measurements->contains($measurement))->toBeTrue();
});

test('it can have multiple measurements', function () {
    $user = User::factory()->create();
    $medicationType = MeasurementType::where('slug', 'medication')->first();

    $medication = Medication::factory()->create();
    
    $measurement1 = Measurement::factory()->create([
        'user_id' => $user->id,
        'measurement_type_id' => $medicationType->id,
    ]);
    
    $measurement2 = Measurement::factory()->create([
        'user_id' => $user->id,
        'measurement_type_id' => $medicationType->id,
    ]);

    $medication->measurements()->attach([$measurement1->id, $measurement2->id]);

    expect($medication->fresh()->measurements)->toHaveCount(2);
});

test('it cascades delete to pivot table', function () {
    $user = User::factory()->create();
    $medicationType = MeasurementType::where('slug', 'medication')->first();
    
    $measurement = Measurement::factory()->create([
        'user_id' => $user->id,
        'measurement_type_id' => $medicationType->id,
    ]);

    $medication = Medication::factory()->create();
    $measurement->medications()->attach($medication);

    $this->assertDatabaseHas('measurement_medication', [
        'measurement_id' => $measurement->id,
        'medication_id' => $medication->id,
    ]);

    $medication->delete();

    $this->assertDatabaseMissing('measurement_medication', [
        'measurement_id' => $measurement->id,
        'medication_id' => $medication->id,
    ]);
});