<?php

namespace App\Http\Controllers;
use App\Services\CarTypeService;
use App\Http\Requests\CartypeRequest;
use App\Models\CarType;
use Illuminate\Http\Request;

use App\app\Traits\ApiResponse;
use App\Http\Resources\CarTypeResource;

class CartypeController extends Controller
{
    use ApiResponse;

    protected $carTypeService;

    public function __construct(CarTypeService $carTypeService)
    {
        $this->carTypeService = $carTypeService;
    }

    public function index()
    {
        $cartypes = $this->carTypeService->list();
        return $this->successResponse('Car types retrieved successfully', $cartypes);
    }

    public function store(CartypeRequest $request)
    {
        $data = $this->carTypeService->create($request->validated(), $request->file('logo'));
        return $this->successResponse('Car type created successfully!', $data, 201);
    }

    public function show(Cartype $cartype)
    {
        $data = $this->carTypeService->show($cartype);
        return $this->successResponse('Car type retrieved successfully', $data);
    }

    public function update(CartypeRequest $request, Cartype $cartype)
    {
        $data = $this->carTypeService->update($cartype, $request->validated(), $request->file('logo'));
        return $this->successResponse('Car type updated successfully!', $data);
    }

    public function destroy(Cartype $cartype)
    {
        $this->carTypeService->delete($cartype);
        return $this->successResponse('Car type deleted successfully!');
    }
}
