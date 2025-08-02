<div class="space-y-4">
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

    @if (!$showForm)
        <!-- Measurement Type Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Measurement</h3>
            <p class="text-sm text-gray-600 mb-4">Select the type of measurement you'd like to add for {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}:</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($measurementTypes as $type)
                    <button 
                        wire:click="selectType('{{ $type->slug }}')"
                        class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
                    >
                        <div class="text-2xl mb-2">
                            @switch($type->slug)
                                @case('glucose')
                                    🩸
                                    @break
                                @case('weight')
                                    ⚖️
                                    @break
                                @case('exercise')
                                    🏃‍♂️
                                    @break
                                @case('notes')
                                    📝
                                    @break
                                @default
                                    📊
                            @endswitch
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $type->name }}</span>
                        @if ($type->unit)
                            <span class="text-xs text-gray-500">({{ $type->unit }})</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @else
        <!-- Measurement Entry Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    Add {{ ucfirst($selectedType) }} Measurement
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
                @if ($selectedType === 'glucose')
                    <!-- Glucose Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="glucoseValue" class="block text-sm font-medium text-gray-700">Blood Glucose (mmol/L)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                id="glucoseValue"
                                wire:model.defer="glucoseValue"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., 5.5"
                                required
                            >
                            @error('glucoseValue') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="glucoseTime" class="block text-sm font-medium text-gray-700">Time</label>
                            <input 
                                type="time" 
                                id="glucoseTime"
                                wire:model.defer="glucoseTime"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                            @error('glucoseTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="isFasting"
                            wire:model.defer="isFasting"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="isFasting" class="ml-2 block text-sm text-gray-900">
                            Fasting measurement
                        </label>
                    </div>

                    <div>
                        <label for="glucoseNotes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea 
                            id="glucoseNotes"
                            wire:model.live.debounce.500ms="glucoseNotes"
                            rows="3"
                            maxlength="1000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('glucoseNotes') border-red-500 @enderror"
                            placeholder="Any additional notes... (max 1000 characters)"
                        ></textarea>
                        @error('glucoseNotes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        @if($glucoseNotes)
                            <span class="text-gray-500 text-xs mt-1 block">{{ strlen($glucoseNotes) }}/1000 characters</span>
                        @endif
                    </div>

                @elseif ($selectedType === 'weight')
                    <!-- Weight Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="weightValue" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0.1"
                                max="500"
                                id="weightValue"
                                wire:model.live.debounce.500ms="weightValue"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('weightValue') border-red-500 @enderror"
                                placeholder="0.1 - 500.0 kg"
                                required
                            >
                            @error('weightValue') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            @if($weightValue && is_numeric($weightValue))
                                @if($weightValue < 0.1)
                                    <span class="text-red-500 text-xs mt-1 block">Weight must be at least 0.1 kg</span>
                                @elseif($weightValue > 500)
                                    <span class="text-red-500 text-xs mt-1 block">Weight seems too high (max 500 kg)</span>
                                @elseif($weightValue >= 40 && $weightValue <= 120)
                                    <span class="text-green-600 text-xs mt-1 block">✓ Normal adult range</span>
                                @endif
                            @endif
                        </div>

                        <div>
                            <label for="weightTime" class="block text-sm font-medium text-gray-700">Time</label>
                            <input 
                                type="time" 
                                id="weightTime"
                                wire:model.live.debounce.500ms="weightTime"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('weightTime') border-red-500 @enderror"
                                required
                            >
                            @error('weightTime') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="weightNotes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea 
                            id="weightNotes"
                            wire:model.live.debounce.500ms="weightNotes"
                            rows="3"
                            maxlength="1000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('weightNotes') border-red-500 @enderror"
                            placeholder="Any additional notes... (max 1000 characters)"
                        ></textarea>
                        @error('weightNotes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        @if($weightNotes)
                            <span class="text-gray-500 text-xs mt-1 block">{{ strlen($weightNotes) }}/1000 characters</span>
                        @endif
                    </div>

                @elseif ($selectedType === 'exercise')
                    <!-- Exercise Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="exerciseDescription" class="block text-sm font-medium text-gray-700">Exercise Description</label>
                            <input 
                                type="text" 
                                id="exerciseDescription"
                                wire:model.live.debounce.500ms="exerciseDescription"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('exerciseDescription') border-red-500 @enderror"
                                placeholder="e.g., Walking, Running, Gym workout"
                                minlength="2"
                                maxlength="255"
                                required
                            >
                            @error('exerciseDescription') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            @if($exerciseDescription)
                                <span class="text-gray-500 text-xs mt-1 block">{{ strlen($exerciseDescription) }}/255 characters</span>
                            @endif
                        </div>

                        <div>
                            <label for="exerciseTime" class="block text-sm font-medium text-gray-700">Time</label>
                            <input 
                                type="time" 
                                id="exerciseTime"
                                wire:model.live.debounce.500ms="exerciseTime"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('exerciseTime') border-red-500 @enderror"
                                required
                            >
                            @error('exerciseTime') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="exerciseDuration" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                        <input 
                            type="number" 
                            id="exerciseDuration"
                            wire:model.live.debounce.500ms="exerciseDuration"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('exerciseDuration') border-red-500 @enderror"
                            placeholder="1 - 1440 minutes"
                            min="1"
                            max="1440"
                            required
                        >
                        @error('exerciseDuration') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        @if($exerciseDuration && is_numeric($exerciseDuration))
                            @if($exerciseDuration < 1)
                                <span class="text-red-500 text-xs mt-1 block">Duration must be at least 1 minute</span>
                            @elseif($exerciseDuration > 1440)
                                <span class="text-red-500 text-xs mt-1 block">Duration cannot exceed 24 hours (1440 minutes)</span>
                            @elseif($exerciseDuration >= 30 && $exerciseDuration <= 90)
                                <span class="text-green-600 text-xs mt-1 block">✓ Good workout duration</span>
                            @endif
                        @endif
                    </div>

                    <div>
                        <label for="exerciseNotes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea 
                            id="exerciseNotes"
                            wire:model.live.debounce.500ms="exerciseNotes"
                            rows="3"
                            maxlength="1000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('exerciseNotes') border-red-500 @enderror"
                            placeholder="Any additional notes... (max 1000 characters)"
                        ></textarea>
                        @error('exerciseNotes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        @if($exerciseNotes)
                            <span class="text-gray-500 text-xs mt-1 block">{{ strlen($exerciseNotes) }}/1000 characters</span>
                        @endif
                    </div>

                @elseif ($selectedType === 'notes')
                    <!-- Notes Form -->
                    <div>
                        <label for="notesTime" class="block text-sm font-medium text-gray-700">Time</label>
                        <input 
                            type="time" 
                            id="notesTime"
                            wire:model.live.debounce.500ms="notesTime"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notesTime') border-red-500 @enderror"
                            required
                        >
                        @error('notesTime') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="notesContent" class="block text-sm font-medium text-gray-700">Daily Notes</label>
                        <textarea 
                            id="notesContent"
                            wire:model.live.debounce.500ms="notesContent"
                            rows="5"
                            minlength="1"
                            maxlength="2000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notesContent') border-red-500 @enderror"
                            placeholder="Write your daily notes here... (max 2000 characters)"
                            required
                        ></textarea>
                        @error('notesContent') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        @if($notesContent)
                            <span class="text-gray-500 text-xs mt-1 block">{{ strlen($notesContent) }}/2000 characters</span>
                        @endif
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
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
                        <span wire:loading.remove>Save Measurement</span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
