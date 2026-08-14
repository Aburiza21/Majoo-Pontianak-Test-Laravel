<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Merchant;
use App\Models\Outlet;
use Carbon\Carbon;
use Exception;

class ReportService
{
    /**
     * Get the monthly report data
     *
     * @param int $merchantId
     * @param int|null $outletId
     * @param string $month "YYYY-MM"
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function getMonthlyReport(int $merchantId, ?int $outletId, string $month, int $userId): array
    {
        // 1. Strict Multi-Tenancy Validation
        $merchant = Merchant::where('id', $merchantId)->where('user_id', $userId)->first();
        if (!$merchant) {
            throw new Exception('Forbidden. Merchant does not belong to you or does not exist.', 403);
        }

        if ($outletId) {
            $outlet = Outlet::where('id', $outletId)->where('merchant_id', $merchantId)->first();
            if (!$outlet) {
                throw new Exception('Forbidden or Not Found. Outlet does not belong to this merchant.', 403);
            }
        }

        // Parse year and month
        [$year, $mon] = explode('-', $month);

        // 2. Base query and report generation wrapped in Redis Cache
        // Cache key based on merchant, outlet, and month
        $cacheKey = "report_monthly_{$merchantId}_{$outletId}_{$month}";
        
        // Cache for 60 minutes
        return Cache::remember($cacheKey, 60 * 60, function () use ($merchantId, $outletId, $month, $year, $mon) {
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
    }
}
