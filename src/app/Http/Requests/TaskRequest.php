<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Временно разрешаем всем, позже добавим проверку прав
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:new,in_progress,blocked,done,cancelled',
            'priority' => 'nullable|in:low,normal,high,critical',
            'due_date' => 'nullable|date|after_or_equal:today',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = [
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:new,in_progress,blocked,done,cancelled',
                'priority' => 'sometimes|in:low,normal,high,critical',
                'due_date' => 'nullable|date|after_or_equal:today',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название задачи обязательно',
            'project_id.required' => 'Проект обязателен',
            'project_id.exists' => 'Указанный проект не существует',
            'due_date.after_or_equal' => 'Дата выполнения не может быть раньше сегодня',
            'status.in' => 'Некорректный статус',
            'priority.in' => 'Некорректный приоритет',
        ];
    }
}