<?php

namespace App\Livewire;

use App\Models\Medication;
use Livewire\Component;
use Livewire\WithPagination;

class MedicinesManagement extends Component
{
    use WithPagination;

    // Form fields
    public string $name = '';
    public string $description = '';
    
    // Modal state
    public bool $showModal = false;
    public string $mode = 'add'; // 'add' or 'edit'
    public ?int $medicationId = null;
    public $medication = null;
    
    // Search and filter
    public string $search = '';
    
    // Delete confirmation
    public bool $showDeleteConfirm = false;

    public function openAddMedicine()
    {
        $this->mode = 'add';
        $this->showModal = true;
        $this->resetFormFields();
    }

    public function openEditMedicine($medicationId)
    {
        $this->medication = Medication::find($medicationId);
        
        if (!$this->medication) {
            session()->flash('error', 'Medicine not found.');
            return;
        }

        $this->mode = 'edit';
        $this->medicationId = $medicationId;
        $this->name = $this->medication->name;
        $this->description = $this->medication->description ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Medicine name is required.',
            'name.max' => 'Medicine name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
        ];

        if ($this->mode === 'edit') {
            $this->medication->update($data);
            session()->flash('success', 'Medicine updated successfully!');
        } else {
            // Check for duplicate name
            $existing = Medication::where('name', $this->name)->first();
            if ($existing) {
                $this->addError('name', 'A medicine with this name already exists.');
                return;
            }
            
            Medication::create($data);
            session()->flash('success', 'Medicine added successfully!');
        }

        $this->cancel();
    }

    public function confirmDelete($medicationId)
    {
        $this->medication = Medication::find($medicationId);
        $this->medicationId = $medicationId;
        
        if (!$this->medication) {
            session()->flash('error', 'Medicine not found.');
            return;
        }

        // Check if medicine is used in any measurements
        $measurementCount = $this->medication->medicationMeasurements()->count();
        if ($measurementCount > 0) {
            session()->flash('error', "Cannot delete '{$this->medication->name}' because it is used in {$measurementCount} medication measurement(s).");
            return;
        }

        $this->showDeleteConfirm = true;
    }

    public function delete()
    {
        if (!$this->medication) {
            session()->flash('error', 'Medicine not found.');
            return;
        }

        $medicineName = $this->medication->name;
        $this->medication->delete();
        
        session()->flash('success', "Medicine '{$medicineName}' deleted successfully!");
        $this->showDeleteConfirm = false;
        $this->medication = null;
        $this->medicationId = null;
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->showDeleteConfirm = false;
        $this->resetFormFields();
    }

    private function resetFormFields()
    {
        $this->reset(['name', 'description', 'medicationId', 'medication']);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $medications = Medication::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.medicines-management', [
            'medications' => $medications,
        ]);
    }
}