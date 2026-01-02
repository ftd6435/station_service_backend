<?php

namespace App\Modules\Caisse\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_station'    => ['required', 'exists:stations,id', 'unique:comptes,id_station'],
            'libelle'       => ['required', 'string', 'max:100'],
            'numero'       => ['required', 'string', 'max:100'],
            'commentaire'   => ['nullable', 'string', 'max:255'],
            'solde_initial' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation du compte.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
