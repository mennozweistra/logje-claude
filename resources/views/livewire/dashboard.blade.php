<div class="max-w-4xl mx-auto p-4 space-y-6">
    {{-- Date Navigation Header --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            {{-- Date Display --}}
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-bold text-gray-900">{{ $selectedDateFormatted }}</h1>
                @if($isToday)
                    <span class="text-sm text-green-600 font-medium">Today</span>
                @endif
            </div>

            {{-- Navigation Buttons --}}
            <div class="flex items-center justify-center space-x-2">
                <button wire:click="previousDay" 
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                @if(!$isToday)
                    <button wire:click="goToToday" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                        Today
                    </button>
                @endif

                <button wire:click="nextDay" 
                        @if($isToday) disabled @endif
                        class="p-2 rounded-lg transition-colors {{ $isToday ? 'bg-gray-50 text-gray-300 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Date Picker --}}
            <div class="flex justify-center md:justify-end">
                <input type="date" 
                       wire:model.lazy="selectedDate"
                       max="{{ date('Y-m-d') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>

    {{-- Search and Filter --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="space-y-4">
            <!-- Search Input -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search measurements</label>
                    <input type="text" 
                           id="search"
                           wire:model.debounce.300ms="search"
                           placeholder="Search by notes, type, or value..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Date Range -->
                <div class="sm:w-48">
                    <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-1">Date range</label>
                    <select id="dateRange" 
                            wire:model="dateRange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="1">Today only</option>
                        <option value="7">Last 7 days</option>
                        <option value="14">Last 2 weeks</option>
                        <option value="30">Last month</option>
                        <option value="90">Last 3 months</option>
                    </select>
                </div>
            </div>

            <!-- Filter and Sort Row -->
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Type Filters -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Measurement types</label>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $types = [
                                ['slug' => 'glucose', 'name' => 'Blood Glucose', 'color' => 'bg-red-100 text-red-800'],
                                ['slug' => 'weight', 'name' => 'Weight', 'color' => 'bg-blue-100 text-blue-800'],
                                ['slug' => 'exercise', 'name' => 'Exercise', 'color' => 'bg-green-100 text-green-800'],
                                ['slug' => 'notes', 'name' => 'Notes', 'color' => 'bg-purple-100 text-purple-800']
                            ];
                        @endphp
                        @foreach($types as $type)
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model="filterTypes"
                                       value="{{ $type['slug'] }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full {{ $type['color'] }}">
                                    {{ $type['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Sort Options -->
                <div class="sm:w-48">
                    <label for="sortBy" class="block text-sm font-medium text-gray-700 mb-1">Sort by</label>
                    <select id="sortBy" 
                            wire:model="sortBy"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="newest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="value_high">Highest value</option>
                        <option value="value_low">Lowest value</option>
                        <option value="type">By type</option>
                    </select>
                </div>
            </div>

            <!-- Clear Filters Button -->
            @if($search || $filterTypes || $dateRange != 1 || $sortBy != 'newest')
                <div class="flex justify-end">
                    <button wire:click="clearFilters"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- View Toggle and Measurements --}}
    <div class="bg-white rounded-lg shadow p-6">
        {{-- View Toggle Switch --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Measurements</h2>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-600 {{ !$detailedView ? 'font-medium' : '' }}">Summary</span>
                <button wire:click="toggleView" 
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $detailedView ? 'bg-blue-600' : 'bg-gray-200' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $detailedView ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
                <span class="text-sm text-gray-600 {{ $detailedView ? 'font-medium' : '' }}">Detailed</span>
            </div>
        </div>

        {{-- Measurements Display --}}
        @if($measurements->count() > 0)
            @if($detailedView)
                {{-- Detailed View --}}
                <div class="space-y-4">
                    @foreach($measurements as $measurement)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="font-medium text-gray-900">{{ $measurement->measurementType->name }}</span>
                                        <span class="text-sm text-gray-500">{{ $measurement->created_at->format('H:i') }}</span>
                                    </div>
                                    
                                    @if($measurement->value)
                                        <div class="text-sm text-gray-600 mb-1">
                                            <span class="font-medium">Value:</span> {{ $measurement->value }}{{ $measurement->measurementType->unit ? ' ' . $measurement->measurementType->unit : '' }}
                                            @if($measurement->is_fasting)
                                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Fasting</span>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if($measurement->duration)
                                        <div class="text-sm text-gray-600 mb-1">
                                            <span class="font-medium">Duration:</span> {{ $measurement->duration }} minutes
                                        </div>
                                    @endif
                                    
                                    @if($measurement->description)
                                        <div class="text-sm text-gray-600 mb-1">
                                            <span class="font-medium">Description:</span> {{ $measurement->description }}
                                        </div>
                                    @endif
                                    
                                    @if($measurement->notes)
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">Notes:</span> {{ $measurement->notes }}
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Edit and Delete Buttons --}}
                                <div class="flex items-center space-x-2 ml-4">
                                    <button 
                                        wire:click="editMeasurement({{ $measurement->id }})"
                                        class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Edit measurement"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="confirmDelete({{ $measurement->id }})"
                                        class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Delete measurement"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Summary View --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        $groupedMeasurements = $measurements->groupBy('measurementType.name');
                    @endphp
                    
                    @foreach(['Glucose', 'Weight', 'Exercise', 'Notes'] as $typeName)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-2">{{ $typeName }}</h3>
                            @if(isset($groupedMeasurements[$typeName]) && $groupedMeasurements[$typeName]->count() > 0)
                                <div class="text-2xl font-bold text-blue-600 mb-1">
                                    {{ $groupedMeasurements[$typeName]->count() }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($typeName === 'Glucose' && $groupedMeasurements[$typeName]->first()->value)
                                        Latest: {{ $groupedMeasurements[$typeName]->first()->value }}{{ $groupedMeasurements[$typeName]->first()->measurementType->unit ? ' ' . $groupedMeasurements[$typeName]->first()->measurementType->unit : '' }}
                                    @elseif($typeName === 'Weight' && $groupedMeasurements[$typeName]->first()->value)
                                        Latest: {{ $groupedMeasurements[$typeName]->first()->value }}{{ $groupedMeasurements[$typeName]->first()->measurementType->unit ? ' ' . $groupedMeasurements[$typeName]->first()->measurementType->unit : '' }}
                                    @elseif($typeName === 'Exercise' && $groupedMeasurements[$typeName]->first()->duration)
                                        Total: {{ $groupedMeasurements[$typeName]->sum('duration') }} min
                                    @else
                                        entries recorded
                                    @endif
                                </div>
                            @else
                                <div class="text-2xl font-bold text-gray-300 mb-1">0</div>
                                <div class="text-xs text-gray-400">No entries</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No measurements recorded</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by adding your first measurement for this date.</p>
            </div>
        @endif
    </div>

    {{-- Add Measurement Section --}}
    @livewire('add-measurement', ['date' => $selectedDate])
    
    {{-- Edit Measurement Component --}}
    @livewire('edit-measurement')
</div>
