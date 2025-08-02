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

    public function render(MeasurementRepository $measurementRepository)
    {
        $measurements = $measurementRepository->getByUserAndDate(
            auth()->id(),
            $this->selectedDate
        );

        return view('livewire.dashboard', [
            'measurements' => $measurements,
            'selectedDateFormatted' => Carbon::parse($this->selectedDate)->format('l, F j, Y'),
            'isToday' => Carbon::parse($this->selectedDate)->isToday(),
        ]);
    }
}
