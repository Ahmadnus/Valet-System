<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Services\CarService;
use App\Http\Requests\StoreCarRequest;

use App\Http\Requests\UpdateCarRequest;
use App\Http\Resources\Car\CarResource;
use Illuminate\Support\Facades\Auth;
use Exception;
class CarController extends Controller
{   protected $carService;

    public function __construct(CarService $carService)
    {
        $this->carService = $carService;
    }

    /**
     * Display a list of the user's cars.
     */
    public function index()
    {
        $cars = Auth::user()->cars;
        return CarResource::collection($cars);
    }

    /**
     * Store a newly created car.
     */
    public function store(StoreCarRequest $request)
    {
        try {
            $car = $this->carService->store($request->validated());
            return response()->json([
                'message' => 'Car added successfully',
                'car' => new CarResource($car),
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Display a specific car.
     */
    public function show(Car $car)
    {
        if ($car->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return new CarResource($car);
    }

    /**
     * Update the specified car.
     */
    public function update(UpdateCarRequest $request, Car $car)
    {
        try {
            $this->carService->update($request, $car);
            return response()->json([
                'message' => 'Car updated successfully',
                'car' => new CarResource($car),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified car.
     */
    public function destroy(Car $car)
    {
        try {
            $this->carService->delete($car);
            return response()->json(['message' => 'Car deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
