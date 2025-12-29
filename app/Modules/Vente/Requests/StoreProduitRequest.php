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
            // =========================
            // IDENTITÉ CUVE
            // =========================
            'libelle'     => 'required|string|max:255',
            'type_cuve'   => 'required|string|max:100',

            // =========================
            // STATION (OBLIGATOIRE)
            // =========================
            'id_station'  => 'required|exists:stations,id',

            // =========================
            // STOCK
            // =========================
            'qt_initial'  => 'nullable|numeric|min:0',
            'qt_actuelle' => 'nullable|numeric|min:0',

            // =========================
            // PRIX
            // =========================
            'pu_vente'    => 'nullable|numeric|min:0',
            'pu_unitaire' => 'nullable|numeric|min:0',
        ];
    }
}
