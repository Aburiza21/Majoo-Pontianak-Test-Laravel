<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * GET /api/report/monthly
     * Query params: merchant_id, outlet_id (optional), month (YYYY-MM)
     */
    public function monthly(Request $request)
    {
        $request->validate([
            'merchant_id' => 'required|integer',
            'outlet_id'   => 'nullable|integer',
            'month'       => 'required|date_format:Y-m',
        ]);

        $merchantId = $request->integer('merchant_id');
        $outletId   = $request->integer('outlet_id');
        $month      = $request->input('month'); // e.g. "2026-08"

        // Parse year and month
        [$year, $mon] = explode('-', $month);

        // Base query: filter by merchant and date range
        $query = DB::table('transactions')
            ->selectRaw("
                outlet_id,
                TO_CHAR(created_at, 'YYYY-MM-DD') AS date,
                SUM(bill_total)                    AS total_revenue,
                COUNT(id)                          AS total_transactions
            ")
            ->where('merchant_id', $merchantId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $mon);

        // Optional outlet filter
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $rows = $query
            ->groupByRaw("outlet_id, TO_CHAR(created_at, 'YYYY-MM-DD')")
            ->orderByRaw("outlet_id, date")
            ->get();

        // Calculate summary totals
        $summary = [
            'merchant_id'         => $merchantId,
            'month'               => $month,
            'total_revenue'       => $rows->sum('total_revenue'),
            'total_transactions'  => $rows->sum('total_transactions'),
        ];

        return response()->json([
            'summary' => $summary,
            'details' => $rows,
        ]);
    }
}
