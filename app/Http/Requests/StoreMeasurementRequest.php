<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'measurement_type_id' => 'required|exists:measurement_types,id',
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->isFuture()) {
                        $fail('The date cannot be in the future.');
                    }
                },
            ],
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'date.before_or_equal' => 'You cannot add measurements for future dates.',
            'time.date_format' => 'Please enter a valid time in HH:MM format.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Combine date and time into a single datetime
        if ($this->has('date') && $this->has('time')) {
            $this->merge([
                'created_at' => Carbon::createFromFormat('Y-m-d H:i', $this->date . ' ' . $this->time),
            ]);
        }

        // Add user_id
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}
