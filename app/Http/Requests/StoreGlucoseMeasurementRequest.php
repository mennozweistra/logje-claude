<?php

namespace App\Http\Requests;

class StoreGlucoseMeasurementRequest extends StoreMeasurementRequest
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
                'min:0',
                'max:50',
                'regex:/^\d+(\.\d{1,2})?$/', // Allow up to 2 decimal places
            ],
            'is_fasting' => 'boolean',
        ]);
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'value.required' => 'Blood glucose level is required.',
            'value.numeric' => 'Blood glucose level must be a number.',
            'value.min' => 'Blood glucose level cannot be negative.',
            'value.max' => 'Blood glucose level seems too high (max 50 mmol/L). Please check your reading.',
            'value.regex' => 'Blood glucose level can have at most 2 decimal places.',
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Ensure is_fasting is a boolean
        $this->merge([
            'is_fasting' => $this->boolean('is_fasting'),
        ]);
    }
}
