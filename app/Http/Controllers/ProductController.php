<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Filter
        if ($request->has('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        // Sort
        $sort = $request->input('sort', 'id');
        $query->orderBy($sort);

        // N+1 problem here!
        return $query->paginate(50);
    }
}
