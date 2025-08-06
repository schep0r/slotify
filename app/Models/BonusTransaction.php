<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusTransaction extends Model
{
    protected $fillable = [
        'user_bonus_id', 'type', 'amount', 'description', 'game_data'
    ];

    protected $casts = [
        'game_data' => 'array',
    ];

    public function userBonus(): BelongsTo
    {
        return $this->belongsTo(UserBonus::class);
    }
}
