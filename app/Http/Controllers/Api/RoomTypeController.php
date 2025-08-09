<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
  /**
   * Display a listing of room types with filtering, sorting, and searching
   */
  public function index(Request $request): JsonResponse
  {
    $query = RoomType::with(['images', 'facilities', 'features']);

    // Search by name or description
    if ($request->has('search') && $request->search) {
      $searchTerm = $request->search;
      $query->where(function ($q) use ($searchTerm) {
        $q->where('name', 'like', '%' . $searchTerm . '%')
          ->orWhere('description', 'like', '%' . $searchTerm . '%');
      });
    }

    // Filter by price range
    if ($request->has('min_price') && $request->min_price) {
      $query->where('price', '>=', (int) $request->min_price);
    }

    if ($request->has('max_price') && $request->max_price) {
      $query->where('price', '<=', (int) $request->max_price);
    }

    // Filter by area range
    if ($request->has('min_area') && $request->min_area) {
      $query->where('area', '>=', (int) $request->min_area);
    }

    if ($request->has('max_area') && $request->max_area) {
      $query->where('area', '<=', (int) $request->max_area);
    }

    // Filter by capacity
    if ($request->has('adult') && $request->adult) {
      $query->where('adult', '>=', (int) $request->adult);
    }

    if ($request->has('children') && $request->children) {
      $query->where('children', '>=', (int) $request->children);
    }

    // Filter by facilities
    if ($request->has('facilities') && $request->facilities) {
      $facilityIds = is_array($request->facilities) ? $request->facilities : explode(',', $request->facilities);
      $query->whereHas('facilities', function ($q) use ($facilityIds) {
        $q->whereIn('facilities.id', $facilityIds);
      });
    }

    // Filter by features
    if ($request->has('features') && $request->features) {
      $featureIds = is_array($request->features) ? $request->features : explode(',', $request->features);
      $query->whereHas('features', function ($q) use ($featureIds) {
        $q->whereIn('features.id', $featureIds);
      });
    }

    // Sorting
    $sortBy = $request->input('sort_by', 'price');
    $sortOrder = $request->input('sort_order', 'asc');

    $allowedSortFields = ['price', 'area', 'adult', 'children', 'name', 'created_at'];
    if (in_array($sortBy, $allowedSortFields)) {
      $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
    }

    // Pagination
    $limit = min((int) $request->input('limit', 10), 50); // Max 50 items per page
    $page = (int) $request->input('page', 1);

    $total = $query->count();
    $roomTypes = $query
      ->skip(($page - 1) * $limit)
      ->take($limit)
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Room types retrieved successfully',
      'data' => $roomTypes,
      'pagination' => [
        'total' => $total,
        'current_page' => $page,
        'limit' => $limit,
        'last_page' => ceil($total / $limit)
      ],
      'filters_applied' => [
        'search' => $request->search,
        'price_range' => [
          'min' => $request->min_price,
          'max' => $request->max_price
        ],
        'area_range' => [
          'min' => $request->min_area,
          'max' => $request->max_area
        ],
        'capacity' => [
          'adult' => $request->adult,
          'children' => $request->children
        ],
        'facilities' => $request->facilities,
        'features' => $request->features,
        'sort' => [
          'by' => $sortBy,
          'order' => $sortOrder
        ]
      ]
    ]);
  }

  /**
   * Display the specified room type
   */
  public function show($id): JsonResponse
  {
    $roomType = RoomType::with(['images', 'facilities', 'features'])->find($id);

    if (!$roomType) {
      return response()->json([
        'success' => false,
        'message' => 'Room type not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Room type retrieved successfully',
      'data' => $roomType
    ]);
  }
}
