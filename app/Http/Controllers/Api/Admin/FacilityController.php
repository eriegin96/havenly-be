<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FacilityController extends Controller
{
  /**
   * Display a listing of facilities
   */
  public function index(Request $request): JsonResponse
  {
    $limit = (int) $request->input('limit', 20);
    $page = (int) $request->input('page', 1);

    $query = Facility::query();

    $total = $query->count(); // total records

    $facilities = $query
      ->skip(($page - 1) * $limit)
      ->take($limit)
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Facilities retrieved successfully',
      'data' => $facilities,
      'pagination' => [
        'total' => $total,
        'current_page' => $page,
        'limit' => $limit,
        'last_page' => ceil($total / $limit)
      ]
    ]);
  }

  /**
   * Store a newly created facility
   */
  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:50|unique:facilities,name',
      'content' => 'required|string|max:100',
      'description' => 'required|string|max:255'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $facility = Facility::create([
      'name' => $request->input('name'),
      'content' => $request->input('content'),
      'description' => $request->input('description')
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Facility created successfully',
      'data' => $facility
    ], 201);
  }

  /**
   * Display the specified facility
   */
  public function show($id): JsonResponse
  {
    $facility = Facility::find($id);

    if (!$facility) {
      return response()->json([
        'success' => false,
        'message' => 'Facility not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Facility retrieved successfully',
      'data' => $facility
    ]);
  }

  /**
   * Update the specified facility
   */
  public function update(Request $request, $id): JsonResponse
  {
    $facility = Facility::find($id);

    if (!$facility) {
      return response()->json([
        'success' => false,
        'message' => 'Facility not found'
      ], 404);
    }

    if (count($request->all()) == 0) {
      return response()->json([
        'success' => false,
        'message' => 'No data to update'
      ], 400);
    }

    $validator = Validator::make($request->all(), [
      'name' => 'nullable|string|max:50|unique:facilities,name,' . $id,
      'content' => 'nullable|string|max:100',
      'description' => 'nullable|string|max:255'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $facility->update([
      'name' => $request->input('name') ?? $facility->name,
      'content' => $request->input('content') ?? $facility->content,
      'description' => $request->input('description') ?? $facility->description
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Facility updated successfully',
      'data' => $facility->fresh()
    ]);
  }

  /**
   * Remove the specified facility
   */
  public function destroy($id): JsonResponse
  {
    $facility = Facility::find($id);

    if (!$facility) {
      return response()->json([
        'success' => false,
        'message' => 'Facility not found'
      ], 404);
    }

    // Check if facility is linked to any room types
    if ($facility->roomTypes()->count() > 0) {
      return response()->json([
        'success' => false,
        'message' => 'Cannot delete facility. It is linked to room types.'
      ], 400);
    }

    $facility->delete();

    return response()->json([
      'success' => true,
      'message' => 'Facility deleted successfully'
    ]);
  }
}
