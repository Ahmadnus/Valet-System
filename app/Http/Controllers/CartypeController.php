<?php

namespace App\Http\Controllers;

use App\Models\CarType;
use Illuminate\Http\Request;
use App\Http\Requests\CartypeRequest;

class CartypeController extends Controller
{
    public function index()
    {
        $cartypes = Cartype::all()->map(function ($cartype) {
            return [
                'id' => $cartype->id,
                'type' => $cartype->type,
                'logo' => $cartype->getFirstMediaUrl('logos'),
            ];
        });

        return response()->json($cartypes, 200);
    }

    public function store(CartypeRequest $request)
    {
        $cartype = Cartype::create(['type' => $request->type]);

        if ($request->hasFile('logo')) {
            $cartype->addMedia($request->file('logo'))->toMediaCollection('logos');
        }

        return response()->json([
            'message' => 'Car type created successfully!',
            'data' => [
                'id' => $cartype->id,
                'type' => $cartype->type,
                'logo' => $cartype->getFirstMediaUrl('logos'),
            ]
        ], 201);
    }

    public function show(Cartype $cartype)
    {
        return response()->json([
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ], 200);
    }

    public function update(CartypeRequest $request, Cartype $cartype)
    {
        $cartype->update(['type' => $request->type]);

        if ($request->hasFile('logo')) {
            $cartype->clearMediaCollection('logos'); // Remove old image
            $cartype->addMedia($request->file('logo'))->toMediaCollection('logos');
        }

        return response()->json([
            'message' => 'Car type updated successfully!',
            'data' => [
                'id' => $cartype->id,
                'type' => $cartype->type,
                'logo' => $cartype->getFirstMediaUrl('logos'),
            ]
        ], 200);
    }

    public function destroy(Cartype $cartype)
    {
        $cartype->clearMediaCollection('logos'); // Delete image
        $cartype->delete();

        return response()->json(['message' => 'Car type deleted successfully!'], 200);
    }
}
