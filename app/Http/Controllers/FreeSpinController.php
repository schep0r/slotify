<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Managers\FreeSpinManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FreeSpinController extends Controller
{
    protected $freeSpinManager;

    public function __construct(FreeSpinManager $freeSpinManager)
    {
        $this->freeSpinManager = $freeSpinManager;
    }

    /**
     * Get user's available free spins
     */
    public function getAvailableSpins(Request $request): JsonResponse
    {
        $user = $request->user();
        $gameId = $request->query('game_id');

        $availableSpins = $this->freeSpinManager->getAvailableFreeSpins($user, $gameId);
        $freeSpinRecords = $this->freeSpinManager->getUserFreeSpins($user, $gameId);

        return response()->json([
            'success' => true,
            'data' => [
                'available_spins' => $availableSpins,
                'free_spin_records' => $freeSpinRecords->map(function ($freeSpin) {
                    return [
                        'id' => $freeSpin->id,
                        'remaining_spins' => $freeSpin->getRemainingSpins(),
                        'bet_value' => $freeSpin->bet_value,
                        'game_restriction' => $freeSpin->game_restriction,
                        'expires_at' => $freeSpin->expires_at,
                        'source' => $freeSpin->source
                    ];
                })
            ]
        ]);
    }

    /**
     * Use a free spin
     */
    public function useSpin(Request $request): JsonResponse
    {
        $request->validate([
            'game_id' => 'required|string',
            'spin_result' => 'required|array',
            'win_amount' => 'required|numeric|min:0'
        ]);

        try {
            $user = $request->user();
            $transaction = $this->freeSpinManager->useFreeSpin(
                $user,
                $request->game_id,
                $request->spin_result,
                $request->win_amount
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'win_amount' => $transaction->win_amount,
                    'remaining_spins' => $this->freeSpinManager->getAvailableFreeSpins($user, $request->game_id),
                    'new_balance' => $user->fresh()->balance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get user's free spin statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->freeSpinManager->getUserFreeSpinStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Award free spins (Admin only)
     */
    public function awardSpins(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
            'source' => 'required|string',
            'bet_value' => 'nullable|numeric|min:0',
            'game_restriction' => 'nullable|string',
            'expires_in_days' => 'nullable|integer|min:1'
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $expiresAt = $request->expires_in_days ?
            now()->addDays($request->expires_in_days) : null;

        $freeSpin = $this->freeSpinManager->awardFreeSpins(
            $user,
            $request->amount,
            $request->source,
            $request->bet_value,
            $request->game_restriction,
            $expiresAt
        );

        return response()->json([
            'success' => true,
            'data' => $freeSpin
        ]);
    }
}
