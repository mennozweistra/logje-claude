<?php

namespace App\Livewire;

use App\Repositories\MeasurementRepository;
use App\Repositories\MeasurementTypeRepository;
use App\Models\Medication;
use App\Models\Food;
use App\Models\FoodMeasurement;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class MeasurementModal extends Component
{
    // Modal state
    public bool $showModal = false;
    public string $mode = 'add'; // 'add' or 'edit'
    public string $selectedDate = '';
    public string $selectedType = '';
    public ?int $measurementId = null;
    public $measurement = null;
    
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

    // Medication fields
    public string $medicationTime = '';
    public array $selectedMedications = [];
    public string $medicationNotes = '';

    // Food fields
    public string $foodTime = '';
    public array $foodEntries = []; // Array of [food_id => grams] pairs
    public string $foodNotes = '';
    public string $foodSearch = '';
    public array $searchResults = [];

    // Delete confirmation
    public bool $showDeleteConfirm = false;

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
            
    }

    #[On('open-add-measurement')]
    public function openAddMeasurement($type, $date = null)
    {
        $this->mode = 'add';
        $this->selectedType = $type;
        $this->selectedDate = $date ?? Carbon::today()->format('Y-m-d');
        $this->showModal = true;
        $this->showDeleteConfirm = false;
        $this->resetFormFields();
        $this->setCurrentTime();
    }

    #[On('open-edit-measurement')]
    public function openEditMeasurement($measurementId, MeasurementRepository $measurementRepository)
    {
        $this->measurement = $measurementRepository->find($measurementId);
        
        if (!$this->measurement || $this->measurement->user_id !== auth()->id()) {
            session()->flash('error', 'Measurement not found.');
            return;
        }

        $this->mode = 'edit';
        $this->measurementId = $measurementId;
        $this->selectedType = $this->measurement->measurementType->slug;
        $this->selectedDate = $this->measurement->date->format('Y-m-d');
        $this->showModal = true;
        $this->showDeleteConfirm = false;
        $this->loadMeasurementData();
    }

    public function setCurrentTime()
    {
        $currentTime = Carbon::now()->format('H:i');
        Log::info('Current time is: ' . $currentTime);
        Log::info('Current timezone is: ' . date_default_timezone_get());

        $this->glucoseTime = $currentTime;
        $this->weightTime = $currentTime;
        $this->exerciseTime = $currentTime;
        $this->notesTime = $currentTime;
        $this->medicationTime = $currentTime;
        $this->foodTime = $currentTime;
    }

    public function loadMeasurementData()
    {
        if (!$this->measurement) return;

        $time = $this->measurement->created_at->format('H:i');

        switch ($this->selectedType) {
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

            case 'medication':
                $this->medicationTime = $time;
                $this->medicationNotes = $this->measurement->notes ?? '';
                $this->selectedMedications = $this->measurement->medications->pluck('id')->toArray();
                break;

            case 'food':
                $this->foodTime = $time;
                $this->foodNotes = $this->measurement->notes ?? '';
                // Load food entries from food_measurements
                $this->foodEntries = [];
                foreach ($this->measurement->foodMeasurements as $foodMeasurement) {
                    $this->foodEntries[$foodMeasurement->food_id] = $foodMeasurement->grams_consumed;
                }
                break;
        }
    }

    public function save(MeasurementRepository $measurementRepository, MeasurementTypeRepository $measurementTypeRepository)
    {
        try {
            if ($this->mode === 'edit') {
                $this->updateMeasurement($measurementRepository);
            } else {
                $this->createMeasurement($measurementRepository, $measurementTypeRepository);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save measurement: ' . $e->getMessage());
        }
    }

    private function createMeasurement(MeasurementRepository $measurementRepository, MeasurementTypeRepository $measurementTypeRepository)
    {
        $measurementType = $measurementTypeRepository->findBySlug($this->selectedType);
        
        if (!$measurementType) {
            session()->flash('error', 'Invalid measurement type selected.');
            return;
        }

        $this->validateMeasurement();
        
        $data = [
            'user_id' => auth()->id(),
            'measurement_type_id' => $measurementType->id,
            'date' => $this->selectedDate,
        ];

        $data = array_merge($data, $this->getMeasurementData());

        // Check for duplicate timestamps
        $this->checkDuplicateTimestamp($data['created_at'], $measurementType->id);

        $measurement = $measurementRepository->create($data);
        
        // Handle medication relationships
        if ($this->selectedType === 'medication' && !empty($this->selectedMedications)) {
            $measurement->medications()->sync($this->selectedMedications);
        }
        
        // Handle food measurements
        if ($this->selectedType === 'food' && !empty($this->foodEntries)) {
            foreach ($this->foodEntries as $foodId => $grams) {
                $food = Food::find($foodId);
                if ($food) {
                    FoodMeasurement::create([
                        'measurement_id' => $measurement->id,
                        'food_id' => $foodId,
                        'grams_consumed' => $grams,
                        'calculated_calories' => $food->calculateCalories((float) $grams),
                        'calculated_carbs' => $food->calculateCarbs((float) $grams),
                    ]);
                }
            }
        }
        
        session()->flash('success', 'Measurement added successfully!');
        $this->cancel();
        $this->dispatch('measurement-saved');
    }

    private function updateMeasurement(MeasurementRepository $measurementRepository)
    {
        if (!$this->measurement) {
            session()->flash('error', 'Measurement not found.');
            return;
        }

        $this->validateMeasurement();
        $data = $this->getMeasurementData();

        // Check for duplicate timestamps (excluding current measurement)
        $this->checkDuplicateTimestampForUpdate($data['created_at'], $this->measurement->measurement_type_id);

        $measurement = $measurementRepository->update($this->measurementId, $data);
        
        // Handle medication relationships
        if ($this->selectedType === 'medication') {
            $this->measurement->medications()->sync($this->selectedMedications);
        }
        
        // Handle food measurements
        if ($this->selectedType === 'food') {
            // Delete existing food measurements
            $this->measurement->foodMeasurements()->delete();
            
            // Create new food measurements
            foreach ($this->foodEntries as $foodId => $grams) {
                $food = Food::find($foodId);
                if ($food) {
                    FoodMeasurement::create([
                        'measurement_id' => $this->measurement->id,
                        'food_id' => $foodId,
                        'grams_consumed' => $grams,
                        'calculated_calories' => $food->calculateCalories((float) $grams),
                        'calculated_carbs' => $food->calculateCarbs((float) $grams),
                    ]);
                }
            }
        }
        
        session()->flash('success', 'Measurement updated successfully!');
        $this->cancel();
        $this->dispatch('measurement-saved');
    }

    private function getMeasurementData(): array
    {
        $data = [];

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

            case 'medication':
                $data['notes'] = $this->medicationNotes;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->medicationTime);
                break;

            case 'food':
                $data['notes'] = $this->foodNotes;
                $data['created_at'] = Carbon::createFromFormat('Y-m-d H:i', $this->selectedDate . ' ' . $this->foodTime);
                break;
        }

        return $data;
    }

    private function validateMeasurement(): array
    {
        switch ($this->selectedType) {
            case 'glucose':
                return $this->validate([
                    'glucoseValue' => [
                        'required',
                        'numeric',
                        'min:0',
                        'max:12',
                        'regex:/^\d+(\.\d{1,2})?$/',
                    ],
                    'glucoseTime' => 'required|date_format:H:i',
                    'glucoseNotes' => 'nullable|string|max:1000',
                ], [
                    'glucoseValue.required' => 'Blood glucose level is required.',
                    'glucoseValue.numeric' => 'Blood glucose level must be a number.',
                    'glucoseValue.min' => 'Blood glucose level must be at least 0 mmol/L.',
                    'glucoseValue.max' => 'Blood glucose level seems too high (max 12 mmol/L). Please check your reading.',
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

            case 'medication':
                return $this->validate([
                    'selectedMedications' => [
                        'required',
                        'array',
                        'min:1',
                    ],
                    'selectedMedications.*' => 'exists:medications,id,user_id,' . auth()->id(),
                    'medicationTime' => 'required|date_format:H:i',
                    'medicationNotes' => 'nullable|string|max:1000',
                ], [
                    'selectedMedications.required' => 'Please select at least one medication.',
                    'selectedMedications.min' => 'Please select at least one medication.',
                    'selectedMedications.*.exists' => 'Selected medication is invalid.',
                    'medicationTime.required' => 'Time is required.',
                    'medicationTime.date_format' => 'Please enter a valid time in HH:MM format.',
                    'medicationNotes.max' => 'Notes cannot exceed 1000 characters.',
                ]);

            case 'food':
                return $this->validate([
                    'foodEntries' => [
                        'required',
                        'array',
                        'min:1',
                    ],
                    'foodEntries.*' => [
                        'required',
                        'integer',
                        'min:1',
                        'max:10000',
                    ],
                    'foodTime' => 'required|date_format:H:i',
                    'foodNotes' => 'nullable|string|max:1000',
                ], [
                    'foodEntries.required' => 'At least one food item is required.',
                    'foodEntries.array' => 'Food entries must be an array.',
                    'foodEntries.min' => 'At least one food item must be selected.',
                    'foodEntries.*.required' => 'Food amount is required.',
                    'foodEntries.*.integer' => 'Food amount must be a whole number.',
                    'foodEntries.*.min' => 'Food amount must be at least 1 gram.',
                    'foodEntries.*.max' => 'Food amount cannot exceed 10000 grams.',
                    'foodTime.required' => 'Time is required.',
                    'foodTime.date_format' => 'Please enter a valid time in HH:MM format.',
                    'foodNotes.max' => 'Notes cannot exceed 1000 characters.',
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

    private function checkDuplicateTimestampForUpdate($timestamp, $measurementTypeId)
    {
        $existingMeasurement = \App\Models\Measurement::where('user_id', auth()->id())
            ->where('measurement_type_id', $measurementTypeId)
            ->where('created_at', $timestamp)
            ->where('id', '!=', $this->measurementId)
            ->first();
            
        if ($existingMeasurement) {
            throw new \Exception('A measurement of this type already exists at this exact time. Please choose a different time.');
        }
    }

    public function confirmDelete()
    {
        if (!$this->measurement) {
            session()->flash('error', 'Measurement not found.');
            return;
        }

        $this->showDeleteConfirm = true;
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
            $this->dispatch('measurement-saved');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete measurement. Please try again.');
        }
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->showDeleteConfirm = false;
        $this->resetFields();
    }

    private function resetFields()
    {
        $this->reset(['measurementId', 'measurement', 'selectedType',
                     'glucoseValue', 'isFasting', 'glucoseNotes',
                     'weightValue', 'weightNotes', 
                     'exerciseDescription', 'exerciseDuration', 'exerciseNotes',
                     'notesContent', 'selectedMedications', 'medicationNotes']);
    }
    
    private function resetFormFields()
    {
        $this->reset(['measurementId', 'measurement',
                     'glucoseValue', 'isFasting', 'glucoseNotes',
                     'weightValue', 'weightNotes', 
                     'exerciseDescription', 'exerciseDuration', 'exerciseNotes',
                     'notesContent', 'selectedMedications', 'medicationNotes',
                     'foodEntries', 'foodNotes', 'foodSearch', 'searchResults']);
    }

    // Food-specific methods
    public function updatedFoodSearch()
    {
        $this->searchFoods();
    }

    public function searchFoods()
    {
        if (strlen($this->foodSearch) >= 2) {
            $this->searchResults = Food::search($this->foodSearch)->take(10)->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    public function addFood($foodId)
    {
        if (!array_key_exists($foodId, $this->foodEntries)) {
            $this->foodEntries[$foodId] = 100; // Default to 100g
        }
        $this->foodSearch = '';
        $this->searchResults = [];
    }

    public function removeFood($foodId)
    {
        unset($this->foodEntries[$foodId]);
    }

    public function updateFoodGrams($foodId, $grams)
    {
        if ($grams > 0) {
            $this->foodEntries[$foodId] = $grams;
        }
    }

    public function render(MeasurementTypeRepository $measurementTypeRepository)
    {
        $measurementTypes = $measurementTypeRepository->getAll();
        $medications = Medication::where('user_id', auth()->id())->get();
        $foods = collect();
        
        // Get foods for selected food entries
        if (!empty($this->foodEntries)) {
            $foods = Food::whereIn('id', array_keys($this->foodEntries))->get();
        }
        
        return view('livewire.measurement-modal', [
            'measurementTypes' => $measurementTypes,
            'medications' => $medications,
            'foods' => $foods,
        ]);
    }
}