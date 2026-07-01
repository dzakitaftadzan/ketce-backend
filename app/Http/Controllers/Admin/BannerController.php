<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Banner request:', ['all' => $request->all(), 'files' => $request->allFiles()]);
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
                'title' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer',
                'is_active' => 'nullable|boolean'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Failed:', $e->errors());
            throw $e;
        }

        $path = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'image' => $path,
            'title' => $request->title,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner berhasil ditambahkan',
            'data' => $banner
        ], 201);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        
        if (Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }
        
        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner berhasil dihapus'
        ]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:banners,id',
            'banners.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->banners as $b) {
            Banner::where('id', $b['id'])->update(['sort_order' => $b['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan banner berhasil diperbarui'
        ]);
    }
}
