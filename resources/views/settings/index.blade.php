<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings & Preferences') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- User Preferences -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Measurement Preferences</h3>
                    
                    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Glucose Unit -->
                            <div>
                                <label for="glucose_unit" class="block text-sm font-medium text-gray-700">Glucose Unit</label>
                                <select id="glucose_unit" name="glucose_unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="mmol/L" {{ (json_decode($user->preferences ?? '{}')->glucose_unit ?? 'mmol/L') == 'mmol/L' ? 'selected' : '' }}>mmol/L (International)</option>
                                    <option value="mg/dL" {{ (json_decode($user->preferences ?? '{}')->glucose_unit ?? 'mmol/L') == 'mg/dL' ? 'selected' : '' }}>mg/dL (US)</option>
                                </select>
                                @error('glucose_unit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Weight Unit -->
                            <div>
                                <label for="weight_unit" class="block text-sm font-medium text-gray-700">Weight Unit</label>
                                <select id="weight_unit" name="weight_unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="kg" {{ (json_decode($user->preferences ?? '{}')->weight_unit ?? 'kg') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                    <option value="lbs" {{ (json_decode($user->preferences ?? '{}')->weight_unit ?? 'kg') == 'lbs' ? 'selected' : '' }}>Pounds (lbs)</option>
                                </select>
                                @error('weight_unit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date Format -->
                            <div>
                                <label for="date_format" class="block text-sm font-medium text-gray-700">Date Format</label>
                                <select id="date_format" name="date_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Y-m-d" {{ (json_decode($user->preferences ?? '{}')->date_format ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>2024-01-15 (ISO)</option>
                                    <option value="d/m/Y" {{ (json_decode($user->preferences ?? '{}')->date_format ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : '' }}>15/01/2024 (European)</option>
                                    <option value="m/d/Y" {{ (json_decode($user->preferences ?? '{}')->date_format ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : '' }}>01/15/2024 (US)</option>
                                </select>
                                @error('date_format')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Time Format -->
                            <div>
                                <label for="time_format" class="block text-sm font-medium text-gray-700">Time Format</label>
                                <select id="time_format" name="time_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="24" {{ (json_decode($user->preferences ?? '{}')->time_format ?? '24') == '24' ? 'selected' : '' }}>24-hour (14:30)</option>
                                    <option value="12" {{ (json_decode($user->preferences ?? '{}')->time_format ?? '24') == '12' ? 'selected' : '' }}>12-hour (2:30 PM)</option>
                                </select>
                                @error('time_format')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Timezone -->
                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                                <select id="timezone" name="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @php
                                        $currentTz = json_decode($user->preferences ?? '{}')->timezone ?? 'UTC';
                                        $timezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Australia/Sydney'];
                                    @endphp
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ $currentTz == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Chart Theme -->
                            <div>
                                <label for="chart_theme" class="block text-sm font-medium text-gray-700">Chart Theme</label>
                                <select id="chart_theme" name="chart_theme" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="light" {{ (json_decode($user->preferences ?? '{}')->chart_theme ?? 'light') == 'light' ? 'selected' : '' }}>Light</option>
                                    <option value="dark" {{ (json_decode($user->preferences ?? '{}')->chart_theme ?? 'light') == 'dark' ? 'selected' : '' }}>Dark</option>
                                </select>
                                @error('chart_theme')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Dashboard Layout -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Dashboard Layout</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <input type="radio" id="layout_grid" name="dashboard_layout" value="grid" 
                                           {{ (json_decode($user->preferences ?? '{}')->dashboard_layout ?? 'grid') == 'grid' ? 'checked' : '' }}
                                           class="peer sr-only">
                                    <label for="layout_grid" class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">Grid Layout</div>
                                            <div class="text-xs text-gray-500">Cards arranged in a responsive grid</div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-1 w-8 h-6">
                                            <div class="bg-gray-300 rounded-sm"></div>
                                            <div class="bg-gray-300 rounded-sm"></div>
                                            <div class="bg-gray-300 rounded-sm"></div>
                                            <div class="bg-gray-300 rounded-sm"></div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="relative">
                                    <input type="radio" id="layout_list" name="dashboard_layout" value="list" 
                                           {{ (json_decode($user->preferences ?? '{}')->dashboard_layout ?? 'grid') == 'list' ? 'checked' : '' }}
                                           class="peer sr-only">
                                    <label for="layout_list" class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">List Layout</div>
                                            <div class="text-xs text-gray-500">Items stacked vertically</div>
                                        </div>
                                        <div class="flex flex-col gap-1 w-8 h-6">
                                            <div class="bg-gray-300 rounded-sm h-1"></div>
                                            <div class="bg-gray-300 rounded-sm h-1"></div>
                                            <div class="bg-gray-300 rounded-sm h-1"></div>
                                            <div class="bg-gray-300 rounded-sm h-1"></div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('dashboard_layout')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Name:</span>
                            <span class="text-gray-900">{{ $user->name }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Email:</span>
                            <span class="text-gray-900">{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Member since:</span>
                            <span class="text-gray-900">{{ $user->created_at->format('F j, Y') }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Last updated:</span>
                            <span class="text-gray-900">{{ $user->updated_at->format('F j, Y g:i A') }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('profile') }}" class="text-blue-600 hover:text-blue-500 text-sm">
                            Edit Profile Information →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>