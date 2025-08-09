<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
  /**
   * Display a listing of features (public access)
   */
  public function index(): JsonResponse
  {
    $features = Feature::all();

    return response()->json([
      'success' => true,
      'message' => 'Features retrieved successfully',
      'data' => $features
    ]);
  }

  /**
   * Display the specified feature (public access)
   */
  public function show($id): JsonResponse
  {
    $feature = Feature::find($id);

    if (!$feature) {
      return response()->json([
        'success' => false,
        'message' => 'Feature not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Feature retrieved successfully',
      'data' => $feature
    ]);
  }
}
