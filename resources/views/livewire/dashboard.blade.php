<div class="max-w-4xl mx-auto p-4 space-y-6">
    {{-- Date Navigation Header --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            {{-- Left: Date Display --}}
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ $selectedDateFormatted }}</h1>
            </div>

            {{-- Right: Navigation Buttons --}}
            <div class="flex items-center space-x-3">
                <button wire:click="previousDay" 
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors w-10 h-10 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button wire:click="goToToday" 
                        class="px-4 py-2 text-white rounded-lg transition-colors text-sm font-medium w-20 h-10 flex items-center justify-center {{ $isToday ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                    Today
                </button>

                <button wire:click="nextDay" 
                        @if($isToday) disabled @endif
                        class="p-2 rounded-lg transition-colors w-10 h-10 flex items-center justify-center {{ $isToday ? 'bg-gray-50 text-gray-300 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Add Measurement Section --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
            @php
                // Reorder measurement types: Weight, Glucose, Exercise, Notes, Medication
                $measurementTypes = [
                    ['slug' => 'weight', 'name' => 'Weight', 'icon' => '⚖️'],
                    ['slug' => 'glucose', 'name' => 'Glucose', 'icon' => '🩸'],
                    ['slug' => 'exercise', 'name' => 'Exercise', 'icon' => '🏸'],
                    ['slug' => 'notes', 'name' => 'Notes', 'icon' => '📝'],
                    ['slug' => 'medication', 'name' => 'Medication', 'icon' => '💊'],
                    ['slug' => 'food', 'name' => 'Food', 'icon' => '🍎']
                ];
            @endphp
            @foreach ($measurementTypes as $type)
                <button 
                    wire:click="openAddMeasurement('{{ $type['slug'] }}')"
                    class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors min-h-[100px]"
                >
                    <div class="text-lg mb-2">{{ $type['icon'] }}</div>
                    <span class="text-sm font-medium text-gray-900 text-center">{{ $type['name'] }}</span>
                </button>
            @endforeach
        </div>
    </div>


    {{-- Measurements --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 text-center">Measurements</h2>
            
            {{-- Simple Measurement Type Filters - Aligned with buttons --}}
            <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                @php
                    // Reorder filters to match buttons: Weight, Glucose, Exercise, Notes, Medication, Food
                    $types = [
                        ['slug' => 'weight', 'name' => 'Weight', 'icon' => '⚖️'],
                        ['slug' => 'glucose', 'name' => 'Glucose', 'icon' => '🩸'],
                        ['slug' => 'exercise', 'name' => 'Exercise', 'icon' => '🏸'],
                        ['slug' => 'notes', 'name' => 'Notes', 'icon' => '📝'],
                        ['slug' => 'medication', 'name' => 'Medication', 'icon' => '💊'],
                        ['slug' => 'food', 'name' => 'Food', 'icon' => '🍎']
                    ];
                @endphp
                @foreach($types as $type)
                    <label class="flex items-center justify-center cursor-pointer p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <input type="checkbox" 
                               wire:model.live="filterTypes"
                               value="{{ $type['slug'] }}"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 mr-2">
                        <span class="flex items-center text-sm text-gray-700">
                            <span class="mr-2 text-lg">{{ $type['icon'] }}</span>
                            {{ $type['name'] }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Measurements Display --}}
        @if($measurements->count() > 0)
            <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
                <tbody>
                    @foreach($measurements as $measurement)
                        <tr class="border-t border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                            wire:click="openEditMeasurement({{ $measurement->id }})">
                            {{-- Icon --}}
                            <td class="py-3 px-1 text-lg leading-none">
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
                                    @case('medication')
                                        💊
                                        @break
                                    @case('food')
                                        🍎
                                        @break
                                    @default
                                        📊
                                @endswitch
                            </td>
                            
                            {{-- Time --}}
                            <td class="py-3 px-1 text-sm font-mono text-black leading-none">
                                {{ $measurement->created_at->format('H:i') }}
                            </td>
                            
                            {{-- Type --}}
                            <td class="py-3 px-1 text-sm font-mono text-black leading-none">
                                @switch($measurement->measurementType->slug)
                                    @case('weight')
                                        Weight:
                                        @break
                                    @case('glucose')
                                        Glucose:
                                        @break
                                    @case('exercise')
                                        Exercise:
                                        @break
                                    @case('notes')
                                        Note:
                                        @break
                                    @case('medication')
                                        Medication:
                                        @break
                                    @case('food')
                                        Food:
                                        @break
                                @endswitch
                            </td>
                            
                            {{-- Value --}}
                            <td class="py-3 px-1 text-sm font-mono text-black leading-none @if($measurement->measurementType->slug === 'notes') hidden @endif">
                                @switch($measurement->measurementType->slug)
                                    @case('weight')
                                        <span>{{ $measurement->value }} kg</span>
                                        @break
                                    @case('glucose')
                                        <span>{{ $measurement->value }} mmol/L</span>
                                        @if($measurement->is_fasting)
                                            <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs rounded-full font-sans">Fasting</span>
                                        @endif
                                        @break
                                    @case('exercise')
                                        @if($measurement->duration)
                                            <span>{{ $measurement->duration }} min</span>
                                            <span class="text-gray-400 mx-2">•</span>
                                        @endif
                                        <span>{{ $measurement->description }}</span>
                                        @break
                                    @case('medication')
                                        <span>{{ $measurement->medications->sortBy('name')->pluck('name')->join(', ') }}</span>
                                        @break
                                    @case('food')
                                        @php
                                            $totalCalories = $measurement->foodMeasurements->sum('calculated_calories');
                                            $totalCarbs = $measurement->foodMeasurements->sum('calculated_carbs');
                                            $foodItems = $measurement->foodMeasurements->map(function($fm) {
                                                return $fm->food->name . ' (' . $fm->grams_consumed . 'g)';
                                            })->take(2);
                                        @endphp
                                        <div class="space-y-1">
                                            <div class="font-medium">{{ round($totalCalories) }} cal | {{ round($totalCarbs, 1) }}g carbs</div>
                                            <div class="text-xs text-gray-600">
                                                {{ $foodItems->join(', ') }}
                                                @if($measurement->foodMeasurements->count() > 2)
                                                    <span class="text-gray-500">+{{ $measurement->foodMeasurements->count() - 2 }} more</span>
                                                @endif
                                            </div>
                                        </div>
                                        @break
                                @endswitch
                            </td>
                            
                            {{-- Notes --}}
                            @if($measurement->measurementType->slug === 'notes')
                                <td class="py-3 px-3 text-sm font-mono text-black leading-none col-span-2" colspan="2">
                                    <span class="truncate">{{ $measurement->notes }}</span>
                                </td>
                            @else
                                <td class="py-3 px-3 text-sm font-mono text-black leading-none">
                                    @if($measurement->notes)
                                        <span class="truncate">{{ $measurement->notes }}</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
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

    {{-- Unified Measurement Modal --}}
    @livewire('measurement-modal')
</div>
