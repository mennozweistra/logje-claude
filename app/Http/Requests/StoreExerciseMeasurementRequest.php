<?php

namespace App\Http\Requests;

class StoreExerciseMeasurementRequest extends StoreMeasurementRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'description' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\w\s\-\.,!]+$/', // Allow letters, numbers, spaces, basic punctuation
            ],
            'duration' => [
                'required',
                'integer',
                'min:1',
                'max:1440', // Max 24 hours
            ],
        ]);
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'description.required' => 'Exercise description is required.',
            'description.min' => 'Exercise description must be at least 2 characters.',
            'description.max' => 'Exercise description cannot exceed 255 characters.',
            'description.regex' => 'Exercise description contains invalid characters.',
            'duration.required' => 'Exercise duration is required.',
            'duration.integer' => 'Exercise duration must be a whole number of minutes.',
            'duration.min' => 'Exercise duration must be at least 1 minute.',
            'duration.max' => 'Exercise duration cannot exceed 24 hours (1440 minutes).',
        ]);
    }
}
