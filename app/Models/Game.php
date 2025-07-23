<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GameSession;

class Game extends Model
{
    use HasFactory;

    /**
     * Get the game sessions for the game.
     */
    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'min_bet',
        'max_bet',
        'reels',
        'rows',
        'paylines',
        'rtp',
        'configuration',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_bet' => 'decimal:2',
        'max_bet' => 'decimal:2',
        'rtp' => 'decimal:2',
        'configuration' => 'json',
        'is_active' => 'boolean',
    ];
}
