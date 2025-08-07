<?php

use App\Models\Todo;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;

new class extends Component
{
    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';
    public string $sortBy = 'priority_created';
    public bool $showArchived = false;
    public bool $filtersVisible = false;
    
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
     * Navigate to create todo
     */
    public function createTodo(): void
    {
        $this->redirect(route('todos.create'), navigate: true);
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
    {{-- Add Todo Section --}}
    @if(!$showArchived)
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <div class="flex items-center justify-center">
            <button wire:click="createTodo" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Todo
            </button>
        </div>
    </div>
    @endif

    {{-- Todo List --}}
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <div class="mb-4 md:mb-6">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <h2 class="text-base md:text-lg font-semibold text-gray-900">
                    {{ $showArchived ? 'Archived Todos' : 'Your Todos' }}
                    <span class="text-sm font-normal text-gray-500 ml-2">
                        ({{ $this->todos->count() }} {{ $this->todos->count() === 1 ? 'item' : 'items' }})
                    </span>
                </h2>
                <div class="flex items-center space-x-2">
                    @if($showArchived)
                    <button wire:click="toggleArchived" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        Show Active
                    </button>
                    @endif
                    <button 
                        wire:click="toggleFilters"
                        class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                        </svg>
                        <span>{{ $filtersVisible ? 'Hide Filters' : 'Show Filters' }}</span>
                    </button>
                </div>
            </div>
            
            {{-- Collapsible Filters --}}
            @if($filtersVisible)
                <div class="transition-all duration-300 ease-in-out opacity-100 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                            @endif
                            
                            @if($statusFilter !== 'all' || $priorityFilter !== 'all' || $sortBy !== 'priority_created' || $showArchived)
                            <button wire:click="resetFilters" class="px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Clear All
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Todo Table --}}
        @if($this->todos->count() > 0)
            <table class="w-full border border-gray-200 rounded-lg overflow-hidden table-fixed">
                <tbody>
                    @foreach($this->todos as $todo)
                        <tr class="border-t border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                            wire:click="openTodo({{ $todo->id }})">
                            {{-- Status Symbol --}}
                            <td class="py-2 md:py-3 px-2 font-mono text-sm leading-none w-12 {{ $this->getPriorityColorClass($todo->priority) }}">
                                {{ $this->getStatusSymbol($todo->status) }}
                            </td>
                            
                            {{-- Title --}}
                            <td class="py-2 md:py-3 px-2 text-sm text-gray-900 leading-none">
                                <div class="truncate">{{ $todo->title }}</div>
                                @if($todo->notes->isNotEmpty())
                                <div class="text-xs text-gray-500 mt-1">{{ $todo->notes->count() }} {{ $todo->notes->count() === 1 ? 'note' : 'notes' }}</div>
                                @endif
                            </td>
                            
                            {{-- Priority (Mobile Only) --}}
                            <td class="py-2 md:py-3 px-2 text-xs font-medium leading-none w-16 md:hidden {{ $this->getPriorityColorClass($todo->priority) }}">
                                {{ strtoupper(substr($todo->priority, 0, 1)) }}
                            </td>
                            
                            {{-- Priority (Desktop Only) --}}
                            <td class="hidden md:table-cell py-2 md:py-3 px-2 text-xs font-medium leading-none w-20 {{ $this->getPriorityColorClass($todo->priority) }}">
                                {{ ucfirst($todo->priority) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
                    @elseif($statusFilter !== 'all' || $priorityFilter !== 'all')
                        No todos match your filters
                    @else
                        No todos yet
                    @endif
                </h3>
                
                <p class="text-sm text-gray-500 mb-4">
                    @if($showArchived)
                        You don't have any archived todos.
                    @elseif($statusFilter !== 'all' || $priorityFilter !== 'all')
                        Try adjusting your filters to see more todos.
                    @else
                        Get started by creating your first todo.
                    @endif
                </p>
                
                @if(!$showArchived && ($statusFilter === 'all' && $priorityFilter === 'all'))
                <button wire:click="createTodo" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Your First Todo
                </button>
                @elseif(($statusFilter !== 'all' || $priorityFilter !== 'all'))
                <button wire:click="resetFilters" class="px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Clear All Filters
                </button>
                @endif
            </div>
        @endif
    </div>
</div>