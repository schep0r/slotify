<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Game;
use Illuminate\Support\Str;

class GameSession extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'game_id',
        'session_token',
        'total_spins',
        'total_bet',
        'total_win',
        'started_at',
        'ended_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_bet' => 'decimal:2',
        'total_win' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the user that owns the game session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the game that the session belongs to.
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
