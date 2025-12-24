<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdatePompeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'        => 'sometimes|string|max:150',
            'reference'      => 'sometimes|nullable|string|max:50',
            'type_pompe'     => 'sometimes|in:essence,gasoil',
            'index_initial'  => 'sometimes|nullable|numeric|min:0',
            'status'         => 'sometimes|boolean',
        ];
    }

    protected function failedValidation(Validator $v)
    {
        throw new ValidationException($v, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation',
            'errors'  => $v->errors(),
        ], 422));
    }
}
