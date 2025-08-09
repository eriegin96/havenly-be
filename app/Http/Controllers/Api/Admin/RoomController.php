<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
  /**
   * Display a listing of rooms
   */
  public function index(Request $request): JsonResponse
  {
    $limit = (int) $request->input('limit', 20);
    $page = (int) $request->input('page', 1);

    $query = Room::with('roomType');

    // Filter by room type
    if ($request->has('room_type_id') && $request->room_type_id) {
      $query->where('room_type_id', $request->room_type_id);
    }

    // Filter by active status
    if ($request->has('is_active')) {
      $query->where('is_active', $request->is_active === 'true' ? true : false);
    }

    // Search by room number
    if ($request->has('search') && $request->search) {
      $query->where('room_number', 'like', '%' . $request->search . '%');
    }

    // Filter by availability (rooms without active bookings)
    if ($request->has('available') && $request->available === 'true') {
      $query->whereDoesntHave('bookingOrders', function ($q) {
        $q->whereIn('status', ['pending', 'confirmed', 'checked-in']);
      });
    }

    $total = $query->count();

    $rooms = $query
      ->orderBy('room_type_id', 'asc')
      ->orderBy('room_number', 'asc')
      ->skip(($page - 1) * $limit)
      ->take($limit)
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Rooms retrieved successfully',
      'data' => $rooms,
      'pagination' => [
        'total' => $total,
        'current_page' => $page,
        'limit' => $limit,
        'last_page' => ceil($total / $limit)
      ]
    ]);
  }

  /**
   * Store a newly created room
   */
  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'room_type_id' => 'required|integer|exists:room_types,id',
      'room_number' => 'required|string|max:20|unique:rooms,room_number',
      'is_active' => 'nullable|boolean'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $room = Room::create([
      'room_type_id' => $request->input('room_type_id'),
      'room_number' => $request->input('room_number'),
      'is_active' => $request->input('is_active', true)
    ]);

    // Load room type relationship
    $room->load('roomType');

    return response()->json([
      'success' => true,
      'message' => 'Room created successfully',
      'data' => $room
    ], 201);
  }

  /**
   * Display the specified room
   */
  public function show($id): JsonResponse
  {
    $room = Room::with(['roomType', 'bookingOrders' => function ($query) {
      $query->orderBy('created_at', 'desc')->take(10);
    }])->find($id);

    if (!$room) {
      return response()->json([
        'success' => false,
        'message' => 'Room not found'
      ], 404);
    }

    // Get room statistics
    $stats = [
      'total_bookings' => $room->bookingOrders()->count(),
      'active_bookings' => $room->bookingOrders()
        ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
        ->count(),
      'completed_bookings' => $room->bookingOrders()
        ->where('status', 'completed')
        ->count(),
      'cancelled_bookings' => $room->bookingOrders()
        ->where('status', 'cancelled')
        ->count(),
    ];

    return response()->json([
      'success' => true,
      'message' => 'Room retrieved successfully',
      'data' => [
        'room' => $room,
        'statistics' => $stats
      ]
    ]);
  }

  /**
   * Update the specified room
   */
  public function update(Request $request, $id): JsonResponse
  {
    $room = Room::find($id);

    if (!$room) {
      return response()->json([
        'success' => false,
        'message' => 'Room not found'
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
      'room_number' => 'nullable|string|max:20|unique:rooms,room_number,' . $id,
      'is_active' => 'nullable|boolean'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    // Check if room has active bookings before changing room type or deactivating
    if ($request->has('room_type_id') && $request->room_type_id != $room->room_type_id) {
      $activeBookings = $room->bookingOrders()
        ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
        ->count();

      if ($activeBookings > 0) {
        return response()->json([
          'success' => false,
          'message' => 'Cannot change room type. Room has active bookings.'
        ], 400);
      }
    }

    if ($request->has('is_active') && !$request->is_active) {
      $activeBookings = $room->bookingOrders()
        ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
        ->count();

      if ($activeBookings > 0) {
        return response()->json([
          'success' => false,
          'message' => 'Cannot deactivate room. Room has active bookings.'
        ], 400);
      }
    }

    // Update room
    $room->update([
      'room_type_id' => $request->input('room_type_id', $room->room_type_id),
      'room_number' => $request->input('room_number', $room->room_number),
      'is_active' => $request->input('is_active', $room->is_active)
    ]);

    // Load room type relationship
    $room->load('roomType');

    return response()->json([
      'success' => true,
      'message' => 'Room updated successfully',
      'data' => $room
    ]);
  }

  /**
   * Remove the specified room
   */
  public function destroy($id): JsonResponse
  {
    $room = Room::with('bookingOrders')->find($id);

    if (!$room) {
      return response()->json([
        'success' => false,
        'message' => 'Room not found'
      ], 404);
    }

    // Check if room has any bookings (active or completed)
    $totalBookings = $room->bookingOrders()->count();

    if ($totalBookings > 0) {
      return response()->json([
        'success' => false,
        'message' => 'Cannot delete room. Room has booking history. Consider deactivating instead.'
      ], 400);
    }

    // Delete the room
    $room->delete();

    return response()->json([
      'success' => true,
      'message' => 'Room deleted successfully'
    ]);
  }

  /**
   * Get rooms by room type
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

    $rooms = Room::where('room_type_id', $roomTypeId)
      ->orderBy('room_number', 'asc')
      ->get();

    // Get statistics
    $stats = [
      'total_rooms' => $rooms->count(),
      'active_rooms' => $rooms->where('is_active', true)->count(),
      'inactive_rooms' => $rooms->where('is_active', false)->count(),
    ];

    return response()->json([
      'success' => true,
      'message' => 'Room type rooms retrieved successfully',
      'data' => [
        'room_type' => $roomType,
        'rooms' => $rooms,
        'statistics' => $stats
      ]
    ]);
  }

  /**
   * Get available rooms by room type and date range
   */
  public function getAvailableRooms(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'room_type_id' => 'required|integer|exists:room_types,id',
      'check_in_date' => 'required|date|after_or_equal:today',
      'check_out_date' => 'required|date|after:check_in_date'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $checkInDate = $request->check_in_date;
    $checkOutDate = $request->check_out_date;
    $roomTypeId = $request->room_type_id;

    // Get available rooms (not overlapping with existing bookings)
    $availableRooms = Room::where('room_type_id', $roomTypeId)
      ->where('is_active', true)
      ->whereDoesntHave('bookingOrders', function ($query) use ($checkInDate, $checkOutDate) {
        $query->whereIn('status', ['pending', 'confirmed', 'checked-in'])
          ->where(function ($q) use ($checkInDate, $checkOutDate) {
            $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
              ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
              ->orWhere(function ($q2) use ($checkInDate, $checkOutDate) {
                $q2->where('check_in_date', '<=', $checkInDate)
                  ->where('check_out_date', '>=', $checkOutDate);
              });
          });
      })
      ->orderBy('room_number', 'asc')
      ->get();

    $roomType = RoomType::find($roomTypeId);

    return response()->json([
      'success' => true,
      'message' => 'Available rooms retrieved successfully',
      'data' => [
        'room_type' => $roomType,
        'check_in_date' => $checkInDate,
        'check_out_date' => $checkOutDate,
        'available_rooms' => $availableRooms,
        'available_count' => $availableRooms->count()
      ]
    ]);
  }
}
