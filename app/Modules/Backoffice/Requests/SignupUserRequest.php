<?php

namespace App\Modules\Backoffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignupUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'telephone' => ['required', 'min:9', 'max:14', 'regex:/^[0-9]+$/', 'unique:users,telephone'],
            'adresse' => ['nullable', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:6']
        ];
    }

    public function messages()
    {
        return [
            'name' => "Le nom doit composer au moins de trois caractères",
            'telephone.required' => "Le numéro doit avoir au minimum 9 digits ex: 600000000",
            'telephone.unique' => "Ce numéro de téléphone a été déjà utilisé",
            'adresse' => "L'adresse doit composer au moins de trois caractères",
            'email.required' => "L'adresse email est obligatoire ex: example@gmail.com",
            'email.unique' => "Cette adresse email a été déjà utilisé",
            'password' => "Le mot de passe est obligatoire",
            'password.confirmation' => "Veuillez confirmer le mot de passe"
        ];
    }
}
