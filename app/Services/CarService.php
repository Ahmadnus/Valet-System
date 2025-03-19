<?php

namespace App\Services;

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
        return Car::create([
            'user_id' => Auth::id(),
            'key_box' => $data['key_box'],
            'cartypeable_id' => $data['cartypeable_id'],
            'cartypeable_type' => \App\Models\CarType::class, // Store full class name
            'plate_number' => $data['plate_number'],
        ]);
    }
    public function delete(Car $car)
    {
        if ($car->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $car->delete();


    }
    public function update(UpdateCarRequest $request, Car $car)
    {
        if ($car->user_id !== Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $car->update($request->validated());


    }
}
