<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index(): View
    {
        return view('settings.index', [
            'user' => auth()->user()
        ]);
    }

    /**
     * Update user preferences
     */
    public function update(Request $request)
    {
        $request->validate([
            'glucose_unit' => 'required|in:mmol/L,mg/dL',
            'weight_unit' => 'required|in:kg,lbs',
            'date_format' => 'required|in:Y-m-d,d/m/Y,m/d/Y',
            'time_format' => 'required|in:24,12',
            'timezone' => 'required|string|max:50',
            'chart_theme' => 'required|in:light,dark',
            'dashboard_layout' => 'required|in:grid,list',
        ]);

        $user = auth()->user();
        
        // Store preferences in user's profile or separate preferences table
        $preferences = [
            'glucose_unit' => $request->glucose_unit,
            'weight_unit' => $request->weight_unit,
            'date_format' => $request->date_format,
            'time_format' => $request->time_format,
            'timezone' => $request->timezone,
            'chart_theme' => $request->chart_theme,
            'dashboard_layout' => $request->dashboard_layout,
        ];

        // For simplicity, store as JSON in user table
        // In production, you might want a separate preferences table
        $user->update([
            'preferences' => json_encode($preferences)
        ]);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully!');
    }
}