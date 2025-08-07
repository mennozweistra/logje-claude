<?php

namespace App\Livewire;

use App\Services\HealthyDayService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class HealthIndicator extends Component
{
    public string $selectedDate;
    public bool $modalVisible = false;
    public array $ruleStatuses = [];
    public bool $isHealthy = false;

    private HealthyDayService $healthyDayService;

    public function boot(HealthyDayService $healthyDayService)
    {
        $this->healthyDayService = $healthyDayService;
    }

    public function mount(string $selectedDate = null)
    {
        $this->selectedDate = $selectedDate ?? Carbon::today()->format('Y-m-d');
        $this->evaluateHealthStatus();
    }

    public function updatedSelectedDate()
    {
        $this->evaluateHealthStatus();
        $this->closeModal(); // Close modal when date changes
    }

    #[On('measurement-saved')]
    #[On('measurement-updated')]
    #[On('measurement-deleted')]
    public function refreshHealthStatus()
    {
        $this->evaluateHealthStatus();
    }

    #[On('dashboard-date-changed')]
    public function updateSelectedDate(string $date)
    {
        $this->selectedDate = $date;
        $this->evaluateHealthStatus();
        $this->closeModal();
    }

    public function toggleModal()
    {
        $this->modalVisible = !$this->modalVisible;
        
        if ($this->modalVisible) {
            $this->loadDetailedRuleStatus();
        }
    }

    public function closeModal()
    {
        $this->modalVisible = false;
    }

    private function evaluateHealthStatus()
    {
        $user = auth()->user();
        $date = Carbon::parse($this->selectedDate);
        
        $this->isHealthy = $this->healthyDayService->isHealthyDay($user, $date);
    }

    private function loadDetailedRuleStatus()
    {
        $user = auth()->user();
        $date = Carbon::parse($this->selectedDate);
        
        $this->ruleStatuses = $this->healthyDayService->getRuleStatuses($user, $date);
    }

    public function render()
    {
        return view('livewire.health-indicator');
    }
}