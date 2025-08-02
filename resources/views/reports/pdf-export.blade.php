<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Health Report - {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header h1 {
            margin: 0;
            color: #1f2937;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #6b7280;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section h2 {
            color: #1f2937;
            font-size: 16px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .stat-box {
            background: #f9fafb;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            display: block;
        }
        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .measurements-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        .measurements-table th,
        .measurements-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }
        .measurements-table th {
            background: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        .measurements-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .no-data {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Health Measurements Report</h1>
        <p><strong>{{ $user->name }}</strong></p>
        <p>{{ $startDate->format('F j, Y') }} - {{ $endDate->format('F j, Y') }}</p>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    @foreach($data as $type => $measurements)
        <div class="section {{ $loop->iteration > 1 ? 'page-break' : '' }}">
            <h2>{{ ucfirst($type) }} Measurements</h2>
            
            @if($statistics[$type]['count'] > 0)
                <div class="stats-grid">
                    <div class="stat-box">
                        <span class="stat-value">{{ $statistics[$type]['count'] }}</span>
                        <span class="stat-label">Total Records</span>
                    </div>
                    
                    @if(isset($statistics[$type]['average']))
                        <div class="stat-box">
                            <span class="stat-value">{{ $statistics[$type]['average'] }}</span>
                            <span class="stat-label">Average</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value">{{ $statistics[$type]['min'] }}</span>
                            <span class="stat-label">Minimum</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value">{{ $statistics[$type]['max'] }}</span>
                            <span class="stat-label">Maximum</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value">{{ $statistics[$type]['latest'] }}</span>
                            <span class="stat-label">Latest</span>
                        </div>
                    @endif
                    
                    @if(isset($statistics[$type]['totalMinutes']))
                        <div class="stat-box">
                            <span class="stat-value">{{ $statistics[$type]['totalMinutes'] }}</span>
                            <span class="stat-label">Total Minutes</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value">{{ $statistics[$type]['averageMinutes'] }}</span>
                            <span class="stat-label">Avg. Minutes</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value">{{ $statistics[$type]['sessions'] }}</span>
                            <span class="stat-label">Sessions</span>
                        </div>
                    @endif
                </div>

                <table class="measurements-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            @if(in_array($type, ['glucose', 'weight']))
                                <th>Value</th>
                                <th>Unit</th>
                            @endif
                            @if($type === 'exercise')
                                <th>Duration (min)</th>
                                <th>Type</th>
                            @endif
                            @if($type === 'glucose')
                                <th>Fasting</th>
                            @endif
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($measurements as $measurement)
                            <tr>
                                <td>{{ $measurement->date->format('M j, Y') }}</td>
                                <td>{{ $measurement->time ? $measurement->time->format('g:i A') : '-' }}</td>
                                @if(in_array($type, ['glucose', 'weight']))
                                    <td>{{ $measurement->value }}</td>
                                    <td>{{ $measurement->measurementType->unit }}</td>
                                @endif
                                @if($type === 'exercise')
                                    <td>{{ $measurement->duration }}</td>
                                    <td>{{ $measurement->description }}</td>
                                @endif
                                @if($type === 'glucose')
                                    <td>{{ $measurement->is_fasting ? 'Yes' : 'No' }}</td>
                                @endif
                                <td>{{ Str::limit($measurement->notes, 50) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">
                    No {{ $type }} measurements found for this date range.
                </div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        Health Tracking Application - Generated {{ now()->format('F j, Y \a\t g:i A') }}
    </div>
</body>
</html>