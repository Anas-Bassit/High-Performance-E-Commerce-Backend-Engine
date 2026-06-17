<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function popularNoCache(ProductService $productService): JsonResponse
    {

        return response()->json($productService->popularNoCache());
    }

    public function popular(ProductService $productService): JsonResponse
    {

        return response()->json($productService->popular());
    }

    public function clearPopularCache(ProductService $productService): JsonResponse
    {

        return response()->json($productService->clearPopularCache());
    }
}
