<?php

namespace App\Livewire;

use App\Repositories\MeasurementRepository;
use App\Repositories\MeasurementTypeRepository;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class EditMeasurement extends Component
{
    public ?int $measurementId = null;
    public $measurement = null;
    public bool $showEditForm = false;
    public bool $showDeleteConfirm = false;
    
    // Glucose fields
    public string $glucoseValue = '';
    public bool $isFasting = false;
    public string $glucoseTime = '';
    public string $glucoseNotes = '';
    
    // Weight fields
    public string $weightValue = '';
    public string $weightTime = '';
    public string $weightNotes = '';
    
    // Exercise fields
    public string $exerciseDescription = '';
    public string $exerciseDuration = '';
    public string $exerciseTime = '';
    public string $exerciseNotes = '';
    
    // Notes fields
    public string $notesTime = '';
    public string $notesContent = '';

    #[On('edit-measurement')]
    public function startEdit($measurementId, MeasurementRepository $measurementRepository)
    {
        $this->measurement = $measurementRepository->find($measurementId);
        
        if (!$this->measurement || $this->measurement->user_id !== auth()->id()) {
            session()->flash('error', 'Measurement not found.');
            return;
        }

        $this->measurementId = $measurementId;
        $this->showEditForm = true;
        $this->showDeleteConfirm = false;
        $this->loadMeasurementData();
    }

    #[On('confirm-delete-measurement')]
    public function confirmDelete($measurementId, MeasurementRepository $measurementRepository)
    {
        $this->measurement = $measurementRepository->find($measurementId);
        
        if (!$this->measurement || $this->measurement->user_id !== auth()->id()) {
            session()->flash('error', 'Measurement not found.');
            return;
        }

        $this->measurementId = $measurementId;
        $this->showDeleteConfirm = true;
        $this->showEditForm = false;
    }

    public function loadMeasurementData()
    {
        if (!$this->measurement) return;

        $type = $this->measurement->measurementType->slug;
        $time = $this->measurement->created_at->format('H:i');

        switch ($type) {
            case 'glucose':
                $this->glucoseValue = $this->measurement->value ?? '';
                $this->isFasting = $this->measurement->is_fasting ?? false;
                $this->glucoseTime = $time;
                $this->glucoseNotes = $this->measurement->notes ?? '';
                break;

            case 'weight':
                $this->weightValue = $this->measurement->value ?? '';
                $this->weightTime = $time;
                $this->weightNotes = $this->measurement->notes ?? '';
                break;

            case 'exercise':
                $this->exerciseDescription = $this->measurement->description ?? '';
                $this->exerciseDuration = $this->measurement->duration ?? '';
                $this->exerciseTime = $time;
                $this->exerciseNotes = $this->measurement->notes ?? '';
                break;

            case 'notes':
                $this->notesTime = $time;
                $this->notesContent = $this->measurement->notes ?? '';
                break;
        }
    }

    public function update(MeasurementRepository $measurementRepository)
    {
        if (!$this->measurement) {
            session()->flash('error', 'Measurement not found.');
            return;
        }

        $type = $this->measurement->measurementType->slug;
        $data = [];

        switch ($type) {
            case 'glucose':
                $this->validate([
                    'glucoseValue' => 'required|numeric|min:0|max:50',
                    'glucoseTime' => 'required',
                ]);
                
                $data['value'] = $this->glucoseValue;
                $data['is_fasting'] = $this->isFasting;
                $data['notes'] = $this->glucoseNotes;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->glucoseTime);
                break;

            case 'weight':
                $this->validate([
                    'weightValue' => 'required|numeric|min:0|max:500',
                    'weightTime' => 'required',
                ]);
                
                $data['value'] = $this->weightValue;
                $data['notes'] = $this->weightNotes;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->weightTime);
                break;

            case 'exercise':
                $this->validate([
                    'exerciseDescription' => 'required|string|max:255',
                    'exerciseDuration' => 'required|integer|min:1|max:1440',
                    'exerciseTime' => 'required',
                ]);
                
                $data['description'] = $this->exerciseDescription;
                $data['duration'] = $this->exerciseDuration;
                $data['notes'] = $this->exerciseNotes;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->exerciseTime);
                break;

            case 'notes':
                $this->validate([
                    'notesContent' => 'required|string|max:1000',
                    'notesTime' => 'required',
                ]);
                
                $data['notes'] = $this->notesContent;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->notesTime);
                break;
        }

        try {
            $measurementRepository->update($this->measurementId, $data);
            session()->flash('success', 'Measurement updated successfully!');
            $this->cancel();
            $this->dispatch('measurement-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update measurement. Please try again.');
        }
    }

    public function delete(MeasurementRepository $measurementRepository)
    {
        if (!$this->measurement) {
            session()->flash('error', 'Measurement not found.');
            return;
        }

        try {
            $measurementRepository->delete($this->measurementId);
            session()->flash('success', 'Measurement deleted successfully!');
            $this->cancel();
            $this->dispatch('measurement-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete measurement. Please try again.');
        }
    }

    public function cancel()
    {
        $this->reset(['measurementId', 'measurement', 'showEditForm', 'showDeleteConfirm',
                     'glucoseValue', 'isFasting', 'glucoseTime', 'glucoseNotes',
                     'weightValue', 'weightTime', 'weightNotes', 
                     'exerciseDescription', 'exerciseDuration', 'exerciseTime', 'exerciseNotes',
                     'notesTime', 'notesContent']);
    }

    public function render()
    {
        return view('livewire.edit-measurement');
    }
}
