<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateTypeOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'     => ['sometimes', 'string', 'max:100'],
            'commentaire' => ['nullable', 'string', 'max:255'],
            'nature'      => ['sometimes', 'in:0,1,2'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation du type d’opération.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
