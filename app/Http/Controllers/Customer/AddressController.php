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
            'recipient_name' => 'nullable|string|max:150',
            'phone_number' => 'nullable|string|max:20',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'komerce_destination_id' => 'nullable',
            'label' => 'nullable|string|max:50',
            'is_primary' => 'boolean',
        ]);

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            Address::where('user_id', auth()->id())->update(['is_primary' => false]);
        }

        // Jika ini alamat pertama, jadikan utama secara otomatis
        $addressCount = Address::where('user_id', auth()->id())->count();
        if ($addressCount === 0) {
            $isPrimary = true;
        }

        $address = Address::create([
            'user_id' => auth()->id(),
            'address_line' => $validated['address_line'],
            'city' => $validated['city'],
            'recipient_name' => $validated['recipient_name'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'province' => $validated['province'] ?? null,
            'district' => $validated['district'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'komerce_destination_id' => $validated['komerce_destination_id'] ?? null,
            'label' => $validated['label'] ?? 'Rumah',
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $address,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'recipient_name' => 'nullable|string|max:150',
            'phone_number' => 'nullable|string|max:20',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'komerce_destination_id' => 'nullable',
            'label' => 'nullable|string|max:50',
            'is_primary' => 'boolean',
        ]);

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary && !$address->is_primary) {
            Address::where('user_id', auth()->id())->update(['is_primary' => false]);
        }

        $address->update([
            'address_line' => $validated['address_line'],
            'city' => $validated['city'],
            'recipient_name' => $validated['recipient_name'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'province' => $validated['province'] ?? null,
            'district' => $validated['district'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'komerce_destination_id' => $validated['komerce_destination_id'] ?? null,
            'label' => $validated['label'] ?? 'Rumah',
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui.',
            'data' => $address,
        ]);
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