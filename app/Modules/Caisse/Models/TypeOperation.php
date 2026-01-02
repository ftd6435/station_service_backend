<?php

namespace App\Modules\Caisse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeOperation extends Model
{
    protected $table = 'type_operations';

    protected $fillable = [
        'libelle',
        'commentaire',
        'nature',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'nature' => 'integer',
    ];

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
    public function operations(): HasMany
    {
        return $this->hasMany(OperationCompte::class, 'id_type_operation');
    }

    /**
     * =================================================
     * HELPERS MÉTIER
     * =================================================
     */
    public function isEntree(): bool
    {
        return $this->nature === 1;
    }

    public function isSortie(): bool
    {
        return $this->nature === 0;
    }

    public function isTransfert(): bool
    {
        return $this->nature === 2;
    }
}
