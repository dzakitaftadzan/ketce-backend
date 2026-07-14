<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
        'sort_order',
    ];

    /**
     * Append 'url' ke setiap response JSON agar frontend mendapatkan
     * URL lengkap (bukan hanya path relatif seperti "products/abc.jpg").
     * Contoh output: "http://localhost:8000/storage/products/abc.jpg"
     */
    protected $appends = ['url'];

    /**
     * Accessor: konversi path relatif ke full public URL.
     */
    public function getUrlAttribute(): string
    {
        return url(Storage::url($this->image));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}