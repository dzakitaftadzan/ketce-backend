<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
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
            'order_id' => $this->order_id,
            'courier_id' => $this->courier_id,
            'status' => $this->status,
            'delivery_code' => $this->delivery_code,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'failed_at' => $this->failed_at,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'order' => $this->whenLoaded('order'),
            'courier' => $this->whenLoaded('courier', function() {
                return [
                    'id' => $this->courier->id,
                    'name' => $this->courier->name,
                    'phone' => $this->courier->phone ?? null,
                ];
            }),
        ];
    }
}
