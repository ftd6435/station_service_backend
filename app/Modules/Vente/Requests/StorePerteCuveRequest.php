<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StorePerteCuveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // =========================
            // 🔹 Données métier OBLIGATOIRES
            // =========================
            'id_cuve' => [
                'required',
                'integer',
                'exists:cuves,id',
            ],

            'quantite_perdue' => [
                'required',
                'numeric',
                'gt:0', // une perte doit être strictement positive
            ],

            // =========================
            // 🔹 Optionnel
            // =========================
            'commentaire' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'id_cuve.required' => 'La cuve est obligatoire.',
            'id_cuve.exists'   => 'La cuve sélectionnée est invalide.',

            'quantite_perdue.required' => 'La quantité perdue est obligatoire.',
            'quantite_perdue.numeric'  => 'La quantité perdue doit être numérique.',
            'quantite_perdue.gt'       => 'La quantité perdue doit être strictement supérieure à zéro.',

            'commentaire.string' => 'Le commentaire doit être un texte valide.',
            'commentaire.max'    => 'Le commentaire ne doit pas dépasser 255 caractères.',
        ];
    }

    /**
     * Réponse JSON normalisée en cas d’erreur de validation
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'status'  => 422,
                'message' => 'Erreur de validation des données envoyées.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
