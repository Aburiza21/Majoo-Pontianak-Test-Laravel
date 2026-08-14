<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Merchant;
use App\Models\Outlet;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    /**
     * GET /api/report/monthly
     * Query params: merchant_id, outlet_id (optional), month (YYYY-MM)
     */
    #[OA\Get(
        path: "/api/report/monthly",
        summary: "Get monthly report",
        tags: ["Reports"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "merchant_id",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "outlet_id",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "month",
                in: "query",
                required: true,
                description: "Format: YYYY-MM",
                schema: new OA\Schema(type: "string", format: "date", example: "2026-08")
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 10)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful response"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
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
        $user       = $request->user();

        // 1. Strict Multi-Tenancy Validation
        $merchant = Merchant::where('id', $merchantId)->where('user_id', $user->id)->first();
        if (!$merchant) {
            return response()->json(['error' => 'Forbidden. Merchant does not belong to you or does not exist.'], 403);
        }

        if ($outletId) {
            $outlet = Outlet::where('id', $outletId)->where('merchant_id', $merchantId)->first();
            if (!$outlet) {
                return response()->json(['error' => 'Forbidden or Not Found. Outlet does not belong to this merchant.'], 403);
            }
        }

        // Parse year and month
        [$year, $mon] = explode('-', $month);

        // 2. Base query and report generation wrapped in Redis Cache
        // Cache key based on merchant, outlet, and month
        $cacheKey = "report_monthly_{$merchantId}_{$outletId}_{$month}";
        
        // Cache for 60 minutes
        $cachedReport = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60 * 60, function () use ($merchantId, $outletId, $month, $year, $mon) {
            $query = DB::table('transactions')
                ->selectRaw("
                    DATE(created_at) AS date,
                    SUM(bill_total)  AS total_revenue,
                    COUNT(id)        AS total_transactions
                ")
                ->where('merchant_id', $merchantId)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $mon);

            if ($outletId) {
                $query->where('outlet_id', $outletId);
            }

            $transactions = $query
                ->groupByRaw("DATE(created_at)")
                ->get()
                ->keyBy('date');

            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $daysInMonth = $startDate->daysInMonth;
            
            $reportData = [];
            $totalRevenue = 0;
            $totalTransactions = 0;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dateStr = $startDate->copy()->day($i)->format('Y-m-d');
                
                if ($transactions->has($dateStr)) {
                    $tx = $transactions->get($dateStr);
                    $revenue = (float) $tx->total_revenue;
                    $count = (int) $tx->total_transactions;
                } else {
                    $revenue = 0;
                    $count = 0;
                }
                
                $reportData[] = [
                    'date' => $dateStr,
                    'total_revenue' => $revenue,
                    'total_transactions' => $count
                ];
                
                $totalRevenue += $revenue;
                $totalTransactions += $count;
            }

            $summary = [
                'merchant_id'         => $merchantId,
                'outlet_id'           => $outletId,
                'month'               => $month,
                'total_revenue'       => $totalRevenue,
                'total_transactions'  => $totalTransactions,
            ];

            return [
                'summary' => $summary,
                'reportData' => $reportData
            ];
        });

        $summary = $cachedReport['summary'];
        $reportData = $cachedReport['reportData'];

        // 4. Pagination
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);
        
        $pagedData = array_slice($reportData, ($currentPage - 1) * $perPage, $perPage);
        
        $paginator = new LengthAwarePaginator(array_values($pagedData), count($reportData), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query()
        ]);

        return response()->json([
            'summary' => $summary,
            'details' => $paginator,
        ]);
    }
}
