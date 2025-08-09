<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
  /**
   * Display user's booking orders
   */
  public function index(Request $request): JsonResponse
  {
    $user = $request->user();
    $limit = (int) $request->input('limit', 10);
    $page = (int) $request->input('page', 1);

    $query = BookingOrder::where('user_id', $user->id)
      ->with(['roomType.images', 'room', 'review']);

    // Filter by status
    if ($request->has('status') && $request->status) {
      $query->where('status', $request->status);
    }

    // Filter by date range
    if ($request->has('from_date') && $request->from_date) {
      $query->where('check_in_date', '>=', $request->from_date);
    }

    if ($request->has('to_date') && $request->to_date) {
      $query->where('check_out_date', '<=', $request->to_date);
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
   * Store a new booking order
   */
  public function store(Request $request): JsonResponse
  {
    $user = $request->user();

    $validator = Validator::make($request->all(), [
      'room_type_id' => 'required|integer|exists:room_types,id',
      'check_in_date' => 'required|date|after_or_equal:today',
      'check_out_date' => 'required|date|after:check_in_date',
      'phone' => 'required|string|max:20',
      'adult' => 'required|integer|min:1|max:10',
      'children' => 'required|integer|min:0|max:10'
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
      $roomType = RoomType::find($request->room_type_id);
      $checkInDate = Carbon::parse($request->check_in_date);
      $checkOutDate = Carbon::parse($request->check_out_date);

      // Validate capacity
      if ($request->adult > $roomType->adult || $request->children > $roomType->children) {
        return response()->json([
          'success' => false,
          'message' => 'Room capacity exceeded. Maximum: ' . $roomType->adult . ' adults, ' . $roomType->children . ' children'
        ], 400);
      }

      // Check room availability
      $availableRooms = Room::where('room_type_id', $roomType->id)
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
        ->count();

      if ($availableRooms === 0) {
        return response()->json([
          'success' => false,
          'message' => 'No rooms available for the selected dates'
        ], 400);
      }

      // Calculate total price (number of nights * room price)
      $nights = $checkInDate->diffInDays($checkOutDate);
      $totalPrice = $nights * $roomType->price;

      // Create booking order
      $booking = BookingOrder::create([
        'user_id' => $user->id,
        'room_type_id' => $roomType->id,
        'room_id' => null, // Will be assigned by admin
        'status' => 'pending',
        'check_in_date' => $checkInDate,
        'check_out_date' => $checkOutDate,
        'phone' => $request->phone,
        'adult' => $request->adult,
        'children' => $request->children,
        'total_price' => $totalPrice,
        'is_paid' => false,
        'is_reviewed' => false
      ]);

      DB::commit();

      // Load relationships for response
      $booking->load(['roomType.images', 'user']);

      return response()->json([
        'success' => true,
        'message' => 'Booking created successfully',
        'data' => [
          'booking' => $booking,
          'nights' => $nights,
          'available_rooms' => $availableRooms
        ]
      ], 201);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        'success' => false,
        'message' => 'Failed to create booking',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Display the specified booking order
   */
  public function show(Request $request, $id): JsonResponse
  {
    $user = $request->user();

    $booking = BookingOrder::where('user_id', $user->id)
      ->where('id', $id)
      ->with(['roomType.images', 'room', 'user', 'review'])
      ->first();

    if (!$booking) {
      return response()->json([
        'success' => false,
        'message' => 'Booking not found or access denied'
      ], 404);
    }

    // Calculate additional info
    $checkInDate = Carbon::parse($booking->check_in_date);
    $checkOutDate = Carbon::parse($booking->check_out_date);
    $nights = $checkInDate->diffInDays($checkOutDate);
    $daysUntilCheckIn = now()->diffInDays($checkInDate, false);

    return response()->json([
      'success' => true,
      'message' => 'Booking retrieved successfully',
      'data' => [
        'booking' => $booking,
        'nights' => $nights,
        'days_until_checkin' => $daysUntilCheckIn,
        'can_cancel' => $this->canCancelBooking($booking),
        'can_review' => $this->canReviewBooking($booking)
      ]
    ]);
  }

  /**
   * Cancel a booking order
   */
  public function cancel(Request $request, $id): JsonResponse
  {
    $user = $request->user();

    $booking = BookingOrder::where('user_id', $user->id)
      ->where('id', $id)
      ->first();

    if (!$booking) {
      return response()->json([
        'success' => false,
        'message' => 'Booking not found or access denied'
      ], 404);
    }

    // Check if booking can be cancelled
    if (!$this->canCancelBooking($booking)) {
      return response()->json([
        'success' => false,
        'message' => 'Booking cannot be cancelled. Only pending bookings or bookings with at least 24 hours before check-in can be cancelled.'
      ], 400);
    }

    // Update booking status
    $booking->update(['status' => 'cancelled']);
    $booking->load(['roomType.images', 'room']);

    return response()->json([
      'success' => true,
      'message' => 'Booking cancelled successfully',
      'data' => $booking
    ]);
  }

  /**
   * Get user's booking statistics
   */
  public function statistics(Request $request): JsonResponse
  {
    $user = $request->user();

    $stats = [
      'total_bookings' => BookingOrder::where('user_id', $user->id)->count(),
      'pending_bookings' => BookingOrder::where('user_id', $user->id)->where('status', 'pending')->count(),
      'confirmed_bookings' => BookingOrder::where('user_id', $user->id)->where('status', 'confirmed')->count(),
      'completed_bookings' => BookingOrder::where('user_id', $user->id)->where('status', 'completed')->count(),
      'cancelled_bookings' => BookingOrder::where('user_id', $user->id)->where('status', 'cancelled')->count(),
      'total_spent' => BookingOrder::where('user_id', $user->id)
        ->whereIn('status', ['confirmed', 'completed', 'checked-in', 'checked-out'])
        ->sum('total_price'),
      'reviews_given' => BookingOrder::where('user_id', $user->id)
        ->where('is_reviewed', true)
        ->count()
    ];

    return response()->json([
      'success' => true,
      'message' => 'User booking statistics retrieved successfully',
      'data' => $stats
    ]);
  }

  /**
   * Check if a booking can be cancelled
   */
  private function canCancelBooking(BookingOrder $booking): bool
  {
    // Can only cancel pending bookings or confirmed bookings with at least 24 hours notice
    if ($booking->status === 'pending') {
      return true;
    }

    if ($booking->status === 'confirmed') {
      $checkInDate = Carbon::parse($booking->check_in_date);
      $hoursUntilCheckIn = now()->diffInHours($checkInDate, false);
      return $hoursUntilCheckIn >= 24;
    }

    return false;
  }

  /**
   * Check if a booking can be reviewed
   */
  private function canReviewBooking(BookingOrder $booking): bool
  {
    return $booking->status === 'completed' && !$booking->is_reviewed;
  }
}
