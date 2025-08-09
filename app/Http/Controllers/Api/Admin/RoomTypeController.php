<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Models\RoomImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoomTypeController extends Controller
{
  /**
   * Display a listing of room types
   */
  public function index(Request $request): JsonResponse
  {
    $limit = (int) $request->input('limit', 20);
    $page = (int) $request->input('page', 1);

    $query = RoomType::with(['images', 'facilities', 'features', 'rooms']);

    // Search functionality
    if ($request->has('search') && $request->search) {
      $searchTerm = $request->search;
      $query->where(function ($q) use ($searchTerm) {
        $q->where('name', 'like', '%' . $searchTerm . '%')
          ->orWhere('description', 'like', '%' . $searchTerm . '%');
      });
    }

    $total = $query->count();

    $roomTypes = $query
      ->orderBy('created_at', 'desc')
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
      ]
    ]);
  }

  /**
   * Store a newly created room type
   */
  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255|unique:room_types,name',
      'area' => 'required|integer|min:1',
      'price' => 'required|integer|min:0',
      'quantity' => 'required|integer|min:0',
      'adult' => 'required|integer|min:1|max:10',
      'children' => 'required|integer|min:0|max:10',
      'description' => 'required|string|max:500',
      'facilities' => 'required|array',
      'facilities.*' => 'integer|exists:facilities,id',
      'features' => 'required|array',
      'features.*' => 'integer|exists:features,id',
      'images' => 'nullable|array',
      'images.*' => 'string|max:255', // Image paths
      'thumbnail_image' => 'nullable|string|max:255' // Path of the thumbnail image
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    DB::beginTransaction();

    try {
      $roomType = RoomType::create([
        'name' => $request->input('name'),
        'area' => $request->input('area'),
        'price' => $request->input('price'),
        'quantity' => $request->input('quantity'),
        'adult' => $request->input('adult'),
        'children' => $request->input('children'),
        'description' => $request->input('description')
      ]);

      // Assign facilities
      if ($request->has('facilities') && is_array($request->facilities)) {
        $roomType->facilities()->attach($request->facilities);
      }

      // Assign features
      if ($request->has('features') && is_array($request->features)) {
        $roomType->features()->attach($request->features);
      }

      // Attach images if provided
      if ($request->has('images') && is_array($request->images)) {
        $thumbnailImage = $request->input('thumbnail_image');

        foreach ($request->images as $imagePath) {
          $isThumb = ($thumbnailImage && $imagePath === $thumbnailImage);

          // If setting this as thumbnail, remove thumbnail status from existing images
          if ($isThumb) {
            RoomImage::where('room_type_id', $roomType->id)
              ->update(['is_thumbnail' => false]);
          }

          RoomImage::create([
            'room_type_id' => $roomType->id,
            'path' => $imagePath,
            'is_thumbnail' => $isThumb
          ]);
        }
      }

      DB::commit();

      // Load relationships for response
      $roomType->load(['facilities', 'features', 'images']);

      return response()->json([
        'success' => true,
        'message' => 'Room type created successfully',
        'data' => $roomType
      ], 201);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        'success' => false,
        'message' => 'Failed to create room type',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Display the specified room type
   */
  public function show($id): JsonResponse
  {
    $roomType = RoomType::with(['images', 'facilities', 'features', 'rooms'])->find($id);

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

  /**
   * Update the specified room type
   */
  public function update(Request $request, $id): JsonResponse
  {
    $roomType = RoomType::find($id);

    if (!$roomType) {
      return response()->json([
        'success' => false,
        'message' => 'Room type not found'
      ], 404);
    }

    if (count($request->all()) == 0) {
      return response()->json([
        'success' => false,
        'message' => 'No data to update'
      ], 400);
    }

    $validator = Validator::make($request->all(), [
      'name' => 'nullable|string|max:255|unique:room_types,name,' . $id,
      'area' => 'nullable|integer|min:1',
      'price' => 'nullable|integer|min:0',
      'quantity' => 'nullable|integer|min:0',
      'adult' => 'nullable|integer|min:1|max:10',
      'children' => 'nullable|integer|min:0|max:10',
      'description' => 'nullable|string|max:500',
      'facilities' => 'nullable|array',
      'facilities.*' => 'integer|exists:facilities,id',
      'features' => 'nullable|array',
      'features.*' => 'integer|exists:features,id',
      'images' => 'nullable|array',
      'images.*' => 'string|max:255', // Image paths
      'thumbnail_image' => 'nullable|string|max:255', // Path of the thumbnail image
      'replace_images' => 'nullable|boolean' // Whether to replace all images or add to existing
    ]);


    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    DB::beginTransaction();

    try {
      // Update room type basic info
      $roomType->update([
        'name' => $request->input('name') ?? $roomType->name,
        'area' => $request->input('area') ?? $roomType->area,
        'price' => $request->input('price') ?? $roomType->price,
        'quantity' => $request->input('quantity') ?? $roomType->quantity,
        'adult' => $request->input('adult') ?? $roomType->adult,
        'children' => $request->input('children') ?? $roomType->children,
        'description' => $request->input('description') ?? $roomType->description
      ]);

      // Update facilities if provided
      if ($request->has('facilities')) {
        if (is_array($request->facilities)) {
          $roomType->facilities()->sync($request->facilities);
        } else {
          $roomType->facilities()->detach();
        }
      }

      // Update features if provided
      if ($request->has('features')) {
        if (is_array($request->features)) {
          $roomType->features()->sync($request->features);
        } else {
          $roomType->features()->detach();
        }
      }

      // Handle images update
      if ($request->has('images')) {
        $replaceImages = $request->input('replace_images', false);
        $thumbnailImage = $request->input('thumbnail_image');

        // If replace_images is true, delete all existing images
        if ($replaceImages) {
          RoomImage::where('room_type_id', $roomType->id)->delete();
        }

        if (is_array($request->images) && !empty($request->images)) {
          // Reset all thumbnails first if we have a new thumbnail
          if ($thumbnailImage) {
            RoomImage::where('room_type_id', $roomType->id)
              ->update(['is_thumbnail' => false]);
          }

          foreach ($request->images as $imagePath) {
            // Check if this image already exists for this room type
            $existingImage = RoomImage::where('room_type_id', $roomType->id)
              ->where('path', $imagePath)
              ->first();

            if (!$existingImage) {
              $isThumb = ($thumbnailImage && $imagePath === $thumbnailImage);

              RoomImage::create([
                'room_type_id' => $roomType->id,
                'path' => $imagePath,
                'is_thumbnail' => $isThumb
              ]);
            } else if ($thumbnailImage && $imagePath === $thumbnailImage) {
              // Update existing image to be thumbnail
              $existingImage->update(['is_thumbnail' => true]);
            }
          }
        }
      }

      DB::commit();

      // Load relationships for response
      $roomType->load(['facilities', 'features', 'images']);

      return response()->json([
        'success' => true,
        'message' => 'Room type updated successfully',
        'data' => $roomType
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        'success' => false,
        'message' => 'Failed to update room type',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Remove the specified room type
   */
  public function destroy($id): JsonResponse
  {
    $roomType = RoomType::with(['rooms', 'bookingOrders'])->find($id);

    if (!$roomType) {
      return response()->json([
        'success' => false,
        'message' => 'Room type not found'
      ], 404);
    }

    // Check if room type has active bookings
    $activeBookings = $roomType->bookingOrders()
      ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
      ->count();

    if ($activeBookings > 0) {
      return response()->json([
        'success' => false,
        'message' => 'Cannot delete room type. It has active bookings.'
      ], 400);
    }

    DB::beginTransaction();

    try {
      // Delete room type
      $roomType->delete();

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Room type deleted successfully'
      ]);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete room type',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
