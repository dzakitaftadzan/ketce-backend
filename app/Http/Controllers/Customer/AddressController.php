<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Menampilkan semua alamat milik user.
     */
    public function index()
    {
        $addresses = Address::where('user_id', auth()->id())->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Menambah alamat baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
        ]);

        $address = Address::create([
            'user_id' => auth()->id(),
            'address_line' => $validated['address_line'],
            'city' => $validated['city'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $address,
        ], 201);
    }

    /**
     * Menghapus alamat.
     */
    public function destroy($id)
    {
        $address = Address::where('user_id', auth()->id())->findOrFail($id);

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }
}