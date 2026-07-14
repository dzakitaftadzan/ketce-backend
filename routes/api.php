<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\TrackingController;
use App\Http\Controllers\Customer\ProfileController;

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;

use App\Http\Controllers\Courier\DeliveryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Customer\BannerController as CustomerBannerController;

// ─── Auth (Public) ────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// ─── Public: Katalog Produk ───────────────────────────────────────────────────

/**
 * GET /api/products
 * Parameter opsional:
 *   - featured (bool)   : hanya ambil N produk terbaru (untuk homepage)
 *   - limit    (int)    : jumlah produk (default 12, max 50)
 *   - search   (string) : cari berdasarkan nama produk
 *   - category (string) : filter berdasarkan slug kategori
 *   - sort     (string) : terbaru | harga_rendah | harga_tinggi | nama
 *   - min_price (int)   : harga minimum
 *   - max_price (int)   : harga maksimum
 *   - page     (int)    : halaman (default 1)
 */
Route::get('/products', function (\Illuminate\Http\Request $request) {
    $query = \App\Models\Product::with('images')->withCount('orderItems');

    // Filter: featured (homepage) — ambil N produk terbaru saja
    if ($request->has('featured')) {
        $limit = min((int) $request->query('limit', 8), 50);
        return response()->json([
            'success' => true,
            'data'    => $query->latest()->limit($limit)->get(),
        ]);
    }

    // Filter: search by name or sku
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('sku', 'like', '%' . $searchTerm . '%');
        });
    }

    // Filter: category slug
    if ($request->filled('category')) {
        $query->whereHas('category', function ($q) use ($request) {
            $q->where('slug', $request->category);
        });
    }

    // Filter: price range
    if ($request->filled('min_price')) {
        $query->where('price', '>=', (int) $request->min_price);
    }
    if ($request->filled('max_price')) {
        $query->where('price', '<=', (int) $request->max_price);
    }

    // Sort
    match ($request->query('sort', 'terbaru')) {
        'harga_rendah' => $query->orderBy('price', 'asc'),
        'harga_tinggi' => $query->orderBy('price', 'desc'),
        'nama'         => $query->orderBy('name', 'asc'),
        default        => $query->latest(),
    };

    // Paginate
    $perPage = min((int) $request->query('limit', 12), 50);
    $paginated = $query->paginate($perPage);

    return response()->json([
        'success'   => true,
        'data'      => $paginated->items(),
        'total'     => $paginated->total(),
        'last_page' => $paginated->lastPage(),
        'page'      => $paginated->currentPage(),
    ]);
});

/**
 * GET /api/products/{slug}
 * Detail satu produk berdasarkan slug (public, tidak perlu login).
 */
Route::get('/products/{slug}', function (string $slug) {
    $product = \App\Models\Product::with('images', 'category')
        ->where('slug', $slug)
        ->first();

    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data'    => $product,
    ]);
});

/**
 * GET /api/categories
 */
Route::get('/categories', function () {
    return response()->json([
        'success' => true,
        'data'    => \App\Models\Category::all(),
    ]);
});

/**
 * GET /api/banners
 */
Route::get('/banners', [CustomerBannerController::class, 'index']);


// ─── Protected Routes (Butuh Login) ──────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Profil user yang sedang login
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return response()->json($request->user());
    });
    
    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::put('/', [ProfileController::class, 'updateProfile']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
    });
    
    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
        
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{product_id}', [WishlistController::class, 'toggle']);

    Route::post('/logout', [AuthController::class, 'logout']);

    // ══════════════════════════════════════════════════════════════════════════
    // CUSTOMER (dan admin bisa mengakses)
    // ══════════════════════════════════════════════════════════════════════════
    Route::middleware('checkrole:customer,admin')->group(function () {
        // Komerce (Ongkir)
        Route::prefix('komerce')->group(function () {
            Route::get('/destination/search', [\App\Http\Controllers\Customer\KomerceController::class, 'searchDestination']);
            Route::post('/cost', [\App\Http\Controllers\Customer\KomerceController::class, 'cost']);
        });

        // Alamat
        Route::prefix('addresses')->group(function () {
            Route::get('/',      [AddressController::class, 'index']);
            Route::post('/',     [AddressController::class, 'store']);
            Route::put('/{id}',  [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
            Route::put('/{id}/primary', [ProfileController::class, 'setPrimaryAddress']);
        });

        // Keranjang belanja
        Route::prefix('cart')->group(function () {
            Route::get('/',       [CartController::class, 'index']);
            Route::post('/',      [CartController::class, 'store']);
            Route::put('/{id}',   [CartController::class, 'update']);
            Route::delete('/{id}',[CartController::class, 'destroy']);
        });

        // Pesanan customer
        Route::prefix('orders')->group(function () {
            Route::post('/',             [CustomerOrderController::class, 'store']);
            Route::get('/',              [CustomerOrderController::class, 'index']);
            Route::get('/{code}',        [CustomerOrderController::class, 'show']);
            Route::delete('/{code}',     [CustomerOrderController::class, 'cancel']);
            Route::get('/{code}/tracking', [TrackingController::class, 'show']);
            
            // Placeholder Midtrans
            Route::post('/{code}/pay',   [\App\Http\Controllers\Customer\PaymentController::class, 'createPayment']);
        });
    });
    
    // Webhook Midtrans (Bisa dipindah ke luar middleware jika Midtrans tidak mengirim token Bearer)
    Route::post('/payment/notification', [\App\Http\Controllers\Customer\PaymentController::class, 'notificationHandler'])->withoutMiddleware('auth:sanctum');

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN
    // ══════════════════════════════════════════════════════════════════════════
    Route::middleware('checkrole:admin')->prefix('admin')->group(function () {

        // Produk CRUD
        Route::apiResource('products', ProductController::class);
        Route::delete('/products/images/{imageId}', [ProductController::class, 'destroyImage']);

        // Kategori CRUD
        Route::apiResource('categories', CategoryController::class);
        Route::delete('/categories/{id}/image', [CategoryController::class, 'destroyImage']);

        // Manajemen Pesanan
        Route::get('/orders',                 [AdminOrderController::class, 'index']);
        Route::post('/orders/{id}/confirm',   [AdminOrderController::class, 'confirmPayment']);
        Route::patch('/orders/{id}/pack',     [AdminOrderController::class, 'pack']);
        Route::patch('/orders/{id}/assign',   [AdminOrderController::class, 'assignCourier']);
        Route::delete('/orders/{id}',         [AdminOrderController::class, 'cancelOrder']);
        Route::get('/stats',                  [AdminOrderController::class, 'stats']);

        // Banners
        Route::get('/banners', [AdminBannerController::class, 'index']);
        Route::post('/banners', [AdminBannerController::class, 'store']);
        Route::delete('/banners/{id}', [AdminBannerController::class, 'destroy']);
        Route::post('/banners/reorder', [AdminBannerController::class, 'updateOrder']);

        // Manajemen Kurir
        Route::apiResource('couriers', CourierController::class);
        Route::patch('/couriers/{id}/toggle', [CourierController::class, 'toggle']);
    });

    // ══════════════════════════════════════════════════════════════════════════
    // KURIR
    // PERBAIKAN BUG #4: 'courier' diganti 'kurir' agar sesuai nilai di DB
    // ══════════════════════════════════════════════════════════════════════════
    Route::middleware('checkrole:kurir,admin')->prefix('courier')->group(function () {
        Route::get('/deliveries',              [DeliveryController::class, 'index']);
        Route::post('/deliveries/{id}/pickup', [DeliveryController::class, 'pickup']);
        Route::post('/deliveries/{id}/done',   [DeliveryController::class, 'done']);
        Route::post('/deliveries/{id}/failed', [DeliveryController::class, 'failed']);
    });
});

Route::post('/test-upload', function (\Illuminate\Http\Request $req) {
    return response()->json([
        'post' => $req->all(),
        'files' => $req->allFiles()
    ]);
});