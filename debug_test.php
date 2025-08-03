<?php

// Debug test to check medications
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Medication;

$medications = Medication::all();
echo "Count: " . $medications->count() . "\n";
echo "First: " . ($medications->first() ? $medications->first()->name : 'null') . "\n";
foreach ($medications as $med) {
    echo "- " . $med->name . " (ID: " . $med->id . ")\n";
}