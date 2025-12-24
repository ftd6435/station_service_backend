<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Identité
            'name'       => 'sometimes|string|max:100',

            // Auth
            'email'      => 'sometimes|nullable|email|max:100',
            'password'   => 'sometimes|nullable|string|min:6',

            // Coordonnées
            'telephone'  => 'sometimes|nullable|string|max:30',
            'adresse'    => 'sometimes|nullable|string|max:150',

            // Image utilisateur
            'image'      => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Métier
            'role'       => 'sometimes|in:super_admin,admin,gerant,superviseur,pompiste',
            'id_station' => 'sometimes|nullable|exists:stations,id',

            'status'     => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'         => 'L’email est invalide.',
            'password.min'        => 'Le mot de passe doit contenir au moins 6 caractères.',

            'image.image'         => 'Le fichier doit être une image.',
            'image.mimes'         => 'L’image doit être au format jpg, jpeg, png ou webp.',
            'image.max'           => 'La taille maximale de l’image est de 2 Mo.',

            'role.in'             => 'Le rôle sélectionné est invalide.',
            'id_station.exists'   => 'La station sélectionnée est invalide.',
            'status.boolean'      => 'Le statut doit être vrai ou faux.',
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
