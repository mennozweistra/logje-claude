<div class="inline-flex items-center">
    {{-- Health Status Indicator --}}
    <button 
        wire:click="toggleModal" 
        class="ml-3 text-2xl hover:scale-110 transition-transform duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full p-1"
        title="Click to view health rules status"
    >
        @if($isHealthy)
            😊
        @else
            😔
        @endif
    </button>

    {{-- Modal Overlay --}}
    @if($modalVisible)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
             wire:click="closeModal"
             x-data=""
             x-on:keydown.escape.window="$wire.closeModal()">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 max-w-4xl shadow-lg rounded-md bg-white" 
                 @click.stop>
                
                {{-- Modal Header --}}
                <div class="pb-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Daily Health Rules
                        </h3>
                        <button 
                            wire:click="closeModal" 
                            class="text-gray-400 hover:text-gray-600 focus:outline-none"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ Carbon\Carbon::parse($selectedDate)->format('l, d-m-Y') }}
                    </p>
                </div>

                {{-- Modal Body --}}
                <div class="py-4">
                    <div class="space-y-3">
                        @foreach($ruleStatuses as $ruleKey => $status)
                            <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $status['active'] ? ($status['met'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') : 'bg-gray-50 border-gray-200' }}">
                                {{-- Status Icon --}}
                                <div class="flex-shrink-0 mt-0.5">
                                    @if(!$status['active'])
                                        {{-- Inactive rule - grayed out clock --}}
                                        <div class="w-5 h-5 text-gray-400" title="Not yet active">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @elseif($status['met'])
                                        {{-- Rule met - green checkmark --}}
                                        <div class="w-5 h-5 text-green-600" title="Completed">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @else
                                        {{-- Rule not met - red cross --}}
                                        <div class="w-5 h-5 text-red-600" title="Not completed">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Rule Details --}}
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium {{ $status['active'] ? 'text-gray-900' : 'text-gray-500' }}">
                                            {{ $status['time'] }}
                                        </span>
                                        @if(!$status['active'])
                                            <span class="text-xs px-2 py-0.5 bg-gray-200 text-gray-600 rounded-full">
                                                Not Active Yet
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm {{ $status['active'] ? 'text-gray-700' : 'text-gray-500' }} mt-1">
                                        {{ $status['description'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Overall Status --}}
                    <div class="mt-6 p-4 rounded-lg {{ $isHealthy ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">
                                @if($isHealthy)
                                    😊
                                @else
                                    😔
                                @endif
                            </span>
                            <div>
                                <p class="text-sm font-medium {{ $isHealthy ? 'text-green-800' : 'text-red-800' }}">
                                    @if($isHealthy)
                                        You're having a healthy day!
                                    @else
                                        Some health goals need attention
                                    @endif
                                </p>
                                <p class="text-xs {{ $isHealthy ? 'text-green-600' : 'text-red-600' }}">
                                    @if(Carbon\Carbon::parse($selectedDate)->isToday())
                                        Based on active rules for current time
                                    @else
                                        Based on complete daily requirements
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <button 
                        wire:click="closeModal" 
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>