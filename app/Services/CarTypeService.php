<?php

namespace App\Services;
use App\Models\Cartype;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
class CarTypeService
{
    public function list()
    {
        return Cartype::all()->map(fn($cartype) => [
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ]);
    }

    public function create(array $data, ?UploadedFile $logo = null): array
    {
        $cartype = Cartype::create([
            'en' => ['type' => $data['type']['en']],
            'ar' => ['type' => $data['type']['ar']],
        ]);

        if ($logo) {
            $cartype->addMedia($logo)->toMediaCollection('logos');
        }

        return [
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ];
    }

    public function show(Cartype $cartype): array
    {
        return [
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ];
    }

    public function update(Cartype $cartype, array $data, ?UploadedFile $logo = null): array
    {
        $cartype->update([
            'en' => ['type' => $data['type']['en']],
            'ar' => ['type' => $data['type']['ar']],
        ]);

        if ($logo) {
            $cartype->clearMediaCollection('logos');
            $cartype->addMedia($logo)->toMediaCollection('logos');
        }

        return [
            'id' => $cartype->id,
            'type' => $cartype->type,
            'logo' => $cartype->getFirstMediaUrl('logos'),
        ];
    }

    public function delete(Cartype $cartype): void
    {
        $cartype->clearMediaCollection('logos');
        $cartype->delete();
    }
}
