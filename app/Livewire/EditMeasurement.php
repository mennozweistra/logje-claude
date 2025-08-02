<?php

namespace App\Livewire;

use App\Repositories\MeasurementRepository;
use App\Repositories\MeasurementTypeRepository;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

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
        try {
            if (!$this->measurement) {
                session()->flash('error', 'Measurement not found.');
                return;
            }

            $type = $this->measurement->measurementType->slug;
            $data = [];

            // Validate using enhanced validation rules
            $this->validateMeasurementUpdate($type);

            switch ($type) {
                case 'glucose':
                    $data['value'] = $this->glucoseValue;
                    $data['is_fasting'] = $this->isFasting;
                    $data['notes'] = $this->glucoseNotes;
                    $newTimestamp = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->glucoseTime);
                    $data['created_at'] = $newTimestamp;
                    break;

                case 'weight':
                    $data['value'] = $this->weightValue;
                    $data['notes'] = $this->weightNotes;
                    $newTimestamp = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->weightTime);
                    $data['created_at'] = $newTimestamp;
                    break;

                case 'exercise':
                    $data['description'] = $this->exerciseDescription;
                    $data['duration'] = $this->exerciseDuration;
                    $data['notes'] = $this->exerciseNotes;
                    $newTimestamp = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->exerciseTime);
                    $data['created_at'] = $newTimestamp;
                    break;

                case 'notes':
                    $data['notes'] = $this->notesContent;
                    $newTimestamp = Carbon::createFromFormat('Y-m-d H:i', $this->measurement->date->format('Y-m-d') . ' ' . $this->notesTime);
                    $data['created_at'] = $newTimestamp;
                    break;
            }

            // Check for duplicate timestamps (excluding current measurement)
            $this->checkDuplicateTimestampForUpdate($newTimestamp, $this->measurement->measurement_type_id);

            $measurementRepository->update($this->measurementId, $data);
            session()->flash('success', 'Measurement updated successfully!');
            $this->cancel();
            $this->dispatch('measurement-updated');
            
        } catch (ValidationException $e) {
            // Re-throw validation exceptions to show field errors
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update measurement: ' . $e->getMessage());
        }
    }

    private function validateMeasurementUpdate(string $type): void
    {
        switch ($type) {
            case 'glucose':
                $this->validate([
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
                break;
                
            case 'weight':
                $this->validate([
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
                break;
                
            case 'exercise':
                $this->validate([
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
                break;
                
            case 'notes':
                $this->validate([
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
                break;
        }
    }

    private function checkDuplicateTimestampForUpdate($timestamp, $measurementTypeId)
    {
        $existingMeasurement = \App\Models\Measurement::where('user_id', auth()->id())
            ->where('measurement_type_id', $measurementTypeId)
            ->where('created_at', $timestamp)
            ->where('id', '!=', $this->measurementId) // Exclude current measurement
            ->first();
            
        if ($existingMeasurement) {
            throw new \Exception('A measurement of this type already exists at this exact time. Please choose a different time.');
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
