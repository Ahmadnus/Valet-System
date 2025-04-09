<?php

namespace App\Services;

use App\Http\Requests\StoreCarRequest;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateCarRequest;

class CarService
{
    /**
     * Create a new class instance.
     */
    public function store(array $data)
    {
        $car = Car::create([
            'user_id' => Auth::id(),
            'cartypeable_id' => $data['cartypeable_id'],
            'cartypeable_type' => \App\Models\CarType::class,
            'plate_number' => $data['plate_number'],
        ]);

        if (isset($data['image'])) {
            $car->addMedia($data['image'])->toMediaCollection('car_images');
        }

        return $car;
    }
    public function delete(Car $car)
{
    if ($car->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $car->clearMediaCollection('image');
    $car->delete();
}
    public function update(UpdateCarRequest $request, Car $car)
{
    if ($car->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $car->update($request->validated());

    if ($request->hasFile('image')) {
        $car->clearMediaCollection('car_images');
        $car->addMedia($request->file('image'))->toMediaCollection('car_images');
    }

    return $car;
}
    public function index()
    {
        return Car::where('user_id', Auth::id())->get();
    }

    /**
     * Show a specific car (only if belongs to current user).
     */
    public function show(Car $car)
    {
        if ($car->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $car->image_url = $car->getFirstMediaUrl('car_images');

        return $car;
    }
}
