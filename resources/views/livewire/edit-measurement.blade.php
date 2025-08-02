<div>
    @if ($showEditForm && $measurement)
        <!-- Edit Form Modal -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="edit-modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Edit {{ ucfirst($measurement->measurementType->name) }} Measurement
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

                <form wire:submit="update" class="space-y-4">
                    @if ($measurement->measurementType->slug === 'glucose')
                        <!-- Glucose Edit Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="glucoseValue" class="block text-sm font-medium text-gray-700">Blood Glucose (mmol/L)</label>
                                <input 
                                    type="number" 
                                    step="0.1" 
                                    id="glucoseValue"
                                    wire:model="glucoseValue"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                            ></textarea>
                        </div>

                    @elseif ($measurement->measurementType->slug === 'weight')
                        <!-- Weight Edit Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="weightValue" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                <input 
                                    type="number" 
                                    step="0.1" 
                                    id="weightValue"
                                    wire:model="weightValue"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                            ></textarea>
                        </div>

                    @elseif ($measurement->measurementType->slug === 'exercise')
                        <!-- Exercise Edit Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="exerciseDescription" class="block text-sm font-medium text-gray-700">Exercise Description</label>
                                <input 
                                    type="text" 
                                    id="exerciseDescription"
                                    wire:model="exerciseDescription"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                            ></textarea>
                        </div>

                    @elseif ($measurement->measurementType->slug === 'notes')
                        <!-- Notes Edit Form -->
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
                                required
                            ></textarea>
                            @error('notesContent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Form Actions -->
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
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Update Measurement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDeleteConfirm && $measurement)
        <!-- Delete Confirmation Modal -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="delete-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Delete Measurement</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete this {{ $measurement->measurementType->name }} measurement? This action cannot be undone.
                        </p>
                    </div>
                    <div class="items-center px-4 py-3 space-x-3">
                        <button 
                            wire:click="cancel"
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
                        >
                            Cancel
                        </button>
                        <button 
                            wire:click="delete"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
