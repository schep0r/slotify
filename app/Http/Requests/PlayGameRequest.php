<?php

namespace App\Http\Requests;

use App\Engines\SlotGameEngine;
use App\Models\Game;
use Exception;
use Illuminate\Foundation\Http\FormRequest;

class PlayGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(SlotGameEngine $gameEngine): array
    {
        $game = $this->route('game');

        if (!$game instanceof Game) {
            return [];
        }

        // Get game-specific validation rules
        try {
            $gameSpecificRules = $gameEngine->getRequiredInputs();
        } catch (Exception $e) {
            $gameSpecificRules = [];
        }

        return $gameSpecificRules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'betAmount.required' => 'Bet amount is required',
            'betAmount.numeric' => 'Bet amount must be a number',
            'betAmount.min' => 'Bet amount must be at least 0.01',
            'bets.required' => 'At least one bet is required',
            'bets.array' => 'Bets must be an array',
            'bets.min' => 'At least one bet is required',
            'bets.*.type.required' => 'Bet type is required',
            'bets.*.type.in' => 'Invalid bet type',
            'bets.*.amount.required' => 'Bet amount is required',
            'bets.*.amount.numeric' => 'Bet amount must be a number',
            'bets.*.amount.min' => 'Bet amount must be at least 0.01',
            'activePaylines.array' => 'Active paylines must be an array',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $game = $this->route('game');

            if (!$game instanceof Game) {
                $validator->errors()->add('game', 'Invalid game');
                return;
            }

            // Check if game is active
            if (!$game->is_active) {
                $validator->errors()->add('game', 'Game is not active');
            }

            // Balance validation is now handled by BalanceCheckMiddleware
        });
    }
}
