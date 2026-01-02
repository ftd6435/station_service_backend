<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreOperationCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_compte'         => ['required', 'exists:comptes,id'],
            'id_type_operation' => ['required', 'exists:type_operations,id'],
            'montant'           => ['required', 'numeric', 'gt:0'],
            'reference'         => ['required', 'string', 'max:100'],
            'commentaire'       => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation de l’opération.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
