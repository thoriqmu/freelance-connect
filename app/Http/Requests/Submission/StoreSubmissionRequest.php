<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'note' => 'required|string|min:10',
            'feedback' => 'nullable|string|min:10',
            'attachments' => 'sometimes|array',
            'attachments.*.file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,zip,doc,docx',
            'attachments.*.title' => 'nullable|string|max:255',
        ];
    }
}
