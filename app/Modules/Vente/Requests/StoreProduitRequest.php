<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'      => 'required|string|max:255',
            'type_cuve' => 'required|string|max:100',

            'qt_initial'  => 'nullable|numeric|min:0',
            'qt_actuelle' => 'nullable|numeric|min:0',

            'pu_vente'    => 'nullable|numeric|min:0',
            'pu_unitaire' => 'nullable|numeric|min:0',
        ];
    }
}
