<?php

namespace App\Repositories;

use App\Models\MeasurementType;
use Illuminate\Database\Eloquent\Collection;

class MeasurementTypeRepository
{
    public function getAll(): Collection
    {
        return MeasurementType::orderBy('name')->get();
    }

    public function findBySlug(string $slug): ?MeasurementType
    {
        return MeasurementType::where('slug', $slug)->first();
    }

    public function find(int $id): ?MeasurementType
    {
        return MeasurementType::find($id);
    }
}