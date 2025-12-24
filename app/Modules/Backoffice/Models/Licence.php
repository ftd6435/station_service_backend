<?php

namespace App\Modules\Backoffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Licence extends Model
{
    protected $table = 'licences';

    protected $fillable = [
        'code_licence',
        'date_achat',
        'date_expiration',
        'days',
        'is_available',
        'is_sent',
        'client_id',
        'created_by',
    ];

    protected $casts = [
        'code_licence' => 'encrypted',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
