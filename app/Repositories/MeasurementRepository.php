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
            ->orderBy('created_at', 'desc')
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

    public function store(array $data): Measurement
    {
        return $this->create($data);
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

    public function getUserMeasurementsByDateRange(int $userId, $startDate, $endDate): Collection
    {
        return Measurement::where('user_id', $userId)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('measurementType')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUserMeasurementsByTypeAndDateRange(int $userId, string $type, $startDate, $endDate): Collection
    {
        return Measurement::where('user_id', $userId)
            ->ofType($type)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('measurementType')
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function searchAndFilter(int $userId, string $search = '', array $filterTypes = [], $startDate = null, $endDate = null, string $sortBy = 'newest'): Collection
    {
        $query = Measurement::where('user_id', $userId)
            ->with('measurementType');

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('value', 'like', '%' . $search . '%')
                  ->orWhereHas('measurementType', function ($typeQuery) use ($search) {
                      $typeQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Apply type filters
        if (!empty($filterTypes)) {
            $query->whereHas('measurementType', function ($typeQuery) use ($filterTypes) {
                $typeQuery->whereIn('slug', $filterTypes);
            });
        }

        // Apply date range filter
        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        // Apply sorting
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('date', 'asc')->orderBy('created_at', 'asc');
                break;
            case 'value_high':
                $query->orderBy('value', 'desc');
                break;
            case 'value_low':
                $query->orderBy('value', 'asc');
                break;
            case 'type':
                $query->join('measurement_types', 'measurements.measurement_type_id', '=', 'measurement_types.id')
                      ->orderBy('measurement_types.name', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
                break;
        }

        return $query->get();
    }
}