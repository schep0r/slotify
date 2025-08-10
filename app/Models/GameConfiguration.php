<?php

namespace App\Models;

use App\Enums\GameConfigurationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'key', // 'paylines', 'reels_count', 'rows_count', 'bonus_features', etc.
        'value',
        'data_type', // 'integer', 'string', 'boolean', 'json', 'decimal'
        'description',
        'is_configurable', // Can be changed by operators
        'sort_order'
    ];

    protected $casts = [
        'is_configurable' => 'boolean',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // Accessor to cast value based on data_type
    public function getValueAttribute($value)
    {
        return match ($this->data_type) {
            'integer' => (int)$value,
            'boolean' => (bool)$value,
            'decimal' => (float)$value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }



    public function scopeReels($query)
    {
        return $query->where('key', GameConfigurationType::REELS->value);
    }

    public function scopePaylines($query)
    {
        return $query->where('key', GameConfigurationType::PAYLINES->value);
    }

    public function scopeRowsNumber($query)
    {
        return $query->where('key', GameConfigurationType::ROWS_COUNT->value);
    }

    public function scopePaytable($query)
    {
        return $query->where('key', GameConfigurationType::PAYTABLE->value);
    }
}
