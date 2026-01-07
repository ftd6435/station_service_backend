<?php

namespace App\Modules\Backoffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
