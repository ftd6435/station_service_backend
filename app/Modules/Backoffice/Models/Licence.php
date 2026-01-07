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

    // protected $casts = [
    //     'code_licence' => 'encrypted',
    // ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // This accessor handles decryption
    public function getCodeLicenceAttribute($value)
    {
        // If value is null or already decrypted, return as-is
        if (!$value || !$this->isEncrypted($value)) {
            return $value;
        }

        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            Log::warning('Failed to decrypt licence code', [
                'licence_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            return 'DECRYPTION_ERROR';
        }
    }

    // This mutator handles encryption
    public function setCodeLicenceAttribute($value)
    {
        // Don't encrypt if it's already encrypted, null, or the error placeholder
        if (!$value || $value === 'DECRYPTION_ERROR' || $this->isEncrypted($value)) {
            $this->attributes['code_licence'] = $value;
        } else {
            $this->attributes['code_licence'] = encrypt($value);
        }
    }

    // Helper method to check if a value is already encrypted
    private function isEncrypted($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $json = json_decode($decoded, true);
        return is_array($json) && isset($json['iv'], $json['value'], $json['mac']);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
