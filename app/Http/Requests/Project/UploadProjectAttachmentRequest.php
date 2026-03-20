<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UploadProjectAttachmentRequest extends FormRequest
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
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,zip,doc,docx',
            'title' => 'sometimes|string|max:255',
        ];
    }
}
