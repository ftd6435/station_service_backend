<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreTypeOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'     => ['required', 'string', 'max:100'],
            'commentaire' => ['required', 'string', 'max:255'],
            'nature'      => ['required', 'in:0,1,2'], // 0 = sortie | 1 = entrée | 2 = transfert
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
