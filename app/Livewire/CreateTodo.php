<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Component;
use Illuminate\Validation\Rule;

class CreateTodo extends Component
{
    public $title = '';
    public $description = '';
    public $priority = 'medium';
    public $status = 'todo';
    
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['required', Rule::in(['high', 'medium', 'low'])],
            'status' => ['required', Rule::in(['todo', 'ongoing', 'paused', 'done'])],
        ];
    }
    
    protected $validationAttributes = [
        'title' => 'title',
        'description' => 'description',
        'priority' => 'priority',
        'status' => 'status'
    ];
    
    public function save()
    {
        $this->validate();
        
        try {
            Todo::create([
                'user_id' => auth()->id(),
                'title' => trim($this->title),
                'description' => trim($this->description) ?: null,
                'priority' => $this->priority,
                'status' => $this->status,
                'is_archived' => false,
            ]);
            
            session()->flash('message', 'Todo created successfully.');
            return $this->redirect(route('todos.index'), navigate: true);
            
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while creating the todo.');
        }
    }
    
    public function cancel()
    {
        return $this->redirect(route('todos.index'), navigate: true);
    }
    
    public function getPriorityOptionsProperty()
    {
        return Todo::getPriorities();
    }
    
    public function getStatusOptionsProperty()
    {
        return Todo::getStatuses();
    }
    
    public function render()
    {
        return view('livewire.create-todo');
    }
}