<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class RoomImageController extends Controller
{
  /**
   * Display a listing of room images
   */
  public function index(Request $request): JsonResponse
  {
    $limit = (int) $request->input('limit', 20);
    $page = (int) $request->input('page', 1);

    $query = RoomImage::with('roomType');

    // Filter by room type
    if ($request->has('room_type_id') && $request->room_type_id) {
      $query->where('room_type_id', $request->room_type_id);
    }

    // Filter by thumbnail status
    if ($request->has('is_thumbnail')) {
      $query->where('is_thumbnail', $request->is_thumbnail === 'true' ? true : false);
    }

    // Search by image name
    if ($request->has('search') && $request->search) {
      $query->where('path', 'like', '%' . $request->search . '%');
    }

    $total = $query->count();

    $roomImages = $query
      ->orderBy('room_type_id', 'asc')
      ->orderBy('is_thumbnail', 'desc')
      ->orderBy('id', 'asc')
      ->skip(($page - 1) * $limit)
      ->take($limit)
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Room images retrieved successfully',
      'data' => $roomImages,
      'pagination' => [
        'total' => $total,
        'current_page' => $page,
        'limit' => $limit,
        'last_page' => ceil($total / $limit)
      ]
    ]);
  }

  /**
   * Store a newly created room image
   */
  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'room_type_id' => 'required|integer|exists:room_types,id',
      'path' => 'required|string|max:255',
      'is_thumbnail' => 'nullable|boolean'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    // Check if setting as thumbnail and there's already a thumbnail for this room type
    $isThumbnail = $request->input('is_thumbnail', false);
    if ($isThumbnail) {
      // Remove thumbnail status from other images of the same room type
      RoomImage::where('room_type_id', $request->room_type_id)
        ->where('is_thumbnail', true)
        ->update(['is_thumbnail' => false]);
    }

    $roomImage = RoomImage::create([
      'room_type_id' => $request->input('room_type_id'),
      'path' => $request->input('path'),
      'is_thumbnail' => $isThumbnail
    ]);

    // Load room type relationship
    $roomImage->load('roomType');

    return response()->json([
      'success' => true,
      'message' => 'Room image created successfully',
      'data' => $roomImage
    ], 201);
  }

  /**
   * Display the specified room image
   */
  public function show($id): JsonResponse
  {
    $roomImage = RoomImage::with('roomType')->find($id);

    if (!$roomImage) {
      return response()->json([
        'success' => false,
        'message' => 'Room image not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Room image retrieved successfully',
      'data' => $roomImage
    ]);
  }

  /**
   * Update the specified room image
   */
  public function update(Request $request, $id): JsonResponse
  {
    $roomImage = RoomImage::find($id);

    if (!$roomImage) {
      return response()->json([
        'success' => false,
        'message' => 'Room image not found'
      ], 404);
    }

    if (count($request->all()) == 0) {
      return response()->json([
        'success' => false,
        'message' => 'No data to update'
      ], 400);
    }

    $validator = Validator::make($request->all(), [
      'room_type_id' => 'nullable|integer|exists:room_types,id',
      'path' => 'nullable|string|max:255',
      'is_thumbnail' => 'nullable|boolean'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    // Handle thumbnail logic
    if ($request->has('is_thumbnail') && $request->is_thumbnail) {
      $roomTypeId = $request->input('room_type_id', $roomImage->room_type_id);

      // Remove thumbnail status from other images of the same room type
      RoomImage::where('room_type_id', $roomTypeId)
        ->where('id', '!=', $id)
        ->where('is_thumbnail', true)
        ->update(['is_thumbnail' => false]);
    }

    // Update room image
    $roomImage->update([
      'room_type_id' => $request->input('room_type_id', $roomImage->room_type_id),
      'path' => $request->input('path', $roomImage->path),
      'is_thumbnail' => $request->input('is_thumbnail', $roomImage->is_thumbnail)
    ]);

    // Load room type relationship
    $roomImage->load('roomType');

    return response()->json([
      'success' => true,
      'message' => 'Room image updated successfully',
      'data' => $roomImage
    ]);
  }

  /**
   * Remove the specified room image
   */
  public function destroy($id): JsonResponse
  {
    $roomImage = RoomImage::find($id);

    if (!$roomImage) {
      return response()->json([
        'success' => false,
        'message' => 'Room image not found'
      ], 404);
    }

    // Store room type id for potential thumbnail reassignment
    $roomTypeId = $roomImage->room_type_id;
    $wasThumbnail = $roomImage->is_thumbnail;

    // Delete the image
    $roomImage->delete();

    // If deleted image was a thumbnail, assign thumbnail to another image of the same room type
    if ($wasThumbnail) {
      $nextImage = RoomImage::where('room_type_id', $roomTypeId)->first();
      if ($nextImage) {
        $nextImage->update(['is_thumbnail' => true]);
      }
    }

    return response()->json([
      'success' => true,
      'message' => 'Room image deleted successfully'
    ]);
  }

  /**
   * Set image as thumbnail for room type
   */
  public function setThumbnail($id): JsonResponse
  {
    $roomImage = RoomImage::find($id);

    if (!$roomImage) {
      return response()->json([
        'success' => false,
        'message' => 'Room image not found'
      ], 404);
    }

    // Remove thumbnail status from other images of the same room type
    RoomImage::where('room_type_id', $roomImage->room_type_id)
      ->where('id', '!=', $id)
      ->update(['is_thumbnail' => false]);

    // Set this image as thumbnail
    $roomImage->update(['is_thumbnail' => true]);
    $roomImage->load('roomType');

    return response()->json([
      'success' => true,
      'message' => 'Image set as thumbnail successfully',
      'data' => $roomImage
    ]);
  }

  /**
   * Upload image and return the path
   */
  public function uploadImage(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      /** @var UploadedFile $image */
      $image = $request->file('image');

      // Generate unique filename
      $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

      // Store in public/images/rooms directory
      $path = $image->storeAs('images/rooms', $filename, 'public');

      // Return the full path that can be accessed via URL
      $fullPath = 'storage/' . $path;

      return response()->json([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'data' => [
          'path' => $fullPath,
          'filename' => $filename,
          'original_name' => $image->getClientOriginalName(),
          'size' => $image->getSize(),
          'mime_type' => $image->getMimeType()
        ]
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to upload image',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get all uploaded images (not linked to room types)
   */
  public function getAllImages(Request $request): JsonResponse
  {
    try {
      $limit = (int) $request->input('limit', 50);
      $page = (int) $request->input('page', 1);

      // Get all files from storage/images/rooms directory
      $files = Storage::disk('public')->files('images/rooms');

      // Convert to array with metadata
      $images = [];
      foreach ($files as $file) {
        $fullPath = 'storage/' . $file;
        $filename = basename($file);
        $size = Storage::disk('public')->size($file);
        $lastModified = Storage::disk('public')->lastModified($file);

        // Check if this image is used in room_images table
        $isUsed = RoomImage::where('path', $fullPath)->exists();

        $images[] = [
          'path' => $fullPath,
          'filename' => $filename,
          'size' => $size,
          'last_modified' => date('Y-m-d H:i:s', $lastModified),
          'is_used' => $isUsed,
          'url' => asset($fullPath)
        ];
      }

      // Sort by last modified (newest first)
      usort($images, function ($a, $b) {
        return strtotime($b['last_modified']) - strtotime($a['last_modified']);
      });

      // Apply search filter if provided
      if ($request->has('search') && $request->search) {
        $searchTerm = strtolower($request->search);
        $images = array_filter($images, function ($image) use ($searchTerm) {
          return strpos(strtolower($image['filename']), $searchTerm) !== false;
        });
      }

      // Apply pagination
      $total = count($images);
      $offset = ($page - 1) * $limit;
      $paginatedImages = array_slice($images, $offset, $limit);

      return response()->json([
        'success' => true,
        'message' => 'Images retrieved successfully',
        'data' => array_values($paginatedImages),
        'pagination' => [
          'total' => $total,
          'current_page' => $page,
          'limit' => $limit,
          'last_page' => ceil($total / $limit)
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve images',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Delete an image file from storage
   */
  public function deleteImageFile(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'path' => 'required|string'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      $imagePath = $request->input('path');

      // Remove 'storage/' prefix to get actual storage path
      $storagePath = str_replace('storage/', '', $imagePath);

      // Check if image is being used in room_images table
      $isUsed = RoomImage::where('path', $imagePath)->exists();

      if ($isUsed) {
        return response()->json([
          'success' => false,
          'message' => 'Cannot delete image. It is currently being used by room types.'
        ], 400);
      }

      // Check if file exists
      if (!Storage::disk('public')->exists($storagePath)) {
        return response()->json([
          'success' => false,
          'message' => 'Image file not found'
        ], 404);
      }

      // Delete the file
      Storage::disk('public')->delete($storagePath);

      return response()->json([
        'success' => true,
        'message' => 'Image deleted successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete image',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get images by room type
   */
  public function getByRoomType($roomTypeId): JsonResponse
  {
    // Verify room type exists
    $roomType = RoomType::find($roomTypeId);
    if (!$roomType) {
      return response()->json([
        'success' => false,
        'message' => 'Room type not found'
      ], 404);
    }

    $images = RoomImage::where('room_type_id', $roomTypeId)
      ->orderBy('is_thumbnail', 'desc')
      ->orderBy('id', 'asc')
      ->get();

    // Add full URL to each image
    $images = $images->map(function ($image) {
      $image->url = asset($image->path);
      return $image;
    });

    return response()->json([
      'success' => true,
      'message' => 'Room type images retrieved successfully',
      'data' => [
        'room_type' => $roomType,
        'images' => $images,
        'total_images' => $images->count(),
        'thumbnail_image' => $images->where('is_thumbnail', true)->first()
      ]
    ]);
  }
}
