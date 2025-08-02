<?php

namespace App\Http\Requests;

class StoreWeightMeasurementRequest extends StoreMeasurementRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'value' => [
                'required',
                'numeric',
                'min:0.1',
                'max:500',
                'regex:/^\d+(\.\d{1,2})?$/', // Allow up to 2 decimal places
            ],
        ]);
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'value.required' => 'Weight is required.',
            'value.numeric' => 'Weight must be a number.',
            'value.min' => 'Weight must be at least 0.1 kg.',
            'value.max' => 'Weight seems too high (max 500 kg). Please check your reading.',
            'value.regex' => 'Weight can have at most 2 decimal places.',
        ]);
    }
}
