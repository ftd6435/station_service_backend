<?php

namespace App\Modules\Vente\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LigneVenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_station'     => ['nullable', 'exists:stations,id'],
            'id_produit'     => ['nullable', 'exists:produits,id'],
            'id_affectation' => ['nullable', 'exists:affectations,id'],

            'index_debut'    => ['nullable', 'numeric'],
            'index_fin'      => ['nullable', 'numeric'],
            'qte_vendu'      => ['nullable', 'numeric'],
        ];
    }
}
