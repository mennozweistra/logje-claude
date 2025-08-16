<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6 md:px-8 space-y-6">
            <!-- Date Range Selector -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Select Date Range</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" id="start-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="updateCharts()">
                            </div>
                            <div>
                                <label for="end-date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" id="end-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="updateCharts()">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="setDateRange(7)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 md:flex-none">7 days</button>
                            <button onclick="setDateRange(14)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 md:flex-none">2 weeks</button>
                            <button onclick="setDateRange(21)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 md:flex-none">3 weeks</button>
                            <button onclick="setDateRange(30)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 md:flex-none">30 days</button>
                            <button onclick="setDateRange(90)" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm flex-1 md:flex-none">90 days</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Container -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="charts-container">
                <!-- Loading Skeletons -->
                <div id="glucose-skeleton">
                    <x-chart-skeleton title="Blood Glucose Trends" />
                </div>
                <div id="fasting-glucose-skeleton">
                    <x-chart-skeleton title="Fasting Glucose Trends" />
                </div>
                <div id="daily-max-glucose-skeleton">
                    <x-chart-skeleton title="Daily Maximum Glucose" />
                </div>
                <div id="weight-skeleton">
                    <x-chart-skeleton title="Weight Progress" />
                </div>
                <div class="md:col-span-2" id="exercise-skeleton">
                    <x-chart-skeleton title="Exercise Activity" />
                </div>
                <div class="md:col-span-2" id="nutrition-skeleton">
                    <x-chart-skeleton title="Daily Nutrition" />
                </div>
                <div id="healthy-days-skeleton">
                    <x-chart-skeleton title="Healthy Days Compliance" />
                </div>
                <div id="low-carb-diet-skeleton">
                    <x-chart-skeleton title="Low Carb Diet Trends" />
                </div>

                <!-- Glucose Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" id="glucose-chart-container" style="display: none;">
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

                <!-- Fasting Glucose Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" id="fasting-glucose-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Fasting Glucose Trends</h3>
                        <div class="relative h-64">
                            <canvas id="fasting-glucose-chart"></canvas>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                                    Fasting Readings
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-300 rounded mr-2"></div>
                                    Trend Line
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Maximum Glucose Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" id="daily-max-glucose-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Daily Maximum Glucose</h3>
                        <div class="relative h-64">
                            <canvas id="daily-max-glucose-chart"></canvas>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-orange-500 rounded mr-2"></div>
                                    Daily Maximum
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-orange-300 rounded mr-2"></div>
                                    Trend Line
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weight Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" id="weight-chart-container" style="display: none;">
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
                <div class="bg-white overflow-hidden shadow-sm rounded-lg md:col-span-2" id="exercise-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Exercise Activity</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                <!-- Nutrition Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg md:col-span-2" id="nutrition-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Daily Nutrition</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Daily Calories</h4>
                                <div class="relative h-48">
                                    <canvas id="calories-chart"></canvas>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Daily Carbohydrates (g)</h4>
                                <div class="relative h-48">
                                    <canvas id="carbs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-orange-500 rounded mr-2"></div>
                                    Calories
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded mr-2"></div>
                                    Carbohydrates
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Healthy Days Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" id="healthy-days-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Healthy Days Compliance</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Daily Compliance</h4>
                                <div class="relative h-48">
                                    <canvas id="healthy-days-chart"></canvas>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Weekly Compliance Rate</h4>
                                <div class="relative h-48">
                                    <canvas id="weekly-compliance-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                                    Healthy Day
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                                    Non-Healthy Day
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                                    Weekly Rate
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Carb Diet Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg" id="low-carb-diet-chart-container" style="display: none;">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Low Carb Diet Trends</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Daily Carb Levels</h4>
                                <div class="relative h-48">
                                    <canvas id="carb-levels-chart"></canvas>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Carb Level Distribution</h4>
                                <div class="relative h-48">
                                    <canvas id="carb-distribution-chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                                    Low Carb 🤗
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded mr-2"></div>
                                    Medium Carb 😟
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                                    High Carb 😞
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Export Data</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        // Chart Manager Singleton (Modern ES2022+ Pattern) - Define globally first
        class ChartManager {
            static #instance = null;
            
            constructor() {
                if (ChartManager.#instance) {
                    return ChartManager.#instance;
                }
                ChartManager.#instance = this;
                
                this.charts = {
                    glucose: null,
                    fastingGlucose: null,
                    dailyMaxGlucose: null,
                    weight: null,
                    exerciseDuration: null,
                    exerciseTypes: null,
                    calories: null,
                    carbs: null,
                    healthyDays: null,
                    weeklyCompliance: null,
                    carbLevels: null,
                    carbDistribution: null
                };
            }
            
            static getInstance() {
                if (!ChartManager.#instance) {
                    ChartManager.#instance = new ChartManager();
                }
                return ChartManager.#instance;
            }
            
            initializeCharts() {
                // Only initialize if charts don't exist
                if (!this.charts.glucose) {
                    this.createGlucoseChart();
                }
                if (!this.charts.fastingGlucose) {
                    this.createFastingGlucoseChart();
                }
                if (!this.charts.dailyMaxGlucose) {
                    this.createDailyMaxGlucoseChart();
                }
                if (!this.charts.weight) {
                    this.createWeightChart();
                }
                if (!this.charts.exerciseDuration) {
                    this.createExerciseDurationChart();
                }
                if (!this.charts.exerciseTypes) {
                    this.createExerciseTypesChart();
                }
                if (!this.charts.calories) {
                    this.createCaloriesChart();
                }
                if (!this.charts.carbs) {
                    this.createCarbsChart();
                }
                if (!this.charts.healthyDays) {
                    this.createHealthyDaysChart();
                }
                if (!this.charts.weeklyCompliance) {
                    this.createWeeklyComplianceChart();
                }
                if (!this.charts.carbLevels) {
                    this.createCarbLevelsChart();
                }
                if (!this.charts.carbDistribution) {
                    this.createCarbDistributionChart();
                }
            }
            
            createGlucoseChart() {
                const ctx = document.getElementById('glucose-chart').getContext('2d');
                this.charts.glucose = new Chart(ctx, {
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
                                        day: 'dd-MM'
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
            }

            createFastingGlucoseChart() {
                const ctx = document.getElementById('fasting-glucose-chart').getContext('2d');
                
                // Define custom plugin for gray area
                const grayAreaPlugin = {
                    id: 'grayArea',
                    beforeDatasetsDraw: (chart) => {
                        const ctx = chart.ctx;
                        const chartArea = chart.chartArea;
                        const yScale = chart.scales.y;
                        
                        if (chartArea && yScale) {
                            // Calculate pixel positions for 6.5 and 7 mmol/L
                            const y65 = yScale.getPixelForValue(6.5);
                            const y7 = yScale.getPixelForValue(7);
                            
                            // Draw gray background area between 6.5 and 7
                            ctx.save();
                            ctx.fillStyle = 'rgba(156, 163, 175, 0.2)'; // Gray with transparency
                            ctx.fillRect(
                                chartArea.left,
                                y7,
                                chartArea.right - chartArea.left,
                                y65 - y7
                            );
                            ctx.restore();
                        }
                    }
                };
                
                this.charts.fastingGlucose = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: 'Fasting Glucose',
                                data: [],
                                borderColor: (context) => {
                                    const value = context.parsed?.y;
                                    if (value < 6.5) return 'rgb(34, 197, 94)'; // Green
                                    if (value <= 7) return 'rgb(249, 115, 22)'; // Orange
                                    return 'rgb(239, 68, 68)'; // Red
                                },
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.1,
                                pointRadius: 4,
                                segment: {
                                    borderColor: (context) => {
                                        const value = context.p1?.parsed?.y;
                                        if (value < 6.5) return 'rgb(34, 197, 94)'; // Green for healthy levels
                                        if (value <= 7) return 'rgb(249, 115, 22)'; // Orange for borderline
                                        return 'rgb(239, 68, 68)'; // Red for elevated
                                    }
                                }
                            },
                            {
                                label: 'Trend Line',
                                data: [],
                                borderColor: 'rgba(239, 68, 68, 0.5)',
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
                                        day: 'dd-MM'
                                    }
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'mmol/L'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true
                            }
                        }
                    },
                    plugins: [grayAreaPlugin]
                });
            }

            createDailyMaxGlucoseChart() {
                const ctx = document.getElementById('daily-max-glucose-chart').getContext('2d');
                this.charts.dailyMaxGlucose = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: 'Daily Maximum',
                                data: [],
                                borderColor: 'rgb(249, 115, 22)',
                                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                tension: 0.1,
                                pointRadius: 4
                            },
                            {
                                label: 'Trend Line',
                                data: [],
                                borderColor: 'rgba(249, 115, 22, 0.5)',
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
                                        day: 'dd-MM'
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
            }

            createWeightChart() {
                const ctx = document.getElementById('weight-chart').getContext('2d');
                this.charts.weight = new Chart(ctx, {
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
                                        day: 'dd-MM'
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
            }

            createExerciseDurationChart() {
                const ctx = document.getElementById('exercise-duration-chart').getContext('2d');
                this.charts.exerciseDuration = new Chart(ctx, {
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
                                        day: 'dd-MM'
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
            }

            createExerciseTypesChart() {
                const ctx = document.getElementById('exercise-types-chart').getContext('2d');
                this.charts.exerciseTypes = new Chart(ctx, {
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

            createCaloriesChart() {
                const ctx = document.getElementById('calories-chart').getContext('2d');
                this.charts.calories = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        datasets: [{
                            label: 'Calories',
                            data: [],
                            backgroundColor: 'rgba(249, 115, 22, 0.8)',
                            borderColor: 'rgb(249, 115, 22)',
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
                                        day: 'dd-MM'
                                    }
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Calories'
                                }
                            }
                        }
                    }
                });
            }

            createCarbsChart() {
                const ctx = document.getElementById('carbs-chart').getContext('2d');
                this.charts.carbs = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        datasets: [{
                            label: 'Carbohydrates',
                            data: [],
                            backgroundColor: 'rgba(234, 179, 8, 0.8)',
                            borderColor: 'rgb(234, 179, 8)',
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
                                        day: 'dd-MM'
                                    }
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Grams'
                                }
                            }
                        }
                    }
                });
            }

            createHealthyDaysChart() {
                const ctx = document.getElementById('healthy-days-chart').getContext('2d');
                this.charts.healthyDays = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [{
                            label: 'Healthy Days',
                            data: [],
                            backgroundColor: function(context) {
                                if (!context.parsed || context.parsed.y === undefined) return 'rgba(34, 197, 94, 0.8)';
                                const value = context.parsed.y;
                                return value === 1 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)';
                            },
                            borderColor: function(context) {
                                if (!context.parsed || context.parsed.y === undefined) return 'rgb(34, 197, 94)';
                                const value = context.parsed.y;
                                return value === 1 ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)';
                            },
                            pointBackgroundColor: function(context) {
                                if (!context.parsed || context.parsed.y === undefined) return 'rgb(34, 197, 94)';
                                const value = context.parsed.y;
                                return value === 1 ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)';
                            },
                            pointRadius: 6,
                            showLine: false
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
                                        day: 'dd-MM'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                max: 1,
                                ticks: {
                                    callback: function(value) {
                                        return value === 1 ? 'Healthy' : 'Not Healthy';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y === 1 ? 'Healthy Day 😊' : 'Not Healthy Day 😔';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            createWeeklyComplianceChart() {
                const ctx = document.getElementById('weekly-compliance-chart').getContext('2d');
                this.charts.weeklyCompliance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        datasets: [{
                            label: 'Compliance Rate (%)',
                            data: [],
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgb(59, 130, 246)',
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
                                        week: 'dd-MM'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Compliance Rate (%)'
                                }
                            }
                        }
                    }
                });
            }

            createCarbLevelsChart() {
                const ctx = document.getElementById('carb-levels-chart').getContext('2d');
                this.charts.carbLevels = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: 'Carb Level',
                                data: [],
                                backgroundColor: function(context) {
                                    if (!context.parsed || context.parsed.y === undefined) return 'rgba(34, 197, 94, 0.8)';
                                    const value = context.parsed.y;
                                    if (value <= 1.5) return 'rgba(34, 197, 94, 0.8)'; // Low - green
                                    if (value <= 2.5) return 'rgba(234, 179, 8, 0.8)'; // Medium - yellow  
                                    return 'rgba(239, 68, 68, 0.8)'; // High - red
                                },
                                borderColor: function(context) {
                                    if (!context.parsed || context.parsed.y === undefined) return 'rgb(34, 197, 94)';
                                    const value = context.parsed.y;
                                    if (value <= 1.5) return 'rgb(34, 197, 94)'; // Low - green
                                    if (value <= 2.5) return 'rgb(234, 179, 8)'; // Medium - yellow
                                    return 'rgb(239, 68, 68)'; // High - red
                                },
                                pointBackgroundColor: function(context) {
                                    if (!context.parsed || context.parsed.y === undefined) return 'rgb(34, 197, 94)';
                                    const value = context.parsed.y;
                                    if (value <= 1.5) return 'rgb(34, 197, 94)'; // Low - green
                                    if (value <= 2.5) return 'rgb(234, 179, 8)'; // Medium - yellow
                                    return 'rgb(239, 68, 68)'; // High - red
                                },
                                pointRadius: 6,
                                showLine: true,
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
                                        day: 'dd-MM'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                max: 3,
                                ticks: {
                                    callback: function(value) {
                                        if (value === 1) return 'Low 🤗';
                                        if (value === 2) return 'Medium 😟';
                                        if (value === 3) return 'High 😞';
                                        return '';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        if (value === 1) return 'Low Carb 🤗';
                                        if (value === 2) return 'Medium Carb 😟';
                                        if (value === 3) return 'High Carb 😞';
                                        return '';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            createCarbDistributionChart() {
                const ctx = document.getElementById('carb-distribution-chart').getContext('2d');
                this.charts.carbDistribution = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Low Carb 🤗', 'Medium Carb 😟', 'High Carb 😞'],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)',   // Low - green
                                'rgba(234, 179, 8, 0.8)',   // Medium - yellow
                                'rgba(239, 68, 68, 0.8)'    // High - red
                            ],
                            borderColor: [
                                'rgb(34, 197, 94)',
                                'rgb(234, 179, 8)', 
                                'rgb(239, 68, 68)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            async updateCharts() {
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                
                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }

                this.showLoadingStates();

                try {
                    // Generate complete date sequence for consistent x-axis across all charts
                    const dateSequence = this.generateDateSequence(startDate, endDate);
                    
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    };

                    // Update glucose chart
                    if (this.charts.glucose) {
                        const glucoseResponse = await fetch(`{{ route('reports.glucose-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (glucoseResponse.ok) {
                            const glucoseData = await glucoseResponse.json();
                            // Pad data with missing dates for consistent x-axis
                            this.charts.glucose.data.datasets[0].data = this.padDataWithMissingDates(glucoseData.dailyAverages, dateSequence);
                            this.charts.glucose.data.datasets[1].data = this.padDataWithMissingDates(glucoseData.fastingReadings, dateSequence);
                            this.charts.glucose.data.datasets[2].data = this.padDataWithMissingDates(glucoseData.nonFastingReadings, dateSequence);
                            this.charts.glucose.update();
                        }
                    }

                    // Update fasting glucose chart
                    if (this.charts.fastingGlucose) {
                        const fastingGlucoseResponse = await fetch(`{{ route('reports.fasting-glucose-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (fastingGlucoseResponse.ok) {
                            const fastingGlucoseData = await fastingGlucoseResponse.json();
                            // Pad data with missing dates for consistent x-axis
                            this.charts.fastingGlucose.data.datasets[0].data = this.padDataWithMissingDates(fastingGlucoseData.fastingReadings, dateSequence);
                            this.charts.fastingGlucose.data.datasets[1].data = this.padDataWithMissingDates(fastingGlucoseData.trendLine, dateSequence);
                            this.charts.fastingGlucose.update();
                        }
                    }

                    // Update daily max glucose chart
                    if (this.charts.dailyMaxGlucose) {
                        const dailyMaxGlucoseResponse = await fetch(`{{ route('reports.daily-max-glucose-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (dailyMaxGlucoseResponse.ok) {
                            const dailyMaxGlucoseData = await dailyMaxGlucoseResponse.json();
                            // Pad data with missing dates for consistent x-axis
                            this.charts.dailyMaxGlucose.data.datasets[0].data = this.padDataWithMissingDates(dailyMaxGlucoseData.dailyMaximums, dateSequence);
                            this.charts.dailyMaxGlucose.data.datasets[1].data = this.padDataWithMissingDates(dailyMaxGlucoseData.trendLine, dateSequence);
                            this.charts.dailyMaxGlucose.update();
                        }
                    }

                    // Update weight chart
                    if (this.charts.weight) {
                        const weightResponse = await fetch(`{{ route('reports.weight-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (weightResponse.ok) {
                            const weightData = await weightResponse.json();
                            // Pad data with missing dates for consistent x-axis
                            this.charts.weight.data.datasets[0].data = this.padDataWithMissingDates(weightData.weights, dateSequence);
                            this.charts.weight.data.datasets[1].data = this.padDataWithMissingDates(weightData.trend, dateSequence);
                            this.charts.weight.update();
                        }
                    }

                    // Update exercise charts
                    if (this.charts.exerciseDuration && this.charts.exerciseTypes) {
                        const exerciseResponse = await fetch(`{{ route('reports.exercise-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (exerciseResponse.ok) {
                            const exerciseData = await exerciseResponse.json();
                            // Pad duration data with missing dates for consistent x-axis
                            this.charts.exerciseDuration.data.datasets[0].data = this.padDataWithMissingDates(exerciseData.dailyDuration, dateSequence);
                            this.charts.exerciseDuration.update();

                            // Exercise types chart (doughnut) doesn't need date padding
                            const types = Object.keys(exerciseData.exerciseTypes);
                            const durations = Object.values(exerciseData.exerciseTypes);
                            this.charts.exerciseTypes.data.labels = types;
                            this.charts.exerciseTypes.data.datasets[0].data = durations;
                            this.charts.exerciseTypes.update();
                        }
                    }

                    // Update nutrition charts
                    if (this.charts.calories && this.charts.carbs) {
                        const nutritionResponse = await fetch(`{{ route('reports.nutrition-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (nutritionResponse.ok) {
                            const nutritionData = await nutritionResponse.json();
                            // Pad data with missing dates for consistent x-axis
                            this.charts.calories.data.datasets[0].data = this.padDataWithMissingDates(nutritionData.dailyCalories, dateSequence);
                            this.charts.calories.update();
                            this.charts.carbs.data.datasets[0].data = this.padDataWithMissingDates(nutritionData.dailyCarbs, dateSequence);
                            this.charts.carbs.update();
                        }
                    }

                    // Update healthy days chart
                    if (this.charts.healthyDays && this.charts.weeklyCompliance) {
                        const healthyDaysResponse = await fetch(`{{ route('reports.healthy-days-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (healthyDaysResponse.ok) {
                            const healthyDaysData = await healthyDaysResponse.json();
                            // Pad data with missing dates for consistent x-axis
                            this.charts.healthyDays.data.datasets[0].data = this.padDataWithMissingDates(healthyDaysData.dailyCompliance, dateSequence);
                            this.charts.healthyDays.update();
                            
                            // Update weekly compliance chart (no need to pad weekly data)
                            this.charts.weeklyCompliance.data.datasets[0].data = healthyDaysData.weeklyCompliance;
                            this.charts.weeklyCompliance.update();
                        }
                    }

                    // Update low carb diet charts
                    if (this.charts.carbLevels && this.charts.carbDistribution) {
                        const lowCarbDietResponse = await fetch(`{{ route('reports.low-carb-diet-data') }}?start_date=${startDate}&end_date=${endDate}`, {
                            method: 'GET',
                            headers: headers,
                            credentials: 'same-origin'
                        });
                        
                        if (lowCarbDietResponse.ok) {
                            const lowCarbDietData = await lowCarbDietResponse.json();
                            // Pad data with missing dates for consistent x-axis
                            this.charts.carbLevels.data.datasets[0].data = this.padDataWithMissingDates(lowCarbDietData.carbLevelData, dateSequence);
                            this.charts.carbLevels.update();
                            
                            // Update carb distribution chart (pie chart doesn't need date padding)
                            const distributionData = [
                                lowCarbDietData.carbLevelCounts.low || 0,
                                lowCarbDietData.carbLevelCounts.medium || 0,
                                lowCarbDietData.carbLevelCounts.high || 0
                            ];
                            this.charts.carbDistribution.data.datasets[0].data = distributionData;
                            this.charts.carbDistribution.update();
                        }
                    }

                    this.hideLoadingStates();

                } catch (error) {
                    console.error('Error updating charts:', error);
                    this.hideLoadingStates();
                    alert('Error loading chart data. Please check your connection and try again.');
                }
            }

            showLoadingStates() {
                const glucoseSkeleton = document.getElementById('glucose-skeleton');
                const fastingGlucoseSkeleton = document.getElementById('fasting-glucose-skeleton');
                const dailyMaxGlucoseSkeleton = document.getElementById('daily-max-glucose-skeleton');
                const weightSkeleton = document.getElementById('weight-skeleton');
                const exerciseSkeleton = document.getElementById('exercise-skeleton');
                const nutritionSkeleton = document.getElementById('nutrition-skeleton');
                const healthyDaysSkeleton = document.getElementById('healthy-days-skeleton');
                const lowCarbDietSkeleton = document.getElementById('low-carb-diet-skeleton');
                
                if (glucoseSkeleton) glucoseSkeleton.style.display = 'block';
                if (fastingGlucoseSkeleton) fastingGlucoseSkeleton.style.display = 'block';
                if (dailyMaxGlucoseSkeleton) dailyMaxGlucoseSkeleton.style.display = 'block';
                if (weightSkeleton) weightSkeleton.style.display = 'block';
                if (exerciseSkeleton) exerciseSkeleton.style.display = 'block';
                if (nutritionSkeleton) nutritionSkeleton.style.display = 'block';
                if (healthyDaysSkeleton) healthyDaysSkeleton.style.display = 'block';
                if (lowCarbDietSkeleton) lowCarbDietSkeleton.style.display = 'block';
                
                document.getElementById('glucose-chart-container').style.display = 'none';
                document.getElementById('fasting-glucose-chart-container').style.display = 'none';
                document.getElementById('daily-max-glucose-chart-container').style.display = 'none';
                document.getElementById('weight-chart-container').style.display = 'none';
                document.getElementById('exercise-chart-container').style.display = 'none';
                document.getElementById('nutrition-chart-container').style.display = 'none';
                document.getElementById('healthy-days-chart-container').style.display = 'none';
                document.getElementById('low-carb-diet-chart-container').style.display = 'none';
            }

            hideLoadingStates() {
                const glucoseSkeleton = document.getElementById('glucose-skeleton');
                const fastingGlucoseSkeleton = document.getElementById('fasting-glucose-skeleton');
                const dailyMaxGlucoseSkeleton = document.getElementById('daily-max-glucose-skeleton');
                const weightSkeleton = document.getElementById('weight-skeleton');
                const exerciseSkeleton = document.getElementById('exercise-skeleton');
                const nutritionSkeleton = document.getElementById('nutrition-skeleton');
                const healthyDaysSkeleton = document.getElementById('healthy-days-skeleton');
                const lowCarbDietSkeleton = document.getElementById('low-carb-diet-skeleton');
                
                if (glucoseSkeleton) glucoseSkeleton.style.display = 'none';
                if (fastingGlucoseSkeleton) fastingGlucoseSkeleton.style.display = 'none';
                if (dailyMaxGlucoseSkeleton) dailyMaxGlucoseSkeleton.style.display = 'none';
                if (weightSkeleton) weightSkeleton.style.display = 'none';
                if (exerciseSkeleton) exerciseSkeleton.style.display = 'none';
                if (nutritionSkeleton) nutritionSkeleton.style.display = 'none';
                if (healthyDaysSkeleton) healthyDaysSkeleton.style.display = 'none';
                if (lowCarbDietSkeleton) lowCarbDietSkeleton.style.display = 'none';
                
                document.getElementById('glucose-chart-container').style.display = 'block';
                document.getElementById('fasting-glucose-chart-container').style.display = 'block';
                document.getElementById('daily-max-glucose-chart-container').style.display = 'block';
                document.getElementById('weight-chart-container').style.display = 'block';
                document.getElementById('exercise-chart-container').style.display = 'block';
                document.getElementById('nutrition-chart-container').style.display = 'block';
                document.getElementById('healthy-days-chart-container').style.display = 'block';
                document.getElementById('low-carb-diet-chart-container').style.display = 'block';
            }

            // Helper method to generate complete date sequence for consistent x-axis
            generateDateSequence(startDate, endDate) {
                const dates = [];
                const current = new Date(startDate);
                const end = new Date(endDate);
                
                while (current <= end) {
                    dates.push(current.toISOString().split('T')[0]);
                    current.setDate(current.getDate() + 1);
                }
                
                return dates;
            }

            // Helper method to pad data with missing dates as null values
            padDataWithMissingDates(data, dateSequence) {
                const dataMap = new Map();
                
                // Create map of existing data points
                data.forEach(point => {
                    dataMap.set(point.x, point.y);
                });
                
                // Generate complete sequence with null for missing dates
                return dateSequence.map(date => ({
                    x: date,
                    y: dataMap.has(date) ? dataMap.get(date) : null
                }));
            }
        }

        // Global function for date range buttons
        function setDateRange(days) {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);

            document.getElementById('end-date').value = endDate.toISOString().split('T')[0];
            document.getElementById('start-date').value = startDate.toISOString().split('T')[0];
            
            // Auto-refresh charts after setting date range
            ChartManager.getInstance().updateCharts();
        }

        // Global function for chart updates
        function updateCharts() {
            ChartManager.getInstance().updateCharts();
        }

        // Global initialization function
        function initializeDateRange() {
            console.log('Initializing date range');
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - 7);

            const startElement = document.getElementById('start-date');
            const endElement = document.getElementById('end-date');

            if (startElement && endElement) {
                // Only set if empty (preserve existing values during navigation)
                if (!startElement.value && !endElement.value) {
                    endElement.value = endDate.toISOString().split('T')[0];
                    startElement.value = startDate.toISOString().split('T')[0];
                    console.log('Date range set:', startElement.value, 'to', endElement.value);
                } else {
                    console.log('Date range already set, preserving values');
                }
            } else {
                console.error('Date input elements not found');
            }
        }
        
        // Listen for Livewire navigation events (only on reports page)
        document.addEventListener('livewire:navigated', function() {
            // Only reinitialize if we're on the reports page and have date inputs
            const startDateElement = document.getElementById('start-date');
            const endDateElement = document.getElementById('end-date');
            
            if (startDateElement && endDateElement) {
                console.log('Livewire navigation detected - reinitializing reports');
                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    initializeDateRange();
                    if (typeof Chart !== 'undefined') {
                        const manager = ChartManager.getInstance();
                        // Destroy existing charts before reinitializing to prevent conflicts
                        Object.keys(manager.charts).forEach(key => {
                            if (manager.charts[key]) {
                                manager.charts[key].destroy();
                                manager.charts[key] = null;
                            }
                        });
                        manager.initializeCharts();
                        const startDate = startDateElement.value;
                        const endDate = endDateElement.value;
                        if (startDate && endDate) {
                            setTimeout(() => manager.updateCharts(), 200);
                        }
                    }
                }, 100);
            }
        });

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

        // Script execution guard - prevent duplicate execution
        if (window.reportsScriptLoaded) {
            console.log('Reports script already loaded');
            // Still allow chart reinitialization if needed
            const startDateElement = document.getElementById('start-date');
            const endDateElement = document.getElementById('end-date');
            if (startDateElement && endDateElement && typeof Chart !== 'undefined') {
                const manager = ChartManager.getInstance();
                if (!manager.charts.glucose || !manager.charts.fastingGlucose || !manager.charts.dailyMaxGlucose || 
                    !manager.charts.healthyDays || !manager.charts.carbLevels) {
                    console.log('Charts not initialized, reinitializing...');
                    manager.initializeCharts();
                    setTimeout(() => manager.updateCharts(), 300);
                }
            }
        } else {
            window.reportsScriptLoaded = true;
            
            // Initialize charts on page load
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOMContentLoaded - initializing reports');
                initializeDateRange();
                if (typeof Chart !== 'undefined') {
                    const manager = ChartManager.getInstance();
                    manager.initializeCharts();
                    setTimeout(() => manager.updateCharts(), 300);
                }
            });
            
            // Also initialize on window load as fallback
            window.addEventListener('load', function() {
                console.log('Window load - checking reports initialization');
                if (!document.getElementById('start-date').value || !document.getElementById('end-date').value) {
                    console.log('Dates not set, initializing...');
                    initializeDateRange();
                }
                if (typeof Chart !== 'undefined') {
                    const manager = ChartManager.getInstance();
                    manager.initializeCharts();
                    setTimeout(() => manager.updateCharts(), 500);
                }
            });
        }
    </script>
</x-app-layout>