<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'      => 'sometimes|string|max:255',
            'type_produit' => 'sometimes|string|max:100',

            'qt_initial'  => 'sometimes|nullable|numeric|min:0',
            'qt_actuelle' => 'sometimes|nullable|numeric|min:0',

            'pu_vente'    => 'sometimes|nullable|numeric|min:0',
            'pu_unitaire' => 'sometimes|nullable|numeric|min:0',

            'status'      => 'sometimes|boolean',
        ];
    }
}
