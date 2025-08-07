<?php

namespace App\Services;

use App\Models\Measurement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthyDayService
{
    /**
     * The seven healthy day rules with their activation times
     */
    const RULES = [
        'rybelsus_morning' => [
            'time' => '09:00',
            'description' => 'Rybelsus medication taken',
            'medication' => 'Rybelsus'
        ],
        'glucose_fasting' => [
            'time' => '09:00', 
            'description' => 'Fasting glucose measurement recorded'
        ],
        'medications_morning' => [
            'time' => '11:00',
            'description' => 'Metformine, Amlodipine, and Kaliumlosartan taken',
            'medications' => ['Metformine', 'Amlodipine', 'Kaliumlosartan']
        ],
        'glucose_second' => [
            'time' => '13:00',
            'description' => 'Second glucose measurement recorded'
        ],
        'exercise' => [
            'time' => '14:00',
            'description' => 'Exercise activity logged'
        ],
        'glucose_third' => [
            'time' => '18:00', 
            'description' => 'Third glucose measurement recorded'
        ],
        'medications_evening' => [
            'time' => '20:00',
            'description' => 'Second Metformine and Atorvastatine taken',
            'medications' => ['Metformine', 'Atorvastatine'],
            'metformine_second' => true
        ]
    ];

    /**
     * Evaluate if a user is having a healthy day
     */
    public function isHealthyDay(User $user, Carbon $date): bool
    {
        $ruleStatuses = $this->getRuleStatuses($user, $date);
        
        foreach ($ruleStatuses as $rule) {
            if ($rule['active'] && !$rule['met']) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get detailed status of all rules for a specific date
     */
    public function getRuleStatuses(User $user, Carbon $date): array
    {
        $measurements = $this->getUserMeasurementsForDate($user, $date);
        $currentTime = $date->isToday() ? Carbon::now()->format('H:i') : '23:59';
        $isPastDate = $date->isPast() && !$date->isToday();
        
        $statuses = [];
        
        foreach (self::RULES as $ruleKey => $ruleConfig) {
            $ruleTime = $ruleConfig['time'];
            $isActive = $isPastDate || $currentTime >= $ruleTime;
            
            $statuses[$ruleKey] = [
                'time' => $ruleTime,
                'description' => $ruleConfig['description'],
                'active' => $isActive,
                'met' => $isActive ? $this->evaluateRule($ruleKey, $ruleConfig, $measurements) : false
            ];
        }
        
        return $statuses;
    }

    /**
     * Get user measurements for a specific date
     */
    private function getUserMeasurementsForDate(User $user, Carbon $date): Collection
    {
        return Measurement::where('user_id', $user->id)
            ->whereDate('date', $date->format('Y-m-d'))
            ->with(['measurementType', 'medications'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Evaluate a specific rule against measurements
     */
    private function evaluateRule(string $ruleKey, array $ruleConfig, Collection $measurements): bool
    {
        switch ($ruleKey) {
            case 'rybelsus_morning':
                return $this->hasMedication($measurements, 'Rybelsus');
                
            case 'glucose_fasting':
                return $this->hasFastingGlucose($measurements);
                
            case 'medications_morning':
                return $this->hasMedications($measurements, $ruleConfig['medications']);
                
            case 'glucose_second':
                return $this->hasMinimumGlucoseMeasurements($measurements, 2);
                
            case 'exercise':
                return $this->hasExercise($measurements);
                
            case 'glucose_third':
                return $this->hasMinimumGlucoseMeasurements($measurements, 3);
                
            case 'medications_evening':
                return $this->hasSecondMetformine($measurements) && 
                       $this->hasMedication($measurements, 'Atorvastatine');
                       
            default:
                return false;
        }
    }

    /**
     * Check if user has taken a specific medication
     */
    private function hasMedication(Collection $measurements, string $medicationName): bool
    {
        return $measurements
            ->where('measurementType.slug', 'medication')
            ->flatMap->medications
            ->contains('name', $medicationName);
    }

    /**
     * Check if user has taken multiple specific medications
     */
    private function hasMedications(Collection $measurements, array $medicationNames): bool
    {
        $takenMedications = $measurements
            ->where('measurementType.slug', 'medication')
            ->flatMap->medications
            ->pluck('name')
            ->unique();
            
        foreach ($medicationNames as $medication) {
            if (!$takenMedications->contains($medication)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if user has at least one fasting glucose measurement
     */
    private function hasFastingGlucose(Collection $measurements): bool
    {
        return $measurements
            ->where('measurementType.slug', 'glucose')
            ->where('is_fasting', true)
            ->isNotEmpty();
    }

    /**
     * Check if user has minimum number of glucose measurements
     */
    private function hasMinimumGlucoseMeasurements(Collection $measurements, int $minimum): bool
    {
        return $measurements
            ->where('measurementType.slug', 'glucose')
            ->count() >= $minimum;
    }

    /**
     * Check if user has logged exercise
     */
    private function hasExercise(Collection $measurements): bool
    {
        return $measurements
            ->where('measurementType.slug', 'exercise')
            ->isNotEmpty();
    }

    /**
     * Check if Metformine has been taken twice (second occurrence)
     */
    private function hasSecondMetformine(Collection $measurements): bool
    {
        $metformineTaken = $measurements
            ->where('measurementType.slug', 'medication')
            ->flatMap->medications
            ->where('name', 'Metformine')
            ->count();
            
        return $metformineTaken >= 2;
    }
}