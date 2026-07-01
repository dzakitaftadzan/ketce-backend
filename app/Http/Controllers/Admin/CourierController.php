<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CourierController extends Controller
{
    // 1. List Kurir dengan Metrik Performa
    public function index()
    {
        $couriers = User::where('role', 'kurir')->get()->map(function ($courier) {
            $totalDeliveries = Delivery::where('courier_id', $courier->id)->count();
            $delivered = Delivery::where('courier_id', $courier->id)->where('status', 'delivered')->count();
            
            return [
                'id' => $courier->id,
                'name' => $courier->name,
                'email' => $courier->email,
                'phone' => $courier->phone,
                'vehicle_type' => $courier->vehicle_type,
                'is_active' => (bool)$courier->is_active,
                'stats' => [
                    'total_deliveries' => $totalDeliveries,
                    'success_rate' => $totalDeliveries > 0 ? round(($delivered / $totalDeliveries) * 100, 2) . '%' : '0%'
                ]
            ];
        });

        return response()->json(['data' => $couriers]);
    }

    // 2. Tambah Kurir Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'phone' => 'required',
            'vehicle_type' => 'required|in:E-BIKE,LIGHT-VAN',
        ]);

        $courier = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'kurir',
            'phone' => $validated['phone'],
            'vehicle_type' => $validated['vehicle_type'],
            'is_active' => true
        ]);

        return response()->json(['message' => 'Kurir berhasil ditambahkan', 'data' => $courier], 201);
    }

    // 3. Update Data Kurir
    public function update(Request $request, $id)
    {
        $courier = User::where('role', 'kurir')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|min:3',
            'phone' => 'sometimes',
            'vehicle_type' => 'sometimes|in:E-BIKE,LIGHT-VAN',
        ]);

        $courier->update($validated);
        return response()->json(['message' => 'Data kurir berhasil diperbarui', 'data' => $courier]);
    }

    // 4. Toggle Status Kurir
    public function toggle($id)
    {
        $courier = User::where('role', 'kurir')->findOrFail($id);
        
        // Cek pengiriman aktif sebelum nonaktifkan
        if ($courier->is_active) {
            $hasActiveDelivery = Delivery::where('courier_id', $id)
                ->whereIn('status', ['assigned', 'picked_up', 'delivering'])
                ->exists();

            if ($hasActiveDelivery) {
                return response()->json(['message' => 'Kurir masih memiliki pengiriman aktif'], 422);
            }
        }

        $courier->is_active = !$courier->is_active;
        $courier->save();

        return response()->json([
            'message' => 'Status kurir berhasil diubah', 
            'is_active' => (bool)$courier->is_active
        ]);
    }
}