<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
  /**
   * Display a listing of all booking orders
   */
  public function index(Request $request): JsonResponse
  {
    $limit = (int) $request->input('limit', 20);
    $page = (int) $request->input('page', 1);

    $query = BookingOrder::with(['user', 'roomType.images', 'room']);

    // Filter by status
    if ($request->has('status') && $request->status) {
      $query->where('status', $request->status);
    }

    // Filter by user
    if ($request->has('user_id') && $request->user_id) {
      $query->where('user_id', $request->user_id);
    }

    // Filter by room type
    if ($request->has('room_type_id') && $request->room_type_id) {
      $query->where('room_type_id', $request->room_type_id);
    }

    // Filter by room
    if ($request->has('room_id') && $request->room_id) {
      $query->where('room_id', $request->room_id);
    }

    // Filter by date range
    if ($request->has('from_date') && $request->from_date) {
      $query->where('check_in_date', '>=', $request->from_date);
    }

    if ($request->has('to_date') && $request->to_date) {
      $query->where('check_out_date', '<=', $request->to_date);
    }

    // Search by user name or phone
    if ($request->has('search') && $request->search) {
      $searchTerm = $request->search;
      $query->where(function ($q) use ($searchTerm) {
        $q->where('phone', 'like', '%' . $searchTerm . '%')
          ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
            $userQuery->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('email', 'like', '%' . $searchTerm . '%');
          });
      });
    }

    $total = $query->count();

    $bookings = $query
      ->orderBy('created_at', 'desc')
      ->skip(($page - 1) * $limit)
      ->take($limit)
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Bookings retrieved successfully',
      'data' => $bookings,
      'pagination' => [
        'total' => $total,
        'current_page' => $page,
        'limit' => $limit,
        'last_page' => ceil($total / $limit)
      ]
    ]);
  }

  /**
   * Display the specified booking order
   */
  public function show($id): JsonResponse
  {
    $booking = BookingOrder::with(['user', 'roomType.images', 'room'])->find($id);

    if (!$booking) {
      return response()->json([
        'success' => false,
        'message' => 'Booking not found'
      ], 404);
    }

    // Get available rooms for this booking (always return for admin management)
    $checkInDate = Carbon::parse($booking->check_in_date);
    $checkOutDate = Carbon::parse($booking->check_out_date);

    $availableRooms = Room::where('room_type_id', $booking->room_type_id)
      ->where('is_active', true)
      ->with('roomType')
      ->whereDoesntHave('bookingOrders', function ($query) use ($checkInDate, $checkOutDate, $booking) {
        $query->whereIn('status', ['pending', 'confirmed', 'checked-in', 'checked-out'])
          ->where('id', '!=', $booking->id)
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

    // Calculate additional info
    $checkInDate = Carbon::parse($booking->check_in_date);
    $checkOutDate = Carbon::parse($booking->check_out_date);
    $nights = $checkInDate->diffInDays($checkOutDate);

    return response()->json([
      'success' => true,
      'message' => 'Booking retrieved successfully',
      'data' => [
        'booking' => $booking,
        'nights' => $nights,
        'available_rooms' => $availableRooms
      ]
    ]);
  }



  /**
   * Update booking details (including room assignment/removal)
   */
  public function update(Request $request, $id): JsonResponse
  {
    $booking = BookingOrder::find($id);

    if (!$booking) {
      return response()->json([
        'success' => false,
        'message' => 'Booking not found'
      ], 404);
    }

    if (count($request->all()) == 0) {
      return response()->json([
        'success' => false,
        'message' => 'No data to update'
      ], 400);
    }

    $validator = Validator::make($request->all(), [
      'check_in_date' => 'nullable|date',
      'check_out_date' => 'nullable|date|after:check_in_date',
      'phone' => 'nullable|string|max:20',
      'adult' => 'nullable|integer|min:1|max:10',
      'children' => 'nullable|integer|min:0|max:10',
      'total_price' => 'nullable|numeric|min:0',
      'is_paid' => 'nullable|boolean',
      'status' => 'nullable|string|in:pending,confirmed,checked-in,checked-out,completed,cancelled',
      'room_id' => 'nullable|integer|exists:rooms,id'
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
      // Validate room assignment if provided
      if ($request->has('room_id')) {
        $newRoomId = $request->input('room_id');

        // If room_id is not null, validate the room
        if ($newRoomId !== null) {
          $room = Room::find($newRoomId);

          if (!$room) {
            return response()->json([
              'success' => false,
              'message' => 'Room not found'
            ], 404);
          }

          // Validate room belongs to the same room type
          if ($room->room_type_id !== $booking->room_type_id) {
            return response()->json([
              'success' => false,
              'message' => 'Room does not belong to the booked room type'
            ], 400);
          }

          // Check if room is active
          if (!$room->is_active) {
            return response()->json([
              'success' => false,
              'message' => 'Room is not active'
            ], 400);
          }
        }
      }

      // Validate room requirement for confirmed and checked-in status
      if ($request->has('status')) {
        $newStatus = $request->input('status');
        $roomId = $request->input('room_id', $booking->room_id);

        if (in_array($newStatus, ['confirmed', 'checked-in']) && !$roomId) {
          return response()->json([
            'success' => false,
            'message' => 'Room assignment is required for ' . $newStatus . ' status'
          ], 400);
        }
      }

      // Recalculate total price if dates changed and price not explicitly set
      if (($request->has('check_in_date') || $request->has('check_out_date')) && !$request->has('total_price')) {
        $checkInDate = $request->input('check_in_date', $booking->check_in_date);
        $checkOutDate = $request->input('check_out_date', $booking->check_out_date);
        $checkInCarbon = Carbon::parse($checkInDate);
        $checkOutCarbon = Carbon::parse($checkOutDate);
        $nights = $checkInCarbon->diffInDays($checkOutCarbon);
        $roomType = RoomType::find($booking->room_type_id);
        $request->merge(['total_price' => $nights * $roomType->price]);
      }

      // Update booking with all allowed fields
      $booking->update($request->only([
        'check_in_date',
        'check_out_date',
        'phone',
        'adult',
        'children',
        'total_price',
        'is_paid',
        'status',
        'room_id'
      ]));

      DB::commit();

      $booking->load(['user', 'roomType.images', 'room']);

      return response()->json([
        'success' => true,
        'message' => 'Booking updated successfully',
        'data' => $booking
      ]);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        'success' => false,
        'message' => 'Failed to update booking',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Delete booking order
   */
  public function destroy($id): JsonResponse
  {
    $booking = BookingOrder::find($id);

    if (!$booking) {
      return response()->json([
        'success' => false,
        'message' => 'Booking not found'
      ], 404);
    }

    DB::beginTransaction();

    try {
      // Delete booking
      $booking->delete();

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Booking deleted successfully'
      ]);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete booking',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get booking statistics
   */
  public function statistics(Request $request): JsonResponse
  {
    $dateFilter = [];
    if ($request->has('from_date') && $request->from_date) {
      $dateFilter[] = ['created_at', '>=', $request->from_date];
    }
    if ($request->has('to_date') && $request->to_date) {
      $dateFilter[] = ['created_at', '<=', $request->to_date];
    }

    $stats = [
      'total_bookings' => BookingOrder::where($dateFilter)->count(),
      'pending_bookings' => BookingOrder::where($dateFilter)->where('status', 'pending')->count(),
      'confirmed_bookings' => BookingOrder::where($dateFilter)->where('status', 'confirmed')->count(),
      'checked_in_bookings' => BookingOrder::where($dateFilter)->where('status', 'checked-in')->count(),
      'completed_bookings' => BookingOrder::where($dateFilter)->where('status', 'completed')->count(),
      'cancelled_bookings' => BookingOrder::where($dateFilter)->where('status', 'cancelled')->count(),
      'total_revenue' => BookingOrder::where($dateFilter)
        ->whereIn('status', ['confirmed', 'completed', 'checked-in', 'checked-out'])
        ->sum('total_price'),
      'paid_bookings' => BookingOrder::where($dateFilter)->where('is_paid', true)->count(),
      'unpaid_bookings' => BookingOrder::where($dateFilter)->where('is_paid', false)->count(),
    ];

    // Get bookings by room type
    $bookingsByRoomType = BookingOrder::where($dateFilter)
      ->with('roomType:id,name')
      ->select('room_type_id', DB::raw('count(*) as total'), DB::raw('sum(total_price) as revenue'))
      ->groupBy('room_type_id')
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Booking statistics retrieved successfully',
      'data' => [
        'overview' => $stats,
        'by_room_type' => $bookingsByRoomType
      ]
    ]);
  }

  /**
   * Get available rooms for a specific room type and date range
   */
  public function getAvailableRooms(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'room_type_id' => 'required|integer|exists:room_types,id',
      'check_in_date' => 'required|date',
      'check_out_date' => 'required|date|after:check_in_date',
      'exclude_booking_id' => 'nullable|integer|exists:booking_orders,id'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $checkInDate = Carbon::parse($request->check_in_date);
    $checkOutDate = Carbon::parse($request->check_out_date);
    $roomTypeId = $request->room_type_id;
    $excludeBookingId = $request->exclude_booking_id;

    $availableRooms = Room::where('room_type_id', $roomTypeId)
      ->where('is_active', true)
      ->whereDoesntHave('bookingOrders', function ($query) use ($checkInDate, $checkOutDate, $excludeBookingId) {
        $query->whereIn('status', ['pending', 'confirmed', 'checked-in', 'checked-out']);

        if ($excludeBookingId) {
          $query->where('id', '!=', $excludeBookingId);
        }

        $query->where(function ($q) use ($checkInDate, $checkOutDate) {
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
        'check_in_date' => $checkInDate->toDateString(),
        'check_out_date' => $checkOutDate->toDateString(),
        'available_rooms' => $availableRooms,
        'available_count' => $availableRooms->count()
      ]
    ]);
  }
}
