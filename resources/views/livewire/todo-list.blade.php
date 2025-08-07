<?php

use App\Models\Todo;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;

new class extends Component
{
    public string $search = '';
    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';
    public string $sortBy = 'priority_created';
    public bool $showArchived = false;
    public bool $filtersVisible = false;
    
    // Modal properties
    public bool $showModal = false;
    public string $mode = 'create';
    public ?int $todoId = null;
    
    // Form properties
    public string $title = '';
    public string $description = '';
    public string $priority = 'medium';
    public string $status = 'todo';
    
    /**
     * Get filtered and sorted todos
     */
    public function getTodosProperty(): Collection
    {
        $query = Todo::with('notes')
            ->where('user_id', auth()->id());
        
        // Apply archived filter
        if ($this->showArchived) {
            $query->archived();
        } else {
            $query->active();
        }
        
        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->byStatus($this->statusFilter);
        }
        
        // Apply priority filter
        if ($this->priorityFilter !== 'all') {
            $query->byPriority($this->priorityFilter);
        }
        
        // Apply search filter
        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }
        
        // Apply sorting
        switch ($this->sortBy) {
            case 'priority_created':
                $query->orderByRaw("
                    CASE priority 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                    END, created_at DESC
                ");
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'status':
                $query->orderByRaw("
                    CASE status 
                    WHEN 'ongoing' THEN 1 
                    WHEN 'todo' THEN 2 
                    WHEN 'paused' THEN 3 
                    WHEN 'done' THEN 4 
                    END, created_at DESC
                ");
                break;
        }
        
        return collect($query->get());
    }
    
    /**
     * Toggle filter visibility
     */
    public function toggleFilters(): void
    {
        $this->filtersVisible = !$this->filtersVisible;
    }
    
    /**
     * Toggle archive filter
     */
    public function toggleArchived(): void
    {
        $this->showArchived = !$this->showArchived;
    }
    
    /**
     * Reset all filters
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->priorityFilter = 'all';
        $this->sortBy = 'priority_created';
        $this->showArchived = false;
    }
    
    /**
     * Get status symbol
     */
    public function getStatusSymbol(string $status): string
    {
        return match($status) {
            'todo' => '[ ]',
            'ongoing' => '[-]',
            'paused' => '[!]',
            'done' => '[x]',
            default => '[ ]'
        };
    }
    
    /**
     * Get priority color class
     */
    public function getPriorityColorClass(string $priority): string
    {
        return match($priority) {
            'high' => 'text-red-600',
            'medium' => 'text-yellow-600',
            'low' => 'text-green-600',
            default => 'text-gray-600'
        };
    }
    
    /**
     * Open create todo modal
     */
    public function createTodo(): void
    {
        $this->resetForm();
        $this->mode = 'create';
        $this->showModal = true;
    }
    
    /**
     * Reset form fields
     */
    public function resetForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->priority = 'medium';
        $this->status = 'todo';
        $this->todoId = null;
        $this->resetErrorBag();
    }
    
    /**
     * Cancel modal and close
     */
    public function cancel(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    /**
     * Save todo
     */
    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:todo,ongoing,paused,done',
        ]);

        if ($this->mode === 'create') {
            Todo::create([
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => $this->status,
                'user_id' => auth()->id(),
            ]);

            session()->flash('success', 'Todo created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }
    
    /**
     * Get priority options
     */
    public function getPriorityOptionsProperty(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ];
    }
    
    /**
     * Get status options
     */
    public function getStatusOptionsProperty(): array
    {
        return [
            'todo' => 'Todo',
            'ongoing' => 'Ongoing',
            'paused' => 'Paused',
            'done' => 'Done',
        ];
    }
    
    /**
     * Navigate to todo detail
     */
    public function openTodo(int $todoId): void
    {
        $this->redirect(route('todos.show', $todoId), navigate: true);
    }
}; ?>

<div class="space-y-6">
    {{-- Page Header --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $showArchived ? 'Archived Todos' : 'Todo Management' }}
                </h1>
                <p class="text-gray-600">
                    {{ $showArchived ? 'View your completed and archived todos' : 'Manage your personal todo list' }}
                </p>
            </div>
            @if(!$showArchived)
            <button 
                wire:click="createTodo"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Add New Todo
            </button>
            @endif
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search todos by title..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>
            <div class="text-sm text-gray-500">
                Total: {{ $this->todos->count() }} {{ $this->todos->count() === 1 ? 'todo' : 'todos' }}
            </div>
            <button 
                wire:click="toggleFilters"
                class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                </svg>
                <span>{{ $filtersVisible ? 'Hide Filters' : 'Filters' }}</span>
            </button>
        </div>
        
        {{-- Collapsible Filters --}}
        @if($filtersVisible)
            <div class="transition-all duration-300 ease-in-out opacity-100 border-t pt-4 mt-4">
                <div class="grid gap-4" style="grid-template-columns: repeat(4, 1fr);">
                    {{-- Status Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model.live="statusFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="all">All Status</option>
                            <option value="todo">Todo</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="paused">Paused</option>
                            <option value="done">Done</option>
                        </select>
                    </div>

                    {{-- Priority Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                        <select wire:model.live="priorityFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="all">All Priorities</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    {{-- Sort By --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sort By</label>
                        <select wire:model.live="sortBy" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="priority_created">Priority + Date</option>
                            <option value="created_desc">Newest First</option>
                            <option value="created_asc">Oldest First</option>
                            <option value="status">Status</option>
                        </select>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-end space-x-2">
                        @if(!$showArchived)
                        <button wire:click="toggleArchived" class="px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Show Archived
                        </button>
                        @else
                        <button wire:click="toggleArchived" class="px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Show Active
                        </button>
                        @endif
                        
                        @if(!empty($search) || $statusFilter !== 'all' || $priorityFilter !== 'all' || $sortBy !== 'priority_created' || $showArchived)
                        <button wire:click="resetFilters" class="px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Clear All
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Todos List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($this->todos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Todo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                Priority
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->todos as $todo)
                        <tr class="hover:bg-gray-50 cursor-pointer transition-colors"
                            wire:click="openTodo({{ $todo->id }})">
                            {{-- Status Symbol --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono text-sm {{ $this->getPriorityColorClass($todo->priority) }}">
                                    {{ $this->getStatusSymbol($todo->status) }}
                                </div>
                            </td>
                            
                            {{-- Title --}}
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $todo->title }}</div>
                                @if($todo->notes->isNotEmpty())
                                <div class="text-xs text-gray-500 mt-1">{{ $todo->notes->count() }} {{ $todo->notes->count() === 1 ? 'note' : 'notes' }}</div>
                                @endif
                            </td>
                            
                            {{-- Priority --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium {{ $this->getPriorityColorClass($todo->priority) }}">
                                    {{ ucfirst($todo->priority) }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                    @if($showArchived)
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                    @else
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    @endif
                </div>
                
                <h3 class="text-sm font-medium text-gray-900 mb-2">
                    @if($showArchived)
                        No archived todos
                    @elseif(!empty($search) || $statusFilter !== 'all' || $priorityFilter !== 'all')
                        No todos match your search or filters
                    @else
                        No todos yet
                    @endif
                </h3>
                
                <p class="text-sm text-gray-500 mb-4">
                    @if($showArchived)
                        You don't have any archived todos.
                    @elseif(!empty($search) || $statusFilter !== 'all' || $priorityFilter !== 'all')
                        Try adjusting your search or filters to see more todos.
                    @else
                        Get started by creating your first todo.
                    @endif
                </p>
                
                @if(!$showArchived && (empty($search) && $statusFilter === 'all' && $priorityFilter === 'all'))
                <button wire:click="createTodo" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Your First Todo
                </button>
                @elseif((!empty($search) || $statusFilter !== 'all' || $priorityFilter !== 'all'))
                <button wire:click="resetFilters" class="px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Clear All Filters
                </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    {{-- Add/Edit Todo Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $mode === 'edit' ? 'Edit Todo' : 'Add New Todo' }}
                    </h3>
                    <button 
                        wire:click="cancel"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Todo Title</label>
                        <input 
                            type="text" 
                            id="title"
                            wire:model.live.debounce.500ms="title"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                            placeholder="Enter todo title..."
                            required
                        >
                        @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                        <textarea 
                            id="description"
                            wire:model.live.debounce.500ms="description"
                            rows="3"
                            maxlength="1000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                            placeholder="Enter todo description (optional)..."
                        ></textarea>
                        @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        @if($description)
                            <span class="text-gray-500 text-xs mt-1 block">{{ strlen($description) }}/1000 characters</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select 
                                id="priority"
                                wire:model.live="priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('priority') border-red-500 @enderror"
                                required
                            >
                                @foreach($this->priorityOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('priority') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select 
                                id="status"
                                wire:model.live="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                                required
                            >
                                @foreach($this->statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button 
                            type="button"
                            wire:click="cancel"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>
                                {{ $mode === 'edit' ? 'Update Todo' : 'Add Todo' }}
                            </span>
                            <span wire:loading>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>