<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Services\CarService;
use App\Http\Requests\CarRequsets\StoreCarRequest;
use App\Http\Requests\StoreCarRequest as RequestsStoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use Illuminate\Support\Facades\Auth;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private CarService $carService)
    {

    }

    public function index()
    {
        $cars = Auth::user()->cars;
        return response()->json($cars);
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
    public function store(RequestsStoreCarRequest $request)
    {
        $car = $this->carService->store($request->validated());

        return response()->json([
            'message' => 'Car added successfully',
            'car' => $car
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Car $car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCarRequest $request, Car $car)
    {

        return $this->carService->update($request, $car);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        return $this->carService->delete($car);

    }
}
