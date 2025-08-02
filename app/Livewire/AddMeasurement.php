<?php

namespace App\Livewire;

use App\Http\Requests\StoreGlucoseMeasurementRequest;
use App\Http\Requests\StoreWeightMeasurementRequest;
use App\Http\Requests\StoreExerciseMeasurementRequest;
use App\Http\Requests\StoreNotesMeasurementRequest;
use App\Repositories\MeasurementRepository;
use App\Repositories\MeasurementTypeRepository;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

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
        try {
            $measurementType = $measurementTypeRepository->findBySlug($this->selectedType);
            
            if (!$measurementType) {
                session()->flash('error', 'Invalid measurement type selected.');
                return;
            }

            // Validate using appropriate form request rules
            $validatedData = $this->validateMeasurement();
            
            $data = [
                'user_id' => auth()->id(),
                'measurement_type_id' => $measurementType->id,
                'date' => $this->selectedDate,
            ];

            switch ($this->selectedType) {
                case 'glucose':
                    $data['value'] = $this->glucoseValue;
                    $data['is_fasting'] = $this->isFasting;
                    $data['notes'] = $this->glucoseNotes;
                    $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->glucoseTime);
                    break;

                case 'weight':
                    $data['value'] = $this->weightValue;
                    $data['notes'] = $this->weightNotes;
                    $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->weightTime);
                    break;

                case 'exercise':
                    $data['description'] = $this->exerciseDescription;
                    $data['duration'] = $this->exerciseDuration;
                    $data['notes'] = $this->exerciseNotes;
                    $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->exerciseTime);
                    break;

                case 'notes':
                    $data['notes'] = $this->notesContent;
                    $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->notesTime);
                    break;
            }

            // Check for duplicate timestamps
            $this->checkDuplicateTimestamp($data['created_at'], $measurementType->id);

            $measurementRepository->create($data);
            session()->flash('success', 'Measurement added successfully!');
            $this->cancel();
            $this->dispatch('measurement-added');
            
        } catch (ValidationException $e) {
            // Re-throw validation exceptions to show field errors
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add measurement: ' . $e->getMessage());
        }
    }

    private function validateMeasurement(): array
    {
        switch ($this->selectedType) {
            case 'glucose':
                return $this->validate([
                    'glucoseValue' => [
                        'required',
                        'numeric',
                        'min:0.1',
                        'max:50',
                        'regex:/^\d+(\.\d{1,2})?$/',
                    ],
                    'glucoseTime' => 'required|date_format:H:i',
                    'glucoseNotes' => 'nullable|string|max:1000',
                ], [
                    'glucoseValue.required' => 'Blood glucose level is required.',
                    'glucoseValue.numeric' => 'Blood glucose level must be a number.',
                    'glucoseValue.min' => 'Blood glucose level must be at least 0.1 mmol/L.',
                    'glucoseValue.max' => 'Blood glucose level seems too high (max 50 mmol/L). Please check your reading.',
                    'glucoseValue.regex' => 'Blood glucose level can have at most 2 decimal places.',
                    'glucoseTime.required' => 'Time is required.',
                    'glucoseTime.date_format' => 'Please enter a valid time in HH:MM format.',
                    'glucoseNotes.max' => 'Notes cannot exceed 1000 characters.',
                ]);
                
            case 'weight':
                return $this->validate([
                    'weightValue' => [
                        'required',
                        'numeric',
                        'min:0.1',
                        'max:500',
                        'regex:/^\d+(\.\d{1,2})?$/',
                    ],
                    'weightTime' => 'required|date_format:H:i',
                    'weightNotes' => 'nullable|string|max:1000',
                ], [
                    'weightValue.required' => 'Weight is required.',
                    'weightValue.numeric' => 'Weight must be a number.',
                    'weightValue.min' => 'Weight must be at least 0.1 kg.',
                    'weightValue.max' => 'Weight seems too high (max 500 kg). Please check your reading.',
                    'weightValue.regex' => 'Weight can have at most 2 decimal places.',
                    'weightTime.required' => 'Time is required.',
                    'weightTime.date_format' => 'Please enter a valid time in HH:MM format.',
                    'weightNotes.max' => 'Notes cannot exceed 1000 characters.',
                ]);
                
            case 'exercise':
                return $this->validate([
                    'exerciseDescription' => [
                        'required',
                        'string',
                        'min:2',
                        'max:255',
                        'regex:/^[\w\s\-\.,!]+$/',
                    ],
                    'exerciseDuration' => [
                        'required',
                        'integer',
                        'min:1',
                        'max:1440',
                    ],
                    'exerciseTime' => 'required|date_format:H:i',
                    'exerciseNotes' => 'nullable|string|max:1000',
                ], [
                    'exerciseDescription.required' => 'Exercise description is required.',
                    'exerciseDescription.min' => 'Exercise description must be at least 2 characters.',
                    'exerciseDescription.max' => 'Exercise description cannot exceed 255 characters.',
                    'exerciseDescription.regex' => 'Exercise description contains invalid characters.',
                    'exerciseDuration.required' => 'Exercise duration is required.',
                    'exerciseDuration.integer' => 'Exercise duration must be a whole number of minutes.',
                    'exerciseDuration.min' => 'Exercise duration must be at least 1 minute.',
                    'exerciseDuration.max' => 'Exercise duration cannot exceed 24 hours (1440 minutes).',
                    'exerciseTime.required' => 'Time is required.',
                    'exerciseTime.date_format' => 'Please enter a valid time in HH:MM format.',
                    'exerciseNotes.max' => 'Notes cannot exceed 1000 characters.',
                ]);
                
            case 'notes':
                return $this->validate([
                    'notesContent' => [
                        'required',
                        'string',
                        'min:1',
                        'max:2000',
                    ],
                    'notesTime' => 'required|date_format:H:i',
                ], [
                    'notesContent.required' => 'Daily notes content is required.',
                    'notesContent.min' => 'Daily notes must contain at least some content.',
                    'notesContent.max' => 'Daily notes cannot exceed 2000 characters.',
                    'notesTime.required' => 'Time is required.',
                    'notesTime.date_format' => 'Please enter a valid time in HH:MM format.',
                ]);
                
            default:
                throw new \InvalidArgumentException('Invalid measurement type: ' . $this->selectedType);
        }
    }

    private function checkDuplicateTimestamp($timestamp, $measurementTypeId)
    {
        $existingMeasurement = \App\Models\Measurement::where('user_id', auth()->id())
            ->where('measurement_type_id', $measurementTypeId)
            ->where('created_at', $timestamp)
            ->first();
            
        if ($existingMeasurement) {
            throw new \Exception('A measurement of this type already exists at this exact time. Please choose a different time.');
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
