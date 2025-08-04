<?php

namespace App\Livewire;

use App\Models\Food;
use Livewire\Component;
use Livewire\WithPagination;

class FoodManagement extends Component
{
    use WithPagination;

    // Form fields
    public string $name = '';
    public string $description = '';
    public string $caloriesPer100g = '';
    public string $carbsPer100g = '';
    
    // Modal state
    public bool $showModal = false;
    public string $mode = 'add'; // 'add' or 'edit'
    public ?int $foodId = null;
    public $food = null;
    
    // Search and filter
    public string $search = '';
    
    // Delete confirmation
    public bool $showDeleteConfirm = false;

    public function openAddFood()
    {
        $this->mode = 'add';
        $this->showModal = true;
        $this->resetFormFields();
    }

    public function openEditFood($foodId)
    {
        // Global scope will automatically filter by user, but we add explicit check for clarity
        $this->food = Food::find($foodId);
        
        if (!$this->food) {
            session()->flash('error', 'Food not found or you do not have permission to edit it.');
            return;
        }

        $this->mode = 'edit';
        $this->foodId = $foodId;
        $this->name = $this->food->name;
        $this->description = $this->food->description ?? '';
        $this->caloriesPer100g = (string) $this->food->calories_per_100g;
        $this->carbsPer100g = (string) $this->food->carbs_per_100g;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'caloriesPer100g' => 'required|numeric|min:0|max:9999.99',
            'carbsPer100g' => 'required|numeric|min:0|max:9999.99',
        ], [
            'name.required' => 'Food name is required.',
            'name.max' => 'Food name cannot exceed 255 characters.',
            'caloriesPer100g.required' => 'Calories per 100g is required.',
            'caloriesPer100g.numeric' => 'Calories must be a number.',
            'caloriesPer100g.min' => 'Calories cannot be negative.',
            'caloriesPer100g.max' => 'Calories cannot exceed 9999.99.',
            'carbsPer100g.required' => 'Carbohydrates per 100g is required.',
            'carbsPer100g.numeric' => 'Carbohydrates must be a number.',
            'carbsPer100g.min' => 'Carbohydrates cannot be negative.',
            'carbsPer100g.max' => 'Carbohydrates cannot exceed 9999.99.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'calories_per_100g' => $this->caloriesPer100g,
            'carbs_per_100g' => $this->carbsPer100g,
        ];

        if ($this->mode === 'edit') {
            $this->food->update($data);
            session()->flash('success', 'Food updated successfully!');
        } else {
            // Check for duplicate name within user's foods (global scope will apply)
            $existing = Food::where('name', $this->name)->first();
            if ($existing) {
                $this->addError('name', 'You already have a food with this name.');
                return;
            }
            
            // Food creation will automatically assign user_id via model global scope
            Food::create($data);
            session()->flash('success', 'Food added successfully!');
        }

        $this->cancel();
    }

    public function confirmDelete($foodId)
    {
        // Global scope will automatically filter by user
        $this->food = Food::find($foodId);
        $this->foodId = $foodId;
        
        if (!$this->food) {
            session()->flash('error', 'Food not found or you do not have permission to delete it.');
            return;
        }

        // Check if food is used in any measurements (with user-specific measurements)
        $measurementCount = $this->food->foodMeasurements()->count();
        if ($measurementCount > 0) {
            session()->flash('error', "Cannot delete '{$this->food->name}' because it is used in {$measurementCount} of your food measurement(s). Delete those measurements first.");
            return;
        }

        $this->showDeleteConfirm = true;
    }

    public function delete()
    {
        if (!$this->food) {
            session()->flash('error', 'Food not found.');
            return;
        }

        $foodName = $this->food->name;
        $this->food->delete();
        
        session()->flash('success', "Food '{$foodName}' deleted successfully!");
        $this->showDeleteConfirm = false;
        $this->food = null;
        $this->foodId = null;
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->showDeleteConfirm = false;
        $this->resetFormFields();
    }

    private function resetFormFields()
    {
        $this->reset(['name', 'description', 'caloriesPer100g', 'carbsPer100g', 'foodId', 'food']);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Global scope automatically filters foods by authenticated user
        $foods = Food::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.food-management', [
            'foods' => $foods,
        ]);
    }
}