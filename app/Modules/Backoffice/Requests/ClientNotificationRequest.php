<?php

namespace App\Modules\Backoffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'min:2'],
            'message' => ['required', 'string', 'min:3'],
            'data' => ['nullable', 'array'],
            'data.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre est obligatoire.',
            'titre.string' => 'Le titre doit être une chaîne de caractères.',
            'titre.min' => 'Le titre doit contenir au moins :min caractères.',

            'message.required' => 'Le message est obligatoire.',
            'message.string' => 'Le message doit être une chaîne de caractères.',
            'message.min' => 'Le message doit contenir au moins :min caractères.',

            'data.array' => 'Le champ data doit être un tableau.',
            'data.*.integer' => 'Chaque élément du champ data doit être un entier.',
        ];
    }
}
