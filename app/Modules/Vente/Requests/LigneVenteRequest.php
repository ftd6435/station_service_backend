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
            // 🔹 Relations
            'id_station'     => ['nullable', 'exists:stations,id'],
            'id_cuve'        => ['nullable', 'exists:produits,id'],
            'id_affectation' => ['nullable', 'exists:affectations,id'],

            // 🔹 Données de vente
            'index_debut'    => ['nullable', 'numeric', 'min:0'],
            'index_fin'      => ['nullable', 'numeric', 'gte:index_debut'],
            'qte_vendu'      => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Messages personnalisés (optionnel mais pro)
     */
    public function messages(): array
    {
        return [
            'index_fin.gte' => 'L’index de fin doit être supérieur ou égal à l’index de début.',
        ];
    }
}
