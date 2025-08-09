<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
  /**
   * Display a listing of facilities (public access)
   */
  public function index(): JsonResponse
  {
    $facilities = Facility::all();

    return response()->json([
      'success' => true,
      'message' => 'Facilities retrieved successfully',
      'data' => $facilities
    ]);
  }

  /**
   * Display the specified facility (public access)
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
}
