<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Date Range Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Select Date Range</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" id="start-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="end-date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" id="end-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex flex-wrap gap-2">
                                <button onclick="setDateRange(7)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 sm:flex-none">7 days</button>
                                <button onclick="setDateRange(30)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 sm:flex-none">30 days</button>
                                <button onclick="setDateRange(90)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 sm:flex-none">90 days</button>
                            </div>
                            <button onclick="updateCharts()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 w-full sm:w-auto">
                                Update Charts
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Container -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6" id="charts-container">
                <!-- Loading Skeletons -->
                <div id="glucose-skeleton">
                    <x-chart-skeleton title="Blood Glucose Trends" />
                </div>
                <div id="weight-skeleton">
                    <x-chart-skeleton title="Weight Progress" />
                </div>
                <div class="xl:col-span-2" id="exercise-skeleton">
                    <x-chart-skeleton title="Exercise Activity" />
                </div>

                <!-- Glucose Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="glucose-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Blood Glucose Trends</h3>
                        <div class="relative h-64">
                            <canvas id="glucose-chart"></canvas>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                                    Daily Average
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                                    Fasting
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                                    Non-Fasting
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weight Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="weight-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Weight Progress</h3>
                        <div class="relative h-64">
                            <canvas id="weight-chart"></canvas>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-purple-500 rounded mr-2"></div>
                                    Weight
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-purple-300 rounded mr-2"></div>
                                    Trend Line
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exercise Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg xl:col-span-2" id="exercise-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Exercise Activity</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Daily Duration (minutes)</h4>
                                <div class="relative h-48">
                                    <canvas id="exercise-duration-chart"></canvas>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Exercise Types</h4>
                                <div class="relative h-48">
                                    <canvas id="exercise-types-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Export Data</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button onclick="exportData('csv')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 w-full">
                            📊 Export as CSV
                        </button>
                        <button onclick="exportData('pdf')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 w-full">
                            📄 Export as PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart variables
        let glucoseChart, weightChart, exerciseDurationChart, exerciseTypesChart;

        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeDateRange();
            initializeCharts();
            updateCharts();
        });
        
        // Also initialize on window load as fallback
        window.addEventListener('load', function() {
            if (!document.getElementById('start-date').value || !document.getElementById('end-date').value) {
                initializeDateRange();
                updateCharts();
            }
        });

        function initializeDateRange() {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - 30);

            document.getElementById('end-date').value = endDate.toISOString().split('T')[0];
            document.getElementById('start-date').value = startDate.toISOString().split('T')[0];
        }

        function setDateRange(days) {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);

            document.getElementById('end-date').value = endDate.toISOString().split('T')[0];
            document.getElementById('start-date').value = startDate.toISOString().split('T')[0];
        }

        function initializeCharts() {
            // Glucose Chart
            const glucoseCtx = document.getElementById('glucose-chart').getContext('2d');
            glucoseChart = new Chart(glucoseCtx, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Daily Average',
                            data: [],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: 'Fasting',
                            data: [],
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            showLine: false,
                            pointRadius: 4
                        },
                        {
                            label: 'Non-Fasting',
                            data: [],
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            showLine: false,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                parser: 'yyyy-MM-dd',
                                displayFormats: {
                                    day: 'MMM dd'
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'mmol/L'
                            }
                        }
                    }
                }
            });

            // Weight Chart
            const weightCtx = document.getElementById('weight-chart').getContext('2d');
            weightChart = new Chart(weightCtx, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Weight',
                            data: [],
                            borderColor: 'rgb(147, 51, 234)',
                            backgroundColor: 'rgba(147, 51, 234, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: 'Trend',
                            data: [],
                            borderColor: 'rgba(147, 51, 234, 0.5)',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                parser: 'yyyy-MM-dd',
                                displayFormats: {
                                    day: 'MMM dd'
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'kg'
                            }
                        }
                    }
                }
            });

            // Exercise Duration Chart
            const exerciseDurationCtx = document.getElementById('exercise-duration-chart').getContext('2d');
            exerciseDurationChart = new Chart(exerciseDurationCtx, {
                type: 'bar',
                data: {
                    datasets: [{
                        label: 'Minutes',
                        data: [],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                parser: 'yyyy-MM-dd',
                                displayFormats: {
                                    day: 'MMM dd'
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Minutes'
                            }
                        }
                    }
                }
            });

            // Exercise Types Chart
            const exerciseTypesCtx = document.getElementById('exercise-types-chart').getContext('2d');
            exerciseTypesChart = new Chart(exerciseTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(147, 51, 234, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function showLoadingStates() {
            // Show skeletons (check if they exist first)
            const glucoseSkeleton = document.getElementById('glucose-skeleton');
            const weightSkeleton = document.getElementById('weight-skeleton');
            const exerciseSkeleton = document.getElementById('exercise-skeleton');
            
            if (glucoseSkeleton) glucoseSkeleton.style.display = 'block';
            if (weightSkeleton) weightSkeleton.style.display = 'block';
            if (exerciseSkeleton) exerciseSkeleton.style.display = 'block';
            
            // Hide charts
            document.getElementById('glucose-chart-container').style.display = 'none';
            document.getElementById('weight-chart-container').style.display = 'none';
            document.getElementById('exercise-chart-container').style.display = 'none';
        }

        function hideLoadingStates() {
            // Hide skeletons (check if they exist first)
            const glucoseSkeleton = document.getElementById('glucose-skeleton');
            const weightSkeleton = document.getElementById('weight-skeleton');
            const exerciseSkeleton = document.getElementById('exercise-skeleton');
            
            if (glucoseSkeleton) glucoseSkeleton.style.display = 'none';
            if (weightSkeleton) weightSkeleton.style.display = 'none';
            if (exerciseSkeleton) exerciseSkeleton.style.display = 'none';
            
            // Show charts
            document.getElementById('glucose-chart-container').style.display = 'block';
            document.getElementById('weight-chart-container').style.display = 'block';
            document.getElementById('exercise-chart-container').style.display = 'block';
        }

        async function updateCharts() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            showLoadingStates();

            try {
                // Get CSRF token from meta tag or cookie
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                };

                // Update glucose chart
                const glucoseResponse = await fetch(`{{ route('reports.glucose-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                });
                
                if (!glucoseResponse.ok) {
                    throw new Error(`HTTP error! status: ${glucoseResponse.status}`);
                }
                
                const glucoseData = await glucoseResponse.json();
                
                glucoseChart.data.datasets[0].data = glucoseData.dailyAverages;
                glucoseChart.data.datasets[1].data = glucoseData.fastingReadings;
                glucoseChart.data.datasets[2].data = glucoseData.nonFastingReadings;
                glucoseChart.update();

                // Update weight chart
                const weightResponse = await fetch(`{{ route('reports.weight-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                });
                
                if (!weightResponse.ok) {
                    throw new Error(`HTTP error! status: ${weightResponse.status}`);
                }
                
                const weightData = await weightResponse.json();
                
                weightChart.data.datasets[0].data = weightData.weights;
                weightChart.data.datasets[1].data = weightData.trend;
                weightChart.update();

                // Update exercise charts
                const exerciseResponse = await fetch(`{{ route('reports.exercise-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                });
                
                if (!exerciseResponse.ok) {
                    throw new Error(`HTTP error! status: ${exerciseResponse.status}`);
                }
                
                const exerciseData = await exerciseResponse.json();
                
                exerciseDurationChart.data.datasets[0].data = exerciseData.dailyDuration;
                exerciseDurationChart.update();

                const types = Object.keys(exerciseData.exerciseTypes);
                const durations = Object.values(exerciseData.exerciseTypes);
                
                exerciseTypesChart.data.labels = types;
                exerciseTypesChart.data.datasets[0].data = durations;
                exerciseTypesChart.update();

                hideLoadingStates();

            } catch (error) {
                console.error('Error updating charts:', error);
                hideLoadingStates();
                
                // Show more detailed error information
                let errorMessage = 'Error loading chart data. ';
                if (error.message.includes('401') || error.message.includes('Unauthenticated')) {
                    errorMessage += 'Please refresh the page and try again (authentication issue).';
                } else if (error.message.includes('403')) {
                    errorMessage += 'You do not have permission to access this data.';
                } else if (error.message.includes('500')) {
                    errorMessage += 'Server error occurred. Please try again later.';
                } else {
                    errorMessage += 'Please check your internet connection and try again.';
                }
                
                alert(errorMessage);
                console.log('Full error details:', error);
            }
        }

        function exportData(format) {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
            
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = format === 'csv' ? '{{ route("reports.export.csv") }}' : '{{ route("reports.export.pdf") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add start date
            const startDateInput = document.createElement('input');
            startDateInput.type = 'hidden';
            startDateInput.name = 'start_date';
            startDateInput.value = startDate;
            form.appendChild(startDateInput);
            
            // Add end date
            const endDateInput = document.createElement('input');
            endDateInput.type = 'hidden';
            endDateInput.name = 'end_date';
            endDateInput.value = endDate;
            form.appendChild(endDateInput);
            
            // Add measurement types
            const types = ['glucose', 'weight', 'exercise', 'notes'];
            types.forEach(type => {
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'types[]';
                typeInput.value = type;
                form.appendChild(typeInput);
            });
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
</x-app-layout>