<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'task_id' => 'required|exists:tasks,id',
            'body' => 'required|string|max:1000',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = [
                'body' => 'sometimes|string|max:1000',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'task_id.required' => 'задача обязательна',
            'body.required' => 'текст комментария обязателен',
        ];
    }
}
