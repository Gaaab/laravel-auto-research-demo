<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * =============================================================================
 * ProductController — AGENT SANDBOX (the only file the agent modifies)
 * =============================================================================
 *
 * This is the equivalent of train.py in Karpathy's autoresearch.
 * The AI agent is free to modify ANYTHING in this file to improve performance.
 *
 * Current implementation: deliberately naive baseline with known bottlenecks:
 * - No eager loading (N+1 query problem)
 * - SELECT * (fetches all columns)
 * - No caching
 * - Offset pagination (slow on large tables)
 * - Loads all relationship data unnecessarily
 *
 * The metric is avg_ms from benchmark.sh — lower is better.
 * =============================================================================
 */
class ProductController extends Controller
{
    /**
     * GET /api/products
     *
     * Query params:
     *   - category (string, optional): filter by category
     *   - sort (string, optional): column to sort by (default: 'id')
     *   - sort_dir (string, optional): 'asc' or 'desc' (default: 'asc')
     *   - per_page (int, optional): items per page (default: 50)
     *
     * Must return paginated JSON with structure:
     *   {
     *     "data": [ { "id", "name", "price", "category", ... }, ... ],
     *     "links": { ... },
     *     "meta": { "current_page", "last_page", "per_page", "total", ... }
     *   }
     *
     * Each product in "data" MUST include at minimum: id, name, price, category.
     * Products may also include: reviews, tags, description, sku, etc.
     */
    public function index(Request $request): JsonResponse
    {
        $key = 'products:' . $request->input('category', '') . ':' . $request->input('sort', 'id') . ':' . $request->input('sort_dir', 'asc') . ':' . $request->input('per_page', 50) . ':' . $request->input('page', 1);

        return response()->json(Cache::remember($key, 60, function () use ($request) {
            $query = Product::select(['id', 'name', 'price', 'category']);

            if ($request->has('category')) {
                $query->where('category', $request->input('category'));
            }

            return $query->orderBy($request->input('sort', 'id'), $request->input('sort_dir', 'asc'))
                ->simplePaginate((int) $request->input('per_page', 50));
        }));
    }
}
