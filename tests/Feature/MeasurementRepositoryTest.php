<?php

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use App\Repositories\MeasurementRepository;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->glucoseType = MeasurementType::where('slug', 'glucose')->first();
    $this->weightType = MeasurementType::where('slug', 'weight')->first();
    $this->repository = new MeasurementRepository();
});

it('can store a glucose measurement', function () {
    $measurementData = [
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 5.5,
        'is_fasting' => true,
        'date' => Carbon::today(),
        'notes' => 'Morning reading'
    ];

    $measurement = $this->repository->store($measurementData);

    expect($measurement)->toBeInstanceOf(Measurement::class);
    expect((float) $measurement->value)->toBe(5.5);
    expect($measurement->is_fasting)->toBeTrue();
    expect($measurement->notes)->toBe('Morning reading');
});

it('can store a weight measurement', function () {
    $measurementData = [
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->weightType->id,
        'value' => 75.2,
        'date' => Carbon::today(),
        'notes' => 'After workout'
    ];

    $measurement = $this->repository->store($measurementData);

    expect($measurement)->toBeInstanceOf(Measurement::class);
    expect((float) $measurement->value)->toBe(75.2);
    expect($measurement->notes)->toBe('After workout');
});

it('can retrieve measurements by user and date range', function () {
    $startDate = Carbon::today()->subDays(7);
    $endDate = Carbon::today();

    // Create measurements within range
    Measurement::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'date' => Carbon::today()->subDays(3)
    ]);

    // Create measurement outside range
    Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'date' => Carbon::today()->subDays(10)
    ]);

    // Create measurement for different user
    Measurement::factory()->create([
        'user_id' => User::factory()->create()->id,
        'measurement_type_id' => $this->glucoseType->id,
        'date' => Carbon::today()->subDays(3)
    ]);

    $measurements = $this->repository->getUserMeasurementsByDateRange(
        $this->user->id,
        $startDate,
        $endDate
    );

    expect($measurements)->toHaveCount(3);
});

it('can retrieve measurements by type and date range', function () {
    $startDate = Carbon::today()->subDays(7);
    $endDate = Carbon::today();

    // Create glucose measurements
    Measurement::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'date' => Carbon::today()->subDays(3)
    ]);

    // Create weight measurements
    Measurement::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->weightType->id,
        'date' => Carbon::today()->subDays(3)
    ]);

    $glucoseMeasurements = $this->repository->getUserMeasurementsByTypeAndDateRange(
        $this->user->id,
        'glucose',
        $startDate,
        $endDate
    );

    $weightMeasurements = $this->repository->getUserMeasurementsByTypeAndDateRange(
        $this->user->id,
        'weight',
        $startDate,
        $endDate
    );

    expect($glucoseMeasurements)->toHaveCount(2);
    expect($weightMeasurements)->toHaveCount(3);
});

it('can update a measurement', function () {
    $measurement = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'value' => 5.5,
        'notes' => 'Original note'
    ]);

    $updatedData = [
        'value' => 6.2,
        'notes' => 'Updated note'
    ];

    $updatedMeasurement = $this->repository->update($measurement->id, $updatedData);

    expect((float) $updatedMeasurement->value)->toBe(6.2);
    expect($updatedMeasurement->notes)->toBe('Updated note');
});

it('can delete a measurement', function () {
    $measurement = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id
    ]);

    $deleted = $this->repository->delete($measurement->id);

    expect($deleted)->toBeTrue();
    expect(Measurement::find($measurement->id))->toBeNull();
});

it('returns measurements ordered by date and time desc', function () {
    $baseDate = Carbon::today();
    
    $measurement1 = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'date' => $baseDate,
        'created_at' => $baseDate->copy()->setTime(8, 0)
    ]);

    $measurement2 = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'date' => $baseDate,
        'created_at' => $baseDate->copy()->setTime(12, 0)
    ]);

    $measurement3 = Measurement::factory()->create([
        'user_id' => $this->user->id,
        'measurement_type_id' => $this->glucoseType->id,
        'date' => $baseDate->copy()->addDay(),
        'created_at' => $baseDate->copy()->addDay()->setTime(8, 0)
    ]);

    $measurements = $this->repository->getUserMeasurementsByDateRange(
        $this->user->id,
        $baseDate->copy()->subDay(),
        $baseDate->copy()->addDays(2)
    );

    expect($measurements->pluck('id')->toArray())->toEqual([
        $measurement3->id,
        $measurement2->id,
        $measurement1->id
    ]);
});

describe('Update Tests (Task 8)', function () {
    it('can update glucose measurement with all fields', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5,
            'is_fasting' => false,
            'notes' => 'Original note',
            'created_at' => Carbon::today()->setTime(8, 0)
        ]);

        $updateData = [
            'value' => 6.8,
            'is_fasting' => true,
            'notes' => 'Updated note',
            'created_at' => Carbon::today()->setTime(9, 30)
        ];

        $updatedMeasurement = $this->repository->update($measurement->id, $updateData);

        expect((float) $updatedMeasurement->value)->toBe(6.8);
        expect($updatedMeasurement->is_fasting)->toBeTrue();
        expect($updatedMeasurement->notes)->toBe('Updated note');
        // Repository updates created_at if provided
        expect($updatedMeasurement->created_at)->toBeInstanceOf(Carbon::class);
    });

    it('can update weight measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->weightType->id,
            'value' => 75.5,
            'notes' => 'Original note'
        ]);

        $updateData = [
            'value' => 76.2,
            'notes' => 'Updated weight note'
        ];

        $updatedMeasurement = $this->repository->update($measurement->id, $updateData);

        expect((float) $updatedMeasurement->value)->toBe(76.2);
        expect($updatedMeasurement->notes)->toBe('Updated weight note');
    });

    it('can update exercise measurement', function () {
        $exerciseType = MeasurementType::where('slug', 'exercise')->first();

        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $exerciseType->id,
            'description' => 'Running',
            'duration' => 30,
            'notes' => 'Original note'
        ]);

        $updateData = [
            'description' => 'Cycling',
            'duration' => 45,
            'notes' => 'Updated exercise note'
        ];

        $updatedMeasurement = $this->repository->update($measurement->id, $updateData);

        expect($updatedMeasurement->description)->toBe('Cycling');
        expect($updatedMeasurement->duration)->toBe(45);
        expect($updatedMeasurement->notes)->toBe('Updated exercise note');
    });

    it('can update notes measurement', function () {
        $notesType = MeasurementType::where('slug', 'notes')->first();

        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $notesType->id,
            'notes' => 'Original note'
        ]);

        $updateData = [
            'notes' => 'Updated daily note'
        ];

        $updatedMeasurement = $this->repository->update($measurement->id, $updateData);

        expect($updatedMeasurement->notes)->toBe('Updated daily note');
    });

    it('returns fresh instance after update', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5,
            'notes' => 'Original note'
        ]);

        $originalId = $measurement->id;
        $originalUpdatedAt = $measurement->updated_at;

        // Wait a moment to ensure updated_at changes
        sleep(1);

        $updatedMeasurement = $this->repository->update($measurement->id, [
            'value' => 6.2,
            'notes' => 'Updated note'
        ]);

        expect($updatedMeasurement->id)->toBe($originalId);
        expect($updatedMeasurement->updated_at)->not->toEqual($originalUpdatedAt);
        expect((float) $updatedMeasurement->value)->toBe(6.2);
        expect($updatedMeasurement->notes)->toBe('Updated note');
    });

    it('throws exception when updating non-existent measurement', function () {
        expect(fn() => $this->repository->update(999999, ['value' => 5.5]))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

describe('Delete Tests (Task 8)', function () {
    it('can delete a measurement with soft delete', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id
        ]);

        $measurementId = $measurement->id;
        $deleted = $this->repository->delete($measurementId);

        expect($deleted)->toBeTrue();
        expect(Measurement::find($measurementId))->toBeNull();
        expect(Measurement::withTrashed()->find($measurementId))->not->toBeNull();
    });

    it('returns true when successfully deleting', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id
        ]);

        $result = $this->repository->delete($measurement->id);

        expect($result)->toBeTrue();
    });

    it('throws exception when deleting non-existent measurement', function () {
        expect(fn() => $this->repository->delete(999999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('deleted measurements are not included in regular queries', function () {
        $measurement1 = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'date' => Carbon::today()
        ]);

        $measurement2 = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'date' => Carbon::today()
        ]);

        // Delete one measurement
        $this->repository->delete($measurement1->id);

        // Query should only return the non-deleted measurement
        $measurements = $this->repository->getByUserAndDate($this->user->id, Carbon::today()->format('Y-m-d'));

        expect($measurements)->toHaveCount(1);
        expect($measurements->first()->id)->toBe($measurement2->id);
    });
});

describe('Find Method Tests (Task 8)', function () {
    it('can find measurement with relationships', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id,
            'value' => 5.5
        ]);

        $foundMeasurement = $this->repository->find($measurement->id);

        expect($foundMeasurement)->not->toBeNull();
        expect($foundMeasurement->id)->toBe($measurement->id);
        expect($foundMeasurement->measurementType)->not->toBeNull();
        expect($foundMeasurement->user)->not->toBeNull();
        expect($foundMeasurement->measurementType->name)->toBe('Glucose');
        expect($foundMeasurement->user->id)->toBe($this->user->id);
    });

    it('returns null for non-existent measurement', function () {
        $foundMeasurement = $this->repository->find(999999);

        expect($foundMeasurement)->toBeNull();
    });

    it('returns null for soft deleted measurement', function () {
        $measurement = Measurement::factory()->create([
            'user_id' => $this->user->id,
            'measurement_type_id' => $this->glucoseType->id
        ]);

        $measurementId = $measurement->id;
        $this->repository->delete($measurementId);

        $foundMeasurement = $this->repository->find($measurementId);

        expect($foundMeasurement)->toBeNull();
    });
});