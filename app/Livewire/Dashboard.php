<?php

namespace App\Livewire;

use App\Repositories\MeasurementRepository;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    public string $selectedDate;
    public string $selectedDateDisplay;
    public array $filterTypes = [];

    public function mount(MeasurementRepository $measurementRepository)
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->selectedDateDisplay = Carbon::today()->format('d-m-Y');
    }

    public function previousDay()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
        $this->selectedDateDisplay = Carbon::parse($this->selectedDate)->format('d-m-Y');
    }

    public function nextDay()
    {
        $currentDate = Carbon::parse($this->selectedDate);
        if (!$currentDate->isToday()) {
            $this->selectedDate = $currentDate->addDay()->format('Y-m-d');
            $this->selectedDateDisplay = Carbon::parse($this->selectedDate)->format('d-m-Y');
        }
    }

    public function goToToday()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->selectedDateDisplay = Carbon::today()->format('d-m-Y');
    }


    public function updatedSelectedDate()
    {
        // Prevent future dates
        $selected = Carbon::parse($this->selectedDate);
        if ($selected->isFuture()) {
            $this->selectedDate = Carbon::today()->format('Y-m-d');
        }
    }

    public function updatedSelectedDateDisplay()
    {
        // Parse Dutch format dd-mm-yyyy and convert to internal format
        try {
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $this->selectedDateDisplay, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                
                $carbon = Carbon::createFromFormat('d-m-Y', $this->selectedDateDisplay);
                
                // Prevent future dates
                if ($carbon->isFuture()) {
                    $this->selectedDate = Carbon::today()->format('Y-m-d');
                    $this->selectedDateDisplay = Carbon::today()->format('d-m-Y');
                } else {
                    $this->selectedDate = $carbon->format('Y-m-d');
                }
            } else {
                // Invalid format, reset to today
                $this->selectedDate = Carbon::today()->format('Y-m-d');
                $this->selectedDateDisplay = Carbon::today()->format('d-m-Y');
            }
        } catch (\Exception $e) {
            // Invalid date, reset to today
            $this->selectedDate = Carbon::today()->format('Y-m-d');
            $this->selectedDateDisplay = Carbon::today()->format('d-m-Y');
        }
    }

    #[On('measurement-saved')]
    public function refreshMeasurements()
    {
        // This will trigger a re-render and fetch fresh measurements
    }

    public function openAddMeasurement($type)
    {
        $this->dispatch('open-add-measurement', $type, $this->selectedDate);
    }

    public function openEditMeasurement($measurementId)
    {
        $this->dispatch('open-edit-measurement', $measurementId);
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
            'selectedDateFormatted' => Carbon::parse($this->selectedDate)->format('l, d-m-Y'),
            'isToday' => Carbon::parse($this->selectedDate)->isToday(),
        ]);
    }
}
