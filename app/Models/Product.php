<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'price',
        'stock',
        'sizes',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'sizes' => 'array',
    ];

    /**
     * Override stock attribute: always calculate from sizes.
     * Jika tidak ada ukuran, stok akan di set ke 0.
     */
    public function getStockAttribute($value)
    {
        if (is_array($this->sizes) && count($this->sizes) > 0) {
            return (int) array_sum($this->sizes);
        }
        return 0;
    }

    /**
     * Append image_url ke setiap response JSON sehingga frontend
     * bisa langsung pakai product.image_url tanpa logika tambahan.
     */
    protected $appends = ['image_url'];

    /**
     * Gunakan slug sebagai route key agar GET /products/{slug} bisa
     * mengambil produk berdasarkan slug, bukan ID.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Accessor: ambil URL gambar pertama dari relasi images.
     * Mengembalikan null jika belum ada gambar.
     */
    public function getImageUrlAttribute(): ?string
    {
        // Menggunakan property (collection yang sudah di-load) alih-alih method query
        $firstImage = $this->images->first();
        if (!$firstImage) return null;
        return url(Storage::url($firstImage->image));
    }

    /**
     * Auto-generate slug dari nama jika slug belum diisi.
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // ─── Relasi ────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
}