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
            <div class="flex items-center justify-center space-x-3">
                <button wire:click="previousDay" 
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors min-w-[40px] h-[40px] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button wire:click="goToToday" 
                        class="px-4 py-2 text-white rounded-lg transition-colors text-sm font-medium min-w-[80px] h-[40px] flex items-center justify-center {{ $isToday ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                    Today
                </button>

                <button wire:click="nextDay" 
                        @if($isToday) disabled @endif
                        class="p-2 rounded-lg transition-colors min-w-[40px] h-[40px] flex items-center justify-center {{ $isToday ? 'bg-gray-50 text-gray-300 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200' }}">
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

    {{-- Add Measurement Section --}}
    @livewire('add-measurement', ['date' => $selectedDate])


    {{-- Measurements --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Measurements</h2>
            
            {{-- Simple Measurement Type Filters --}}
            <div class="flex flex-wrap gap-4 items-center">
                @php
                    $types = [
                        ['slug' => 'glucose', 'name' => 'Glucose', 'icon' => '🩸'],
                        ['slug' => 'weight', 'name' => 'Weight', 'icon' => '⚖️'],
                        ['slug' => 'exercise', 'name' => 'Exercise', 'icon' => '🏸'],
                        ['slug' => 'notes', 'name' => 'Notes', 'icon' => '📝']
                    ];
                @endphp
                @foreach($types as $type)
                    <label class="flex items-center cursor-pointer px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <input type="checkbox" 
                               wire:model.live="filterTypes"
                               value="{{ $type['slug'] }}"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 mr-2">
                        <span class="flex items-center text-sm text-gray-700">
                            <span class="mr-2">{{ $type['icon'] }}</span>
                            {{ $type['name'] }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Measurements Display --}}
        @if($measurements->count() > 0)
            <div class="space-y-2">
                @foreach($measurements as $measurement)
                    <div class="border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition-colors"
                         wire:click="editMeasurement({{ $measurement->id }})">
                        <div class="flex items-center justify-between">
                            {{-- Icon and Time --}}
                            <div class="flex items-center space-x-3">
                                <div class="text-lg">
                                    @switch($measurement->measurementType->slug)
                                        @case('glucose')
                                            🩸
                                            @break
                                        @case('weight')
                                            ⚖️
                                            @break
                                        @case('exercise')
                                            🏸
                                            @break
                                        @case('notes')
                                            📝
                                            @break
                                        @default
                                            📊
                                    @endswitch
                                </div>
                                <span class="text-sm font-mono text-gray-700 min-w-[45px]">{{ $measurement->created_at->format('H:i') }}</span>
                                
                                {{-- Measurement Details --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 text-sm">
                                        @if($measurement->value)
                                            <span class="font-medium text-gray-900">{{ $measurement->value }}{{ $measurement->measurementType->unit ? ' ' . $measurement->measurementType->unit : '' }}</span>
                                            @if($measurement->is_fasting)
                                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs rounded-full">Fasting</span>
                                            @endif
                                        @endif
                                        
                                        @if($measurement->duration)
                                            <span class="text-gray-600">{{ $measurement->duration }}min</span>
                                        @endif
                                        
                                        @if($measurement->description)
                                            <span class="text-gray-900 truncate">{{ $measurement->description }}</span>
                                        @endif
                                        
                                        @if($measurement->notes)
                                            <span class="text-gray-600 truncate">{{ $measurement->notes }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Quick Delete Button --}}
                            <div class="ml-2">
                                <button 
                                    wire:click.stop="confirmDelete({{ $measurement->id }})"
                                    class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                                    title="Delete measurement"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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

    {{-- Edit Measurement Component --}}
    @livewire('edit-measurement')
</div>
