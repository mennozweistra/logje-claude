<?php

namespace App\Http\Requests;

class StoreNotesMeasurementRequest extends StoreMeasurementRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'notes' => [
                'required',
                'string',
                'min:1',
                'max:2000',
            ],
        ]);
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'notes.required' => 'Daily notes content is required.',
            'notes.min' => 'Daily notes must contain at least some content.',
            'notes.max' => 'Daily notes cannot exceed 2000 characters.',
        ]);
    }
}
