<?php

namespace App\Http\Controllers;

use App\Models\CarType;
use Illuminate\Http\Request;
use App\Http\Requests\CartypeRequest;
use App\app\Traits\ApiResponse;
class CartypeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $cartypes = Cartype::all()->map(function ($cartype) {
            return [
                'id' => $cartype->id,
                'type' => $cartype->type,
                'logo' => $cartype->getFirstMediaUrl('logos'),
            ];
        });

        return $this->successResponse('Car types retrieved successfully', $cartypes);
    }

    public function store(CartypeRequest $request)
    {
        $cartype = Cartype::create(['type' => $request->type]);

        if ($request->hasFile('logo')) {
            $cartype->addMedia($request->file('logo'))->toMediaCollection('logos');
        }

        $data = [
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ];

        return $this->successResponse('Car type created successfully!', $data, 201);
    }

    public function show(Cartype $cartype)
    {
        $data = [
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ];

        return $this->successResponse('Car type retrieved successfully', $data);
    }

    public function update(CartypeRequest $request, Cartype $cartype)
    {
        $cartype->update(['type' => $request->type]);

        if ($request->hasFile('logo')) {
            $cartype->clearMediaCollection('logos');
            $cartype->addMedia($request->file('logo'))->toMediaCollection('logos');
        }

        $data = [
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ];

        return $this->successResponse('Car type updated successfully!', $data);
    }

    public function destroy(Cartype $cartype)
    {
        $cartype->clearMediaCollection('logos');
        $cartype->delete();

        return $this->successResponse('Car type deleted successfully!');
    }
}
