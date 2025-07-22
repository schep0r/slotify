<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'gameSession'])
            ->where('user_id', auth()->id());

        // Apply filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange(
                Carbon::parse($request->date_from),
                Carbon::parse($request->date_to)
            );
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ;
    }

    public function summary(Request $request)
    {
        $startDate = $request->date_from ? Carbon::parse($request->date_from) : null;
        $endDate = $request->date_to ? Carbon::parse($request->date_to) : null;

        return Transaction::getUserSummary(auth()->id(), $startDate, $endDate);
    }
}
