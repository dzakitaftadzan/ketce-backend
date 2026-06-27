<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ubah menjadi true agar request ini diizinkan jalan
    }

    public function rules(): bool|array
    {
        return [
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8|confirmed', // Wajib ada input password_confirmation
            'phone_number' => 'nullable|string|max:20',
        ];
    }

    /**
     * Kustomisasi response jika validasi gagal agar formatnya JSON standar kita
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422));
    }
}