<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeWaiverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'request_id' => $this->request_id,
        'user_id' => $this->user_id,
        'status' => $this->status,
        'status_text' => $this->getTranslatedStatus(), // تمّت الإضافة هنا
        'approved_by' => $this->approved_by,
        'created_at' => $this->created_at?->toDateTimeString(),
        'updated_at' => $this->updated_at?->toDateTimeString(),
    ];
}
protected function getTranslatedStatus(): string
{
    $lang = app()->getLocale();

    return match ($this->status) {
        'approved' => $lang == 'ar' ? 'تمت الموافقة' : 'Approved',
        'rejected'   => $lang == 'ar' ? 'مرفوض' : 'Rejected',
        default    => $this->status,
    };
}
}
