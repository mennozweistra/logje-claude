<?php

namespace App\Livewire;

use App\Repositories\MeasurementRepository;
use App\Repositories\MeasurementTypeRepository;
use Carbon\Carbon;
use Livewire\Component;

class AddMeasurement extends Component
{
    public string $selectedDate;
    public string $selectedType = '';
    public bool $showForm = false;
    
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

    public function mount(string $date = null)
    {
        $this->selectedDate = $date ?? Carbon::today()->format('Y-m-d');
        $this->setCurrentTime();
    }

    public function setCurrentTime()
    {
        $currentTime = Carbon::now()->format('H:i');
        $this->glucoseTime = $currentTime;
        $this->weightTime = $currentTime;
        $this->exerciseTime = $currentTime;
        $this->notesTime = $currentTime;
    }

    public function selectType(string $type)
    {
        $this->selectedType = $type;
        $this->showForm = true;
        $this->setCurrentTime();
    }

    public function cancel()
    {
        $this->reset(['selectedType', 'showForm', 'glucoseValue', 'isFasting', 'glucoseNotes',
                     'weightValue', 'weightNotes', 'exerciseDescription', 'exerciseDuration', 
                     'exerciseNotes', 'notesContent']);
        $this->setCurrentTime();
    }

    public function save(MeasurementRepository $measurementRepository, MeasurementTypeRepository $measurementTypeRepository)
    {
        $measurementType = $measurementTypeRepository->findBySlug($this->selectedType);
        
        if (!$measurementType) {
            session()->flash('error', 'Invalid measurement type selected.');
            return;
        }

        $data = [
            'user_id' => auth()->id(),
            'measurement_type_id' => $measurementType->id,
            'date' => $this->selectedDate,
        ];

        switch ($this->selectedType) {
            case 'glucose':
                $this->validate([
                    'glucoseValue' => 'required|numeric|min:0|max:50',
                    'glucoseTime' => 'required',
                ]);
                
                $data['value'] = $this->glucoseValue;
                $data['is_fasting'] = $this->isFasting;
                $data['notes'] = $this->glucoseNotes;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->glucoseTime);
                break;

            case 'weight':
                $this->validate([
                    'weightValue' => 'required|numeric|min:0|max:500',
                    'weightTime' => 'required',
                ]);
                
                $data['value'] = $this->weightValue;
                $data['notes'] = $this->weightNotes;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->weightTime);
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
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->exerciseTime);
                break;

            case 'notes':
                $this->validate([
                    'notesContent' => 'required|string|max:1000',
                    'notesTime' => 'required',
                ]);
                
                $data['notes'] = $this->notesContent;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->notesTime);
                break;
        }

        try {
            $measurementRepository->create($data);
            session()->flash('success', 'Measurement added successfully!');
            $this->cancel();
            $this->dispatch('measurement-added');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add measurement. Please try again.');
        }
    }

    public function render(MeasurementTypeRepository $measurementTypeRepository)
    {
        $measurementTypes = $measurementTypeRepository->getAll();
        
        return view('livewire.add-measurement', [
            'measurementTypes' => $measurementTypes,
        ]);
    }
}
