<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Query;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QueryController extends Controller
{
  /**
   * Display a listing of queries
   */
  public function index(Request $request): JsonResponse
  {
    $limit = (int) $request->input('limit', 20);
    $page = (int) $request->input('page', 1);

    $query = Query::query();

    // Filter by read status
    if ($request->has('is_read')) {
      $isRead = $request->is_read === 'true' ? true : false;
      $query->where('is_read', $isRead);
    }

    // Search by name, email, or subject
    if ($request->has('search') && $request->search) {
      $searchTerm = $request->search;
      $query->where(function ($q) use ($searchTerm) {
        $q->where('name', 'like', '%' . $searchTerm . '%')
          ->orWhere('email', 'like', '%' . $searchTerm . '%')
          ->orWhere('subject', 'like', '%' . $searchTerm . '%');
      });
    }

    // Filter by date range
    if ($request->has('from_date') && $request->from_date) {
      $query->where('created_at', '>=', $request->from_date);
    }

    if ($request->has('to_date') && $request->to_date) {
      $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
    }

    $total = $query->count();

    $queries = $query
      ->orderBy('is_read', 'asc') // Unread first
      ->orderBy('created_at', 'desc')
      ->skip(($page - 1) * $limit)
      ->take($limit)
      ->get();

    // Get statistics
    $stats = [
      'total_queries' => Query::count(),
      'unread_queries' => Query::where('is_read', false)->count(),
      'read_queries' => Query::where('is_read', true)->count(),
      'today_queries' => Query::whereDate('created_at', today())->count()
    ];

    return response()->json([
      'success' => true,
      'message' => 'Queries retrieved successfully',
      'data' => $queries,
      'statistics' => $stats,
      'pagination' => [
        'total' => $total,
        'current_page' => $page,
        'limit' => $limit,
        'last_page' => ceil($total / $limit)
      ]
    ]);
  }

  /**
   * Display the specified query
   */
  public function show($id): JsonResponse
  {
    $query = Query::find($id);

    if (!$query) {
      return response()->json([
        'success' => false,
        'message' => 'Query not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Query retrieved successfully',
      'data' => $query
    ]);
  }

  /**
   * Update query read status
   */
  public function updateReadStatus(Request $request, $id): JsonResponse
  {
    $query = Query::find($id);

    if (!$query) {
      return response()->json([
        'success' => false,
        'message' => 'Query not found'
      ], 404);
    }

    $validator = Validator::make($request->all(), [
      'is_read' => 'required|boolean'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $query->update(['is_read' => $request->is_read]);

    return response()->json([
      'success' => true,
      'message' => 'Query status updated successfully',
      'data' => $query
    ]);
  }

  /**
   * Update read status of multiple queries
   */
  public function updateReadStatusMultiple(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'query_ids' => 'required|array',
      'query_ids.*' => 'integer|exists:queries,id',
      'is_read' => 'required|boolean'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $updatedCount = Query::whereIn('id', $request->query_ids)
      ->update(['is_read' => $request->is_read]);

    return response()->json([
      'success' => true,
      'message' => "{$updatedCount} queries marked as read successfully",
      'updated_count' => $updatedCount
    ]);
  }

  /**
   * Delete query
   */
  public function destroy($id): JsonResponse
  {
    $query = Query::find($id);

    if (!$query) {
      return response()->json([
        'success' => false,
        'message' => 'Query not found'
      ], 404);
    }

    $query->delete();

    return response()->json([
      'success' => true,
      'message' => 'Query deleted successfully'
    ]);
  }

  /**
   * Delete multiple queries
   */
  public function destroyMultiple(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'query_ids' => 'required|array',
      'query_ids.*' => 'integer|exists:queries,id'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    $deletedCount = Query::whereIn('id', $request->query_ids)->delete();

    return response()->json([
      'success' => true,
      'message' => "{$deletedCount} queries deleted successfully",
      'deleted_count' => $deletedCount
    ]);
  }
}
