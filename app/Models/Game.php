<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function configuration()
    {
        return $this->hasMany(GameConfiguration::class);
    }

    public function reelsConfiguration()
    {
        return $this->hasOne(GameConfiguration::class)->reels();
    }

    public function rowsConfiguration()
    {
        return $this->hasOne(GameConfiguration::class)->rowsNumber();
    }

    public function paylinesConfiguration()
    {
        return $this->hasOne(GameConfiguration::class)->paylines();
    }

    public function paytableConfiguration()
    {
        return $this->hasOne(GameConfiguration::class)->paytable();
    }

    public function scatterConfigurations()
    {
        return $this->hasMany(GameConfiguration::class)->scatters();
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

    /**
     * Get the game-specific configuration model
     */
    public function getGameConfiguration()
    {
        $configurationClass = $this->type->getConfigurationModel();
        return $configurationClass::where('game_id', $this->id)->first();
    }

    /**
     * Relationship to slot configuration
     */
    public function slotConfiguration()
    {
        return $this->hasOne(SlotConfiguration::class);
    }
}
