<?php

namespace App\Http\Controllers;

use App\Repositories\MeasurementRepository;
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
        private MeasurementRepository $measurementRepository
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
