<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryLog;

class DeliveryService
{
    public function updateStatus(\, \, \, \)
    {
        \->update(['status' => \]);

        if (\ == 'picked_up') {
            \->order->update(['order_status' => 'delivering']);
            \->update(['picked_up_at' => now()]);
        } elseif (\ == 'delivered') {
            \->order->update(['order_status' => 'delivered']);
            \->update(['delivered_at' => now()]);
        }

        DeliveryLog::create([
            'delivery_id' => \->id,
            'status' => \,
            'description' => \,
            'created_by' => \
        ]);

        return \;
    }
}
