<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Address;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah.'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.'
        ]);
    }

    public function setPrimaryAddress($id)
    {
        $userId = auth()->id();
        
        $address = Address::where('id', $id)->where('user_id', $userId)->first();
        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Alamat tidak ditemukan'], 404);
        }

        Address::where('user_id', $userId)->update(['is_primary' => false]);
        $address->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil diubah.'
        ]);
    }
}
