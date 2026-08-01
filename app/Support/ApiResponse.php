<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Standard API response shapes, used across every module (current and future)
 * so every paginated endpoint in the app returns the exact same structure:
 *
 *   {
 *     "success": true,
 *     "message": "",
 *     "data": [ ... ],
 *     "pagination": {
 *       "current_page": 1,
 *       "total_pages": 20,
 *       "per_page": 20,
 *       "total_items": 387
 *     }
 *   }
 *
 * Incoming requests control paging via `current_page` and `per_page` query/body
 * params (not Laravel's default `page`) — see perPage() and the pageName used
 * when calling ->paginate() in each controller.
 */
class ApiResponse
{
    public const PAGE_NAME = 'current_page';
    public const DEFAULT_PER_PAGE = 20;
    public const MAX_PER_PAGE = 100;

    /** Reads and clamps the requested page size from `per_page`, defaulting to 20, capped at 100. */
    public static function perPage(Request $request, int $default = self::DEFAULT_PER_PAGE, int $max = self::MAX_PER_PAGE): int
    {
        return max(1, min($max, $request->integer('per_page', $default)));
    }

    /** Wrap a paginator into the standard { success, data, pagination } shape. */
    public static function paginated(LengthAwarePaginator $paginator, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
            ],
        ], $code);
    }

    /** Standard non-paginated success response, for consistency alongside paginated(). */
    public static function success(mixed $data = [], string $message = '', int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    /** Standard error response. */
    public static function error(string $message, int $code = 400, mixed $data = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'data' => $data], $code);
    }
}
