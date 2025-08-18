<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BonusType;
use App\Managers\BonusManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BonusController extends Controller
{
    protected BonusManager $bonusManager;

    public function __construct(BonusManager $bonusManager)
    {
        $this->bonusManager = $bonusManager;
    }

    /**
     * Get available bonuses for user
     */
    public function getAvailableBonuses(Request $request): JsonResponse
    {
        $user = $request->user();
        $bonusTypes = BonusType::where('is_active', true)->get();

        $availableBonuses = [];

        foreach ($bonusTypes as $bonusType) {
            $canClaim = $this->bonusManager->canClaimBonus($user, $bonusType);

            $availableBonuses[] = [
                'id' => $bonusType->id,
                'name' => $bonusType->name,
                'description' => $bonusType->description,
                'type' => $bonusType->type,
                'config' => $bonusType->config,
                'can_claim' => $canClaim['can_claim'],
                'reason' => $canClaim['reason'] ?? null,
                'next_claim_at' => $canClaim['next_claim_at'] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'bonuses' => $availableBonuses,
        ]);
    }

    /**
     * Claim a bonus
     */
    public function claimBonus(Request $request, int $bonusTypeId): JsonResponse
    {
        $request->validate([
            'bonus_type_id' => 'sometimes|exists:bonus_types,id',
        ]);

        $user = $request->user();
        $bonusType = BonusType::findOrFail($bonusTypeId);

        try {
            $userBonus = $this->bonusManager->claimBonus(
                $user,
                $bonusType,
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Bonus claimed successfully!',
                'bonus' => [
                    'id' => $userBonus->id,
                    'type' => $userBonus->bonusType->type,
                    'name' => $userBonus->bonusType->name,
                    'amount' => $userBonus->amount,
                    'status' => $userBonus->status,
                    'expires_at' => $userBonus->expires_at,
                    'wagering_requirement' => $userBonus->wagering_requirement,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's active bonus
     */
    public function getActiveBonus(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeBonus = $this->bonusManager->getActiveBonusForUser($user);

        if (!$activeBonus) {
            return response()->json([
                'success' => true,
                'bonus' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'bonus' => [
                'id' => $activeBonus->id,
                'type' => $activeBonus->bonusType->type,
                'name' => $activeBonus->bonusType->name,
                'amount' => $activeBonus->amount,
                'used_amount' => $activeBonus->used_amount,
                'remaining' => $activeBonus->getRemainingAmount(),
                'status' => $activeBonus->status,
                'wagering_progress' => $activeBonus->getWageringProgress(),
                'wagering_requirement' => $activeBonus->wagering_requirement,
                'wagered_amount' => $activeBonus->wagered_amount,
                'expires_at' => $activeBonus->expires_at,
                'is_expired' => $activeBonus->isExpired(),
            ],
        ]);
    }

    /**
     * Get user's bonus history
     */
    public function getBonusHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->input('limit', 20);

        $history = $this->bonusManager->getUserBonusHistory($user, $limit);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Use bonus for slot spin (called from slot game)
     */
    public function useBonusForSpin(Request $request): JsonResponse
    {
        $request->validate([
            'bet_amount' => 'required|integer|min:1',
            'game_result' => 'required|array',
            'game_result.win_amount' => 'required|integer|min:0',
            'game_result.symbols' => 'required|array',
            'game_result.paylines' => 'sometimes|array',
        ]);

        $user = $request->user();
        $betAmount = $request->input('bet_amount');
        $gameResult = $request->input('game_result');

        try {
            $bonusResult = $this->bonusManager->useBonusForSpin($user, $betAmount, $gameResult);

            return response()->json([
                'success' => true,
                'bonus_result' => $bonusResult,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get bonus statistics for user dashboard
     */
    public function getBonusStats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_claimed' => $user->userBonuses()->count(),
            'active_bonuses' => $user->userBonuses()->active()->count(),
            'completed_bonuses' => $user->userBonuses()->where('status', 'used')->count(),
            'expired_bonuses' => $user->userBonuses()->where('status', 'expired')->count(),
            'total_bonus_wins' => $user->userBonuses()
                ->join('bonus_transactions', 'user_bonuses.id', '=', 'bonus_transactions.user_bonus_id')
                ->where('bonus_transactions.type', 'credit')
                ->sum('bonus_transactions.amount'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
