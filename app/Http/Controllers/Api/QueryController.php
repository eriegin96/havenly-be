<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Query;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QueryController extends Controller
{
  /**
   * Store a new query (public access)
   */
  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:50',
      'email' => 'required|email|max:255',
      'subject' => 'required|string|max:255',
      'message' => 'required|string|max:500'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      $query = Query::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'subject' => $request->input('subject'),
        'message' => $request->input('message'),
        'is_read' => false
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Query submitted successfully. We will get back to you soon.',
        'data' => [
          'id' => $query->id,
          'name' => $query->name,
          'email' => $query->email,
          'subject' => $query->subject,
          'created_at' => $query->created_at
        ]
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to submit query',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
