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
                                wire:model="glucoseValue"
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
                                wire:model="glucoseTime"
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
                            wire:model="isFasting"
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
                            wire:model="glucoseNotes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Any additional notes..."
                        ></textarea>
                    </div>

                @elseif ($selectedType === 'weight')
                    <!-- Weight Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="weightValue" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                id="weightValue"
                                wire:model="weightValue"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., 70.5"
                                required
                            >
                            @error('weightValue') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="weightTime" class="block text-sm font-medium text-gray-700">Time</label>
                            <input 
                                type="time" 
                                id="weightTime"
                                wire:model="weightTime"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                            @error('weightTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="weightNotes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea 
                            id="weightNotes"
                            wire:model="weightNotes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Any additional notes..."
                        ></textarea>
                    </div>

                @elseif ($selectedType === 'exercise')
                    <!-- Exercise Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="exerciseDescription" class="block text-sm font-medium text-gray-700">Exercise Description</label>
                            <input 
                                type="text" 
                                id="exerciseDescription"
                                wire:model="exerciseDescription"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Walking, Running, Gym workout"
                                required
                            >
                            @error('exerciseDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="exerciseTime" class="block text-sm font-medium text-gray-700">Time</label>
                            <input 
                                type="time" 
                                id="exerciseTime"
                                wire:model="exerciseTime"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                            @error('exerciseTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="exerciseDuration" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                        <input 
                            type="number" 
                            id="exerciseDuration"
                            wire:model="exerciseDuration"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="e.g., 30"
                            min="1"
                            required
                        >
                        @error('exerciseDuration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="exerciseNotes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea 
                            id="exerciseNotes"
                            wire:model="exerciseNotes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Any additional notes..."
                        ></textarea>
                    </div>

                @elseif ($selectedType === 'notes')
                    <!-- Notes Form -->
                    <div>
                        <label for="notesTime" class="block text-sm font-medium text-gray-700">Time</label>
                        <input 
                            type="time" 
                            id="notesTime"
                            wire:model="notesTime"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                        @error('notesTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="notesContent" class="block text-sm font-medium text-gray-700">Daily Notes</label>
                        <textarea 
                            id="notesContent"
                            wire:model="notesContent"
                            rows="5"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Write your daily notes here..."
                            required
                        ></textarea>
                        @error('notesContent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Save Measurement
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
