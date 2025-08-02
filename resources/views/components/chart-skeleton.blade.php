@props(['title' => 'Loading Chart'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium">{{ $title }}</h3>
            <x-skeleton width="w-16" height="h-4" />
        </div>
        <div class="relative h-64 bg-gray-50 rounded flex items-center justify-center">
            <div class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-500">Loading chart data...</span>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-4">
            <div class="flex items-center">
                <x-skeleton width="w-3" height="h-3" class="rounded-full mr-2" />
                <x-skeleton width="w-16" height="h-3" />
            </div>
            <div class="flex items-center">
                <x-skeleton width="w-3" height="h-3" class="rounded-full mr-2" />
                <x-skeleton width="w-20" height="h-3" />
            </div>
        </div>
    </div>
</div>
