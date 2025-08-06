<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BonusController extends Controller
{
    /**
     * Get all bonus types
     */
    public function getBonusTypes(): JsonResponse
    {
        $bonusTypes = BonusType::withCount('userBonuses')->get();

        return response()->json([
            'success' => true,
            'bonus_types' => $bonusTypes,
        ]);
    }

    /**
     * Create new bonus type
     */
    public function createBonusType(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:bonus_types,code',
            'description' => 'nullable|string',
            'type' => 'required|in:free_spins,bonus_coins,multiplier,no_deposit,deposit_match,cashback',
            'config' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $bonusType = BonusType::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Bonus type created successfully',
            'bonus_type' => $bonusType,
        ]);
    }

    /**
     * Update bonus type
     */
    public function updateBonusType(Request $request, BonusType $bonusType): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'config' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $bonusType->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Bonus type updated successfully',
            'bonus_type' => $bonusType,
        ]);
    }
}
