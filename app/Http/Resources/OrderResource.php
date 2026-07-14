<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_code' => $this->order_code,
            'user_id' => $this->user_id,
            'address_id' => $this->address_id,
            'total_price' => $this->total_price,
            'order_status' => $this->order_status,
            'payment_proof' => $this->payment_proof,
            // Midtrans specific fields that will be added later
            'payment_status' => $this->payment_status ?? null,
            'payment_method' => $this->payment_method ?? null,
            'payment_token' => $this->payment_token ?? null,
            'paid_at' => $this->paid_at ?? null,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => $this->whenLoaded('items'),
            'delivery' => $this->whenLoaded('delivery', function() {
                return new DeliveryResource($this->delivery);
            }),
            'customer' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'address' => $this->whenLoaded('address'),
        ];
    }
}
