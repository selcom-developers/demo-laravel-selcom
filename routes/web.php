<?php

use App\Http\Controllers\CheckoutController;
use App\Models\Checkout;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/checkout');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Route::get('/checkout', [CheckoutController::class, 'create']);
Route::post('/checkout', [CheckoutController::class, 'store']);

Route::post('/webhook', [CheckoutController::class, 'webhook'])->name('webhook');
Route::get('/success', function (){
    return view('success');
})->name('success');
