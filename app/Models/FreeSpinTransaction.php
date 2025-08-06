<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreeSpinTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'free_spin_id',
        'game_id',
        'bet_amount',
        'win_amount',
        'spin_result',
        'played_at'
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
        'win_amount' => 'decimal:2',
        'spin_result' => 'array',
        'played_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function freeSpin(): BelongsTo
    {
        return $this->belongsTo(FreeSpin::class);
    }
}
