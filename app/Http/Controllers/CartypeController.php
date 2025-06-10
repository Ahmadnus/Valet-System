<?php

namespace App\Http\Controllers;
use App\Services\CarTypeService;
use App\Http\Requests\CartypeRequest;
use App\Http\Requests\UpdateCartypeRequest;
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
        $lang = app()->getLocale();
        $this->carTypeService = $carTypeService;
    }

    public function index()
    {
        $lang = app()->getLocale();
        $cartypes = $this->carTypeService->list();
        return $this->successResponse('Car types retrieved successfully', $cartypes);
    }

    public function store(CartypeRequest $request)
    {
        $lang = app()->getLocale();
        $data = $this->carTypeService->create($request->validated(), $request->file('logo'));
        return $this->successResponse(trans('Car type created successfully!'), $data, 201);
    }

    public function show(Cartype $cartype)
    {
        $lang = app()->getLocale();
        $data = $this->carTypeService->show($cartype);
        return $this->successResponse(trans('Car type retrieved successfully'), $data);
    }

    public function update(UpdateCartypeRequest $request, Cartype $cartype)
    {
        $lang = app()->getLocale();
        $data = $this->carTypeService->update($cartype, $request->validated(), $request->file('logo'));
        return $this->successResponse(trans('Car type updated successfully!'), $data);
    }

    public function destroy(Cartype $cartype)
    {
        $lang = app()->getLocale();
        $this->carTypeService->delete($cartype);
        return $this->successResponse(trans('Car type deleted successfully!'));
    }
}
