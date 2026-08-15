<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use Exception;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

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
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "outlet_id",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
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
        $month      = $request->input('month');
        $user       = $request->user();

        try {
            // Retrieve data using the ReportService
            $reportData = $this->reportService->getMonthlyReport($merchantId, $outletId, $month, $user->id);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json(['error' => $e->getMessage()], $code);
        }

        $summary = $reportData['summary'];
        $details = $reportData['reportData'];

        // Pagination
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);
        
        $pagedData = array_slice($details, ($currentPage - 1) * $perPage, $perPage);
        
        $paginator = new LengthAwarePaginator(array_values($pagedData), count($details), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query()
        ]);

        return response()->json([
            'summary' => $summary,
            'details' => $paginator,
        ]);
    }
}
