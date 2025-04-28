<?php

use App\Http\Controllers\ProfileController;
use App\Models\Alimento;
use Illuminate\Support\Facades\Route;
use App\Models\Donante;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ver-logo/{id}', function ($id) {
    $donante = Donante::find($id);

    if (!$donante || !$donante->logo) {
        abort(404);
    }

    return Response::make($donante->logo, 200, [
        'Content-Type' => 'image/png', // por ahora suponemos que es JPEG
        'Content-Disposition' => 'inline; filename="logo.png"',
    ]);
});

Route::get('/ver-foto/{id}', function ($id) {
    $alimento = Alimento::find($id);

    if (!$alimento || !$alimento->foto) {
        abort(404);
    }

    return Response::make($alimento->foto, 200, [
        'Content-Type' => 'image/png', // por ahora suponemos que es JPEG
        'Content-Disposition' => 'inline; filename="foto.png"',
    ]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
