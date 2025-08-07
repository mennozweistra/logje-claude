<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Component;
use Illuminate\Validation\Rule;

class TodoForm extends Component
{
    public ?Todo $todo = null;
    
    public $title = '';
    public $description = '';
    public $priority = 'medium';
    public $status = 'todo';
    public $is_archived = false;
    
    public $isEditing = false;
    
    public function mount(?Todo $todo = null)
    {
        // Debug logging
        \Log::info('TodoForm mount called', [
            'todo_provided' => $todo !== null,
            'todo_id' => $todo?->id,
            'initial_isEditing' => $this->isEditing
        ]);
        
        if ($todo) {
            $this->todo = $todo;
            $this->isEditing = true;
            $this->title = $todo->title;
            $this->description = $todo->description ?? '';
            $this->priority = $todo->priority;
            $this->status = $todo->status;
            $this->is_archived = $todo->is_archived;
            
            \Log::info('TodoForm editing mode set', [
                'todo_id' => $todo->id,
                'isEditing' => $this->isEditing
            ]);
        } else {
            \Log::info('TodoForm create mode', [
                'isEditing' => $this->isEditing
            ]);
        }
    }
    
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['required', Rule::in(['high', 'medium', 'low'])],
            'status' => ['required', Rule::in(['todo', 'ongoing', 'paused', 'done'])],
            'is_archived' => ['boolean']
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
            if ($this->isEditing) {
                // Ensure user can only edit their own todos
                if ($this->todo->user_id !== auth()->id()) {
                    abort(403);
                }
                
                $this->todo->update([
                    'title' => trim($this->title),
                    'description' => trim($this->description) ?: null,
                    'priority' => $this->priority,
                    'status' => $this->status,
                    'is_archived' => $this->is_archived,
                ]);
                
                session()->flash('message', 'Todo updated successfully.');
            } else {
                Todo::create([
                    'user_id' => auth()->id(),
                    'title' => trim($this->title),
                    'description' => trim($this->description) ?: null,
                    'priority' => $this->priority,
                    'status' => $this->status,
                    'is_archived' => $this->is_archived,
                ]);
                
                session()->flash('message', 'Todo created successfully.');
            }
            
            return $this->redirect(route('todos.index'), navigate: true);
            
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while saving the todo.');
        }
    }
    
    public function delete()
    {
        if (!$this->isEditing) {
            return;
        }
        
        // Ensure user can only delete their own todos
        if ($this->todo->user_id !== auth()->id()) {
            abort(403);
        }
        
        try {
            $this->todo->delete();
            session()->flash('message', 'Todo deleted successfully.');
            return $this->redirect(route('todos.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the todo.');
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
        return view('livewire.todo-form');
    }
}