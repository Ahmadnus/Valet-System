<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Models\Car;
use App\Services\CarService;
use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use App\Http\Resources\Car\CarResource;
use Illuminate\Support\Facades\Auth;
use Exception;

class CarController extends Controller
{
    use ApiResponse;

    protected $carService;

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
        return $this->successResponse('Cars retrieved successfully', CarResource::collection($cars));
    }

    /**
     * Store a newly created car.
     */
    public function store(StoreCarRequest $request)
    {
        try {
            $car = $this->carService->store($request->validated());
            return $this->successResponse('Car added successfully', new CarResource($car), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Display a specific car.
     */


    /**
     * Update the specified car.
     */
    public function update(UpdateCarRequest $request, Car $car)
    {
        try {
            $this->carService->update($request, $car);
            return $this->successResponse('Car updated successfully', new CarResource($car));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Remove the specified car.
     */
    public function destroy(Car $car)
    {
        try {
            $this->carService->delete($car);
            return $this->successResponse('Car deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}