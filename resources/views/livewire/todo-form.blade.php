<div class="space-y-6">
    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Main Form --}}
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <form wire:submit.prevent="save" class="space-y-6">
            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="title"
                    wire:model="title" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                    placeholder="Enter todo title..."
                    maxlength="255"
                >
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea 
                    id="description"
                    wire:model="description" 
                    rows="4"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                    placeholder="Enter todo description (optional)..."
                    maxlength="1000"
                ></textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Optional. Maximum 1,000 characters.</p>
            </div>

            {{-- Priority and Status Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Priority --}}
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="priority"
                        wire:model="priority" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('priority') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                    >
                        @foreach($this->priorityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status"
                        wire:model="status" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                    >
                        @foreach($this->statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Archive Checkbox (Only for editing) --}}
            @if($isEditing)
            <div>
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="is_archived"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0"
                    >
                    <span class="ml-2 text-sm text-gray-700">Archive this todo</span>
                </label>
                <p class="text-gray-500 text-xs mt-1">Archived todos are hidden from the main list by default.</p>
            </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex flex-col space-y-3 md:flex-row md:items-center md:justify-between md:space-y-0">
                {{-- Save/Cancel Buttons --}}
                <div class="flex items-center space-x-3">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 min-w-[100px]"
                        wire:loading.attr="disabled"
                        wire:target="save"
                    >
                        <span wire:loading.remove wire:target="save">
                            {{ $isEditing ? 'Update Todo' : 'Create Todo' }}
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>

                    <button 
                        type="button"
                        wire:click="cancel"
                        class="px-4 py-2 bg-gray-200 border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-300 focus:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 min-w-[80px]"
                    >
                        Cancel
                    </button>
                </div>

                {{-- Delete Button (Only for editing) --}}
                @if($isEditing)
                <div>
                    <button 
                        type="button"
                        wire:click="delete"
                        wire:confirm="Are you sure you want to delete this todo? This action cannot be undone."
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        wire:loading.attr="disabled"
                        wire:target="delete"
                    >
                        <span wire:loading.remove wire:target="delete">Delete Todo</span>
                        <span wire:loading wire:target="delete">Deleting...</span>
                    </button>
                </div>
                @endif
            </div>
        </form>
    </div>

    {{-- Notes Section (Only for editing) --}}
    @if($isEditing)
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <livewire:todo-notes :todo="$todo" :key="'notes-'.$todo->id" />
    </div>
    @endif
</div>