<?php

namespace App\Livewire;

use App\Repositories\MeasurementRepository;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    public string $selectedDate;
    public array $filterTypes = [];

    public function mount(MeasurementRepository $measurementRepository)
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    public function previousDay()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
    }

    public function nextDay()
    {
        $currentDate = Carbon::parse($this->selectedDate);
        if (!$currentDate->isToday()) {
            $this->selectedDate = $currentDate->addDay()->format('Y-m-d');
        }
    }

    public function goToToday()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }


    public function updatedSelectedDate()
    {
        // Prevent future dates
        $selected = Carbon::parse($this->selectedDate);
        if ($selected->isFuture()) {
            $this->selectedDate = Carbon::today()->format('Y-m-d');
        }
    }

    #[On('measurement-added')]
    #[On('measurement-updated')]
    public function refreshMeasurements()
    {
        // This will trigger a re-render and fetch fresh measurements
    }

    public function editMeasurement($measurementId)
    {
        $this->dispatch('edit-measurement', measurementId: $measurementId);
    }

    public function confirmDelete($measurementId)
    {
        $this->dispatch('confirm-delete-measurement', measurementId: $measurementId);
    }

    public function clearFilters()
    {
        $this->filterTypes = [];
    }

    public function render(MeasurementRepository $measurementRepository)
    {
        // Get measurements based on current filters
        if (!empty($this->filterTypes)) {
            // Use type filtering
            $measurements = $measurementRepository->searchAndFilter(
                auth()->id(),
                '', // no search
                $this->filterTypes,
                Carbon::parse($this->selectedDate),
                Carbon::parse($this->selectedDate),
                'newest'
            );
        } else {
            // Show all today's measurements when no filters applied
            $measurements = $measurementRepository->getByUserAndDate(
                auth()->id(),
                $this->selectedDate
            );
        }

        return view('livewire.dashboard', [
            'measurements' => $measurements,
            'selectedDateFormatted' => Carbon::parse($this->selectedDate)->format('l, F j, Y'),
            'isToday' => Carbon::parse($this->selectedDate)->isToday(),
        ]);
    }
}
