    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\TambalBanController;
    use App\Models\TambalBan;
    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\OwnerController;
    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\ChatController;
    use App\Http\Controllers\ReviewController;

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    */

    // 1. Halaman Depan (Landing Page)
    Route::get('/', function () {
        return view('welcome');
    })->name('landing');

    // 2. Halaman Peta (Untuk User Mencari Tambal Ban)
    Route::get('/peta', function () {
        // Load relasi reviews agar bisa dihitung ratingnya
        $lokasi = \App\Models\TambalBan::with('reviews')->get();
        return view('peta', compact('lokasi'));
    })->name('peta.index');

    // 3. Route Default Breeze (Dashboard, Profile, dll)
    Route::get('/dashboard', function () {
        // Redirect user biasa ke halaman peta saja, Admin ke dashboard admin
        if (auth()->user()->role === 'admin') {
            return redirect()->route('dashboard'); // Nama route dashboard admin
        }
        return redirect('/');
    })->middleware(['auth', 'verified']);

    // GROUP USER BIASA
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
        // Form Booking
        Route::get('/booking/{id}', [OrderController::class, 'create'])->name('booking.create');
        Route::post('/booking', [OrderController::class, 'store'])->name('booking.store');
        // Riwayat Booking
        Route::get('/riwayat-pesanan', [OrderController::class, 'history'])->name('booking.history');
        Route::get('/booking/{id}/detail', [OrderController::class, 'show'])->name('booking.show');
        Route::patch('/booking/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('booking.cancel');
    });

    // 4. GROUP ROUTE KHUSUS ADMIN
    Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [TambalBanController::class, 'index'])->name('dashboard');
        Route::get('/live-map', [TambalBanController::class, 'liveMap'])->name('admin.map');

        // Resource Admin (CRUD Bengkel)
        Route::resource('tambal-ban', TambalBanController::class);

        Route::get('/orders', [OrderController::class, 'adminIndex'])->name('admin.orders.index');
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update');
        Route::get('/orders/{id}', [OrderController::class, 'adminShow'])->name('admin.orders.show');
    });


    // ROUTE GROUP OWNER
    Route::middleware(['auth', 'isOwner'])->prefix('owner')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\OwnerController::class, 'index'])->name('owner.dashboard');
        Route::post('/order/{id}/update', [App\Http\Controllers\OwnerController::class, 'updateStatus'])->name('owner.order.update');
        Route::get('/order/{id}', [App\Http\Controllers\OwnerController::class, 'show'])->name('owner.order.show');
    });

    // ROUTE CHAT
    Route::middleware(['auth'])->group(function () {
        Route::get('/chat/{order_id}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/{order_id}/send', [ChatController::class, 'send'])->name('chat.send');
        Route::get('/chat/{order_id}/get', [ChatController::class, 'getMessages'])->name('chat.get');
    });

    // ROUTE REVIEW 
    Route::post('/review', [ReviewController::class, 'store'])->middleware('auth')->name('review.store');

    require __DIR__ . '/auth.php';
