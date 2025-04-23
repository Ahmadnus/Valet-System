<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\app\Traits\ApiResponse;
use App\Models\Car;
use App\Services\CarService;
use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use App\Http\Resources\Car\CarResource;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\DB;

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
    $lang = app()->getLocale();
    $cars = $this->carService->getUserCars();

    return $this->successResponse(trans('Cars retrieved successfully'), CarResource::collection($cars));
}

/**
 * Store a newly created car.
 */
public function store(StoreCarRequest $request)
{
    $lang = app()->getLocale();
    try {
        $car = $this->carService->store($request->validated());
        return $this->successResponse(trans('Car added successfully'), new CarResource($car), 201);
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 400);
    }
}

/**
 * Display a specific car.
 */
public function show(Car $car)
{
    $lang = app()->getLocale();
    $car = $this->carService->show($car);
    return $this->successResponse(trans('Car fetched successfully'), new CarResource($car));
}

/**
 * Update the specified car.
 */
public function update(UpdateCarRequest $request, Car $car)
{
    $lang = app()->getLocale();

    try {
        $updatedCar = $this->carService->update($request, $car);

        return $this->successResponse(trans('Car updated successfully'), new CarResource($updatedCar));
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 400);
    }
}
/**
 * Remove the specified car.
 */public function destroy(Car $car)
{



    $car->delete();

    return $this->successResponse(trans('Car deleted successfully'));
}}