<?php

namespace App\Services;

use App\Http\Requests\StoreCarRequest;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateCarRequest;
use App\app\Traits\OwnershipChecker;
class CarService
{
    use OwnershipChecker;
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
        $this->ensureUserOwnsModel($car);

        $car->delete();


    }
    public function update(UpdateCarRequest $request, Car $car)
{
    $this->ensureUserOwnsModel($car);

    $car->update($request->validated());

    if ($request->hasFile('image')) {
        $car->clearMediaCollection('car_images');
        $car->addMedia($request->file('image'))->toMediaCollection('car_images');
    }

    return $car;
}
public function getUserCars()
{
    $cars = Auth::user()->cars;
    $cars->load('cartypeable');

    return $cars;
}
    /**
     * Show a specific car (only if belongs to current user).
     */
    public function show(Car $car)
    {
        $this->ensureUserOwnsModel($car);

        $car->image_url = $car->getFirstMediaUrl('car_images');

        return $car;
    }
}
