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
        'branch_id'
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(WalletProvider::class, 'wallet_provider_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
    // App\Models\Wallet.php

public function branch(): BelongsTo
{
    return $this->belongsTo(Branch::class);
}

// لحساب الرصيد الحالي تلقائيًا
public function getBalanceAttribute(): float
{
    $send = $this->transactions()->where('type', 'send')->sum('amount');
    $receive = $this->transactions()->where('type', 'receive')->sum('amount');

    return $receive - $send;
}


}
