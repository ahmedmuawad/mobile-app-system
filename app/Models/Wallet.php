<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'wallet_provider_id',
        'owner_name',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(WalletProvider::class, 'wallet_provider_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
