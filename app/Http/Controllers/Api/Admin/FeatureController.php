<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeatureController extends Controller
{
  /**
   * Display a listing of features
   */
  public function index(Request $request): JsonResponse
  {
    $limit = (int) $request->input('limit', 20);
    $page = (int) $request->input('page', 1);

    $query = Feature::query();

    $total = $query->count(); // total records

    $features = $query
      ->skip(($page - 1) * $limit)
      ->take($limit)
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Features retrieved successfully',
      'data' => $features,
      'pagination' => [
        'total' => $total,
        'current_page' => $page,
        'limit' => $limit,
        'last_page' => ceil($total / $limit)
      ]
    ]);
  }

  /**
   * Store a newly created feature
   */
  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:50|unique:features,name',
      'content' => 'required|string|max:100'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $feature = Feature::create([
      'name' => $request->input('name'),
      'content' => $request->input('content')
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Feature created successfully',
      'data' => $feature
    ], 201);
  }

  /**
   * Display the specified feature
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

  /**
   * Update the specified feature
   */
  public function update(Request $request, $id): JsonResponse
  {
    $feature = Feature::find($id);

    if (!$feature) {
      return response()->json([
        'success' => false,
        'message' => 'Feature not found'
      ], 404);
    }

    if (count($request->all()) == 0) {
      return response()->json([
        'success' => false,
        'message' => 'No data to update'
      ], 400);
    }

    $validator = Validator::make($request->all(), [
      'name' => 'nullable|string|max:50|unique:features,name,' . $id,
      'content' => 'nullable|string|max:100'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $feature->update([
      'name' => $request->input('name') ?? $feature->name,
      'content' => $request->input('content') ?? $feature->content
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Feature updated successfully',
      'data' => $feature->fresh()
    ]);
  }

  /**
   * Remove the specified feature
   */
  public function destroy($id): JsonResponse
  {
    $feature = Feature::find($id);

    if (!$feature) {
      return response()->json([
        'success' => false,
        'message' => 'Feature not found'
      ], 404);
    }

    // Check if feature is linked to any room types
    if ($feature->roomTypes()->count() > 0) {
      return response()->json([
        'success' => false,
        'message' => 'Cannot delete feature. It is linked to room types.'
      ], 400);
    }

    $feature->delete();

    return response()->json([
      'success' => true,
      'message' => 'Feature deleted successfully'
    ]);
  }
}
