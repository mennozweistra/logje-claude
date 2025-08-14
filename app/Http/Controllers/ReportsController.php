<?php

namespace App\Http\Controllers;

use App\Models\LowCarbDietMeasurement;
use App\Models\Measurement;
use App\Repositories\MeasurementRepository;
use App\Services\HealthyDayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use League\Csv\Writer;

class ReportsController extends Controller
{
    public function __construct(
        private MeasurementRepository $measurementRepository,
        private HealthyDayService $healthyDayService
    ) {}

    /**
     * Display the reports dashboard
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Display the export form
     */
    public function export(): View
    {
        return view('reports.export');
    }

    /**
     * Get glucose trend data for charts
     */
    public function glucoseData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
            $userId, 
            'glucose', 
            $startDate, 
            $endDate
        );

        $chartData = $this->processGlucoseData($measurements);

        return response()->json($chartData);
    }

    /**
     * Get weight trend data for charts
     */
    public function weightData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
            $userId, 
            'weight', 
            $startDate, 
            $endDate
        );

        $chartData = $this->processWeightData($measurements);

        return response()->json($chartData);
    }

    /**
     * Get exercise activity data for charts
     */
    public function exerciseData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
            $userId, 
            'exercise', 
            $startDate, 
            $endDate
        );

        $chartData = $this->processExerciseData($measurements);

        return response()->json($chartData);
    }

    /**
     * Get nutrition data for charts (calories and carbs)
     */
    public function nutritionData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
            $userId, 
            'food', 
            $startDate, 
            $endDate
        );

        $chartData = $this->processNutritionData($measurements);

        return response()->json($chartData);
    }

    /**
     * Get fasting glucose trend data for charts
     */
    public function fastingGlucoseData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
            $userId, 
            'glucose', 
            $startDate, 
            $endDate
        );

        $chartData = $this->processFastingGlucoseData($measurements);

        return response()->json($chartData);
    }

    /**
     * Get daily maximum glucose data for charts
     */
    public function dailyMaxGlucoseData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
            $userId, 
            'glucose', 
            $startDate, 
            $endDate
        );

        $chartData = $this->processDailyMaxGlucoseData($measurements);

        return response()->json($chartData);
    }

    /**
     * Get healthy days data for charts
     */
    public function healthyDaysData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $chartData = $this->processHealthyDaysData($user, $startDate, $endDate);

        return response()->json($chartData);
    }

    /**
     * Get low carb diet data for charts
     */
    public function lowCarbDietData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $days = $request->input('days', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        } else {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days - 1);
        }

        $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
            $userId, 
            'low_carb_diet', 
            $startDate, 
            $endDate
        );

        $chartData = $this->processLowCarbDietData($measurements);

        return response()->json($chartData);
    }

    /**
     * Process glucose measurements for chart display
     */
    private function processGlucoseData($measurements): array
    {
        $dailyAverages = [];
        $fastingReadings = [];
        $nonFastingReadings = [];

        foreach ($measurements as $measurement) {
            $date = $measurement->date->format('Y-m-d');
            $value = (float) $measurement->value;

            if (!isset($dailyAverages[$date])) {
                $dailyAverages[$date] = ['total' => 0, 'count' => 0];
            }

            $dailyAverages[$date]['total'] += $value;
            $dailyAverages[$date]['count']++;

            if ($measurement->is_fasting) {
                $fastingReadings[] = [
                    'x' => $measurement->date->format('Y-m-d'),
                    'y' => $value
                ];
            } else {
                $nonFastingReadings[] = [
                    'x' => $measurement->date->format('Y-m-d'),
                    'y' => $value
                ];
            }
        }

        $averages = [];
        foreach ($dailyAverages as $date => $data) {
            $averages[] = [
                'x' => $date,
                'y' => round($data['total'] / $data['count'], 1)
            ];
        }

        return [
            'dailyAverages' => $averages,
            'fastingReadings' => $fastingReadings,
            'nonFastingReadings' => $nonFastingReadings,
        ];
    }

    /**
     * Process weight measurements for chart display
     */
    private function processWeightData($measurements): array
    {
        $weightData = [];
        $trendData = [];

        foreach ($measurements as $measurement) {
            $point = [
                'x' => $measurement->date->format('Y-m-d'),
                'y' => (float) $measurement->value
            ];
            $weightData[] = $point;
        }

        // Calculate simple trend line (linear regression)
        if (count($weightData) > 1) {
            $trendData = $this->calculateTrendLine($weightData);
        }

        return [
            'weights' => $weightData,
            'trend' => $trendData,
        ];
    }

    /**
     * Process exercise measurements for chart display
     */
    private function processExerciseData($measurements): array
    {
        $dailyDuration = [];
        $exerciseTypes = [];

        foreach ($measurements as $measurement) {
            $date = $measurement->date->format('Y-m-d');
            $duration = (int) $measurement->duration;
            $type = $measurement->description;

            if (!isset($dailyDuration[$date])) {
                $dailyDuration[$date] = 0;
            }
            $dailyDuration[$date] += $duration;

            if (!isset($exerciseTypes[$type])) {
                $exerciseTypes[$type] = 0;
            }
            $exerciseTypes[$type] += $duration;
        }

        $durationData = [];
        foreach ($dailyDuration as $date => $duration) {
            $durationData[] = [
                'x' => $date,
                'y' => $duration
            ];
        }

        return [
            'dailyDuration' => $durationData,
            'exerciseTypes' => $exerciseTypes,
        ];
    }

    /**
     * Process food measurements for nutrition chart display
     */
    private function processNutritionData($measurements): array
    {
        $dailyCalories = [];
        $dailyCarbs = [];

        foreach ($measurements as $measurement) {
            $date = $measurement->date->format('Y-m-d');
            
            if (!isset($dailyCalories[$date])) {
                $dailyCalories[$date] = 0;
                $dailyCarbs[$date] = 0;
            }

            // Sum up calories and carbs from all food measurements for this measurement
            foreach ($measurement->foodMeasurements as $foodMeasurement) {
                $dailyCalories[$date] += $foodMeasurement->calculated_calories;
                $dailyCarbs[$date] += $foodMeasurement->calculated_carbs;
            }
        }

        $calorieData = [];
        $carbData = [];

        foreach ($dailyCalories as $date => $calories) {
            $calorieData[] = [
                'x' => $date,
                'y' => round($calories, 0)
            ];
        }

        foreach ($dailyCarbs as $date => $carbs) {
            $carbData[] = [
                'x' => $date,
                'y' => round($carbs, 1)
            ];
        }

        return [
            'dailyCalories' => $calorieData,
            'dailyCarbs' => $carbData,
        ];
    }

    /**
     * Process fasting glucose measurements for chart display
     */
    private function processFastingGlucoseData($measurements): array
    {
        $fastingReadings = [];

        foreach ($measurements as $measurement) {
            if ($measurement->is_fasting) {
                $fastingReadings[] = [
                    'x' => $measurement->date->format('Y-m-d'),
                    'y' => (float) $measurement->value
                ];
            }
        }

        // Calculate trend line for fasting readings
        $trendData = [];
        if (count($fastingReadings) > 1) {
            $trendData = $this->calculateTrendLine($fastingReadings);
        }

        return [
            'fastingReadings' => $fastingReadings,
            'trendLine' => $trendData,
        ];
    }

    /**
     * Process daily maximum glucose measurements for chart display
     */
    private function processDailyMaxGlucoseData($measurements): array
    {
        $dailyMaximums = [];

        foreach ($measurements as $measurement) {
            $date = $measurement->date->format('Y-m-d');
            $value = (float) $measurement->value;

            if (!isset($dailyMaximums[$date]) || $value > $dailyMaximums[$date]['y']) {
                $dailyMaximums[$date] = [
                    'x' => $date,
                    'y' => $value
                ];
            }
        }

        $maximumData = array_values($dailyMaximums);

        // Calculate trend line for daily maximums
        $trendData = [];
        if (count($maximumData) > 1) {
            $trendData = $this->calculateTrendLine($maximumData);
        }

        return [
            'dailyMaximums' => $maximumData,
            'trendLine' => $trendData,
        ];
    }

    /**
     * Calculate simple linear trend line
     */
    private function calculateTrendLine(array $data): array
    {
        $n = count($data);
        if ($n < 2) return [];

        // Convert dates to numeric values for calculation
        $x = [];
        $y = [];
        foreach ($data as $i => $point) {
            $x[] = $i;
            $y[] = $point['y'];
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumXX = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumXX += $x[$i] * $x[$i];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $trendData = [];
        foreach ($data as $i => $point) {
            $trendData[] = [
                'x' => $point['x'],
                'y' => round($slope * $i + $intercept, 1)
            ];
        }

        return $trendData;
    }

    /**
     * Process healthy days data for chart display
     */
    private function processHealthyDaysData($user, Carbon $startDate, Carbon $endDate): array
    {
        $healthyDaysData = [];
        $complianceRates = [];
        
        // Generate daily compliance status for the date range
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $isHealthy = $this->healthyDayService->isHealthyDay($user, $current->copy());
            $healthyDaysData[] = [
                'x' => $current->format('Y-m-d'),
                'y' => $isHealthy ? 1 : 0  // 1 for healthy, 0 for not healthy
            ];
            $current->addDay();
        }
        
        // Calculate weekly compliance rates
        $weeklyData = [];
        $currentWeekStart = $startDate->copy()->startOfWeek();
        while ($currentWeekStart <= $endDate) {
            $weekEnd = $currentWeekStart->copy()->endOfWeek();
            if ($weekEnd > $endDate) {
                $weekEnd = $endDate->copy();
            }
            
            $weeklyHealthyDays = 0;
            $weeklyTotalDays = 0;
            $weekCurrent = $currentWeekStart->copy();
            
            while ($weekCurrent <= $weekEnd && $weekCurrent >= $startDate) {
                if ($weekCurrent <= $endDate) {
                    $weeklyTotalDays++;
                    if ($this->healthyDayService->isHealthyDay($user, $weekCurrent->copy())) {
                        $weeklyHealthyDays++;
                    }
                }
                $weekCurrent->addDay();
            }
            
            $complianceRate = $weeklyTotalDays > 0 ? round(($weeklyHealthyDays / $weeklyTotalDays) * 100, 1) : 0;
            $weeklyData[] = [
                'x' => $currentWeekStart->format('Y-m-d'),
                'y' => $complianceRate
            ];
            
            $currentWeekStart->addWeek();
        }

        return [
            'dailyCompliance' => $healthyDaysData,
            'weeklyCompliance' => $weeklyData,
            'summary' => [
                'totalDays' => count($healthyDaysData),
                'healthyDays' => count(array_filter($healthyDaysData, fn($day) => $day['y'] === 1)),
                'complianceRate' => count($healthyDaysData) > 0 ? 
                    round((count(array_filter($healthyDaysData, fn($day) => $day['y'] === 1)) / count($healthyDaysData)) * 100, 1) : 0
            ]
        ];
    }

    /**
     * Process low carb diet data for chart display
     */
    private function processLowCarbDietData($measurements): array
    {
        $carbLevelData = [];
        $carbLevelCounts = ['low' => 0, 'medium' => 0, 'high' => 0];
        
        foreach ($measurements as $measurement) {
            if ($measurement->lowCarbDietMeasurement) {
                $carbLevel = $measurement->lowCarbDietMeasurement->carb_level;
                $date = $measurement->date->format('Y-m-d');
                
                // Convert carb levels to numeric values for charting
                $numericValue = match($carbLevel) {
                    'low' => 1,
                    'medium' => 2,
                    'high' => 3,
                    default => 2
                };
                
                $carbLevelData[] = [
                    'x' => $date,
                    'y' => $numericValue,
                    'carbLevel' => $carbLevel,
                    'emoji' => $measurement->lowCarbDietMeasurement->carb_level_emoji
                ];
                
                $carbLevelCounts[$carbLevel]++;
            }
        }

        // Generate trend data (simplified moving average)
        $trendData = [];
        if (count($carbLevelData) > 2) {
            $windowSize = min(3, count($carbLevelData));
            for ($i = 0; $i < count($carbLevelData); $i++) {
                $start = max(0, $i - floor($windowSize / 2));
                $end = min(count($carbLevelData) - 1, $start + $windowSize - 1);
                
                $sum = 0;
                $count = 0;
                for ($j = $start; $j <= $end; $j++) {
                    $sum += $carbLevelData[$j]['y'];
                    $count++;
                }
                
                $trendData[] = [
                    'x' => $carbLevelData[$i]['x'],
                    'y' => round($sum / $count, 1)
                ];
            }
        }

        return [
            'carbLevelData' => $carbLevelData,
            'trendData' => $trendData,
            'carbLevelCounts' => $carbLevelCounts,
            'summary' => [
                'totalEntries' => count($carbLevelData),
                'averageCarbLevel' => count($carbLevelData) > 0 ? 
                    round(array_sum(array_column($carbLevelData, 'y')) / count($carbLevelData), 1) : 0
            ]
        ];
    }

    /**
     * Export measurement data as CSV
     */
    public function exportCsv(Request $request): Response
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'types' => 'array',
            'types.*' => 'in:glucose,weight,exercise,notes',
        ]);

        $userId = auth()->id();
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $types = $request->input('types', ['glucose', 'weight', 'exercise', 'notes']);

        $csv = Writer::createFromString();
        $csv->insertOne([
            'Date',
            'Time', 
            'Type',
            'Value',
            'Unit',
            'Duration',
            'Description',
            'Notes',
            'Is Fasting'
        ]);

        foreach ($types as $type) {
            $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
                $userId,
                $type,
                $startDate,
                $endDate
            );

            foreach ($measurements as $measurement) {
                $csv->insertOne([
                    $measurement->date->format('Y-m-d'),
                    $measurement->time ? $measurement->time->format('H:i') : '',
                    ucfirst($measurement->measurementType->name),
                    $measurement->value ?? '',
                    $measurement->measurementType->unit ?? '',
                    $measurement->duration ?? '',
                    $measurement->description ?? '',
                    $measurement->notes ?? '',
                    $measurement->is_fasting ? 'Yes' : 'No'
                ]);
            }
        }

        $filename = 'health-measurements-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';

        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export measurement data as PDF
     */
    public function exportPdf(Request $request): Response
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'types' => 'array',
            'types.*' => 'in:glucose,weight,exercise,notes',
        ]);

        $userId = auth()->id();
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $types = $request->input('types', ['glucose', 'weight', 'exercise', 'notes']);

        $data = [];
        $statistics = [];

        foreach ($types as $type) {
            $measurements = $this->measurementRepository->getUserMeasurementsByTypeAndDateRange(
                $userId,
                $type,
                $startDate,
                $endDate
            );

            $data[$type] = $measurements;
            $statistics[$type] = $this->calculateStatistics($measurements, $type);
        }

        $pdf = Pdf::loadView('reports.pdf-export', [
            'data' => $data,
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => auth()->user(),
        ]);

        $filename = 'health-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Calculate statistics for measurements
     */
    private function calculateStatistics($measurements, string $type): array
    {
        if ($measurements->isEmpty()) {
            return ['count' => 0];
        }

        $count = $measurements->count();
        $statistics = ['count' => $count];

        if (in_array($type, ['glucose', 'weight'])) {
            $values = $measurements->pluck('value')->map(fn($v) => (float) $v)->filter();
            
            if ($values->isNotEmpty()) {
                $statistics['average'] = round($values->avg(), 1);
                $statistics['min'] = $values->min();
                $statistics['max'] = $values->max();
                $statistics['latest'] = $values->last();
            }
        }

        if ($type === 'exercise') {
            $durations = $measurements->pluck('duration')->map(fn($d) => (int) $d)->filter();
            
            if ($durations->isNotEmpty()) {
                $statistics['totalMinutes'] = $durations->sum();
                $statistics['averageMinutes'] = round($durations->avg(), 0);
                $statistics['sessions'] = $durations->count();
            }
        }

        return $statistics;
    }
}
