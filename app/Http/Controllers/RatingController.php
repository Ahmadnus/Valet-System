<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use App\Services\RatingService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Http\Request;
class RatingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private RatingService $ratingService)
    {

    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
{
    // Validate request data
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:255',
    ]);

    // Call the service function
    $rating = $this->ratingService->createRating($id, $request->all());

    return response()->json([
        'success' => 'Rating submitted successfully!',
        'data' => $rating
    ], 201);
}
    /**
     * Display the specified resource.
     */
    public function show(Rating $rating)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rating $rating)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRatingRequest $request, Rating $rating)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rating $rating)
    {
        //
    }
}
