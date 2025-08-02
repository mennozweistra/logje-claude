<?php

namespace App\Livewire;

use App\Repositories\MeasurementRepository;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    public string $selectedDate;
    public bool $detailedView = false;
    public string $search = '';
    public array $filterTypes = [];
    public string $sortBy = 'newest';
    public int $dateRange = 1; // days to look back

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

    public function toggleView()
    {
        $this->detailedView = !$this->detailedView;
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
        $this->search = '';
        $this->filterTypes = [];
        $this->sortBy = 'newest';
        $this->dateRange = 1;
    }

    public function render(MeasurementRepository $measurementRepository)
    {
        // Get measurements based on current filters
        if ($this->search || !empty($this->filterTypes) || $this->dateRange != 1) {
            // Use search and filter with date range
            $startDate = Carbon::parse($this->selectedDate)->subDays($this->dateRange - 1);
            $endDate = Carbon::parse($this->selectedDate);
            
            $measurements = $measurementRepository->searchAndFilter(
                auth()->id(),
                $this->search,
                $this->filterTypes,
                $startDate,
                $endDate,
                $this->sortBy
            );
        } else {
            // Show only today's measurements when no filters applied
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
