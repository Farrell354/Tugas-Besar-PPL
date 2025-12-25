    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\TambalBanController;
    use App\Models\TambalBan; 
    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\OwnerController;

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

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Group Route Khusus Admin
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [TambalBanController::class, 'index'])->name('dashboard');
});

// ROUTE GROUP OWNER
Route::middleware(['auth', 'isOwner'])->prefix('owner')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\OwnerController::class, 'index'])->name('owner.dashboard');
    Route::post('/order/{id}/update', [App\Http\Controllers\OwnerController::class, 'updateStatus'])->name('owner.order.update');
    Route::get('/dashboard', [App\Http\Controllers\OwnerController::class, 'index'])->name('owner.dashboard');
    Route::post('/order/{id}/update', [App\Http\Controllers\OwnerController::class, 'updateStatus'])->name('owner.order.update');
    Route::get('/order/{id}', [App\Http\Controllers\OwnerController::class, 'show'])->name('owner.order.show');
    Route::get('/orders/{id}', [OrderController::class, 'adminShow'])->name('admin.orders.show');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update');
});

require __DIR__.'/auth.php';

