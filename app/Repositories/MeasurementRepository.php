<?php

namespace App\Repositories;

use App\Models\Measurement;
use Illuminate\Database\Eloquent\Collection;

class MeasurementRepository
{
    public function getByUser(int $userId): Collection
    {
        return Measurement::where('user_id', $userId)
            ->with('measurementType')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUserAndDate(int $userId, string $date): Collection
    {
        return Measurement::where('user_id', $userId)
            ->whereDate('date', $date)
            ->with('measurementType')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getByUserAndType(int $userId, string $type): Collection
    {
        return Measurement::where('user_id', $userId)
            ->ofType($type)
            ->with('measurementType')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): Measurement
    {
        return Measurement::create($data);
    }

    public function update(int $id, array $data): Measurement
    {
        $measurement = Measurement::findOrFail($id);
        $measurement->update($data);
        return $measurement->fresh();
    }

    public function delete(int $id): bool
    {
        $measurement = Measurement::findOrFail($id);
        return $measurement->delete();
    }

    public function find(int $id): ?Measurement
    {
        return Measurement::with('measurementType', 'user')->find($id);
    }
}