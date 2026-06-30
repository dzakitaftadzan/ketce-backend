<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index()
    {
        $couriers = User::where('role', 'kurir')->get();
        return response()->json(['data' => $couriers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone' => 'required',
            'vehicle_type' => 'required|in:E-BIKE,LIGHT-VAN',
        ]);

        $courier = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'kurir',
            'phone' => $validated['phone'],
            'vehicle_type' => $validated['vehicle_type'],
            'is_active' => true
        ]);

        return response()->json(['message' => 'Kurir berhasil ditambahkan', 'data' => $courier], 201);
    }

    public function toggle($id)
    {
        $courier = User::where('role', 'kurir')->findOrFail($id);
        
        $hasActiveDelivery = Delivery::where('courier_id', $id)
            ->whereIn('status', ['assigned', 'picked_up', 'delivering'])
            ->exists();

        if ($hasActiveDelivery) {
            return response()->json(['message' => 'Kurir masih memiliki pengiriman aktif'], 422);
        }

        $courier->is_active = !$courier->is_active;
        $courier->save();

        return response()->json(['message' => 'Status kurir berhasil diubah', 'is_active' => $courier->is_active]);
    }
}