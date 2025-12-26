<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreVilleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
           'libelle' => 'required|string|max:255|unique:stations,libelle',

            'id_pays' => 'required|exists:pays,id',
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé de la ville est obligatoire.',
            'libelle.string'   => 'Le libellé de la ville doit être une chaîne de caractères.',
            'id_pays.required' => 'Le pays est obligatoire.',
            'id_pays.exists'   => 'Le pays sélectionné est invalide.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
