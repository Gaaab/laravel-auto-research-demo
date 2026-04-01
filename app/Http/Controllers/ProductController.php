<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $query = Product::select(['id', 'name', 'price', 'category'])
            ->with(['reviews', 'tags'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        $sortBy = $request->input('sort', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 50);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }
}
