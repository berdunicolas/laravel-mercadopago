<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cobro', [PaymentController::class, 'pantallaDeCobro']);

Route::get('/pago', [PaymentController::class, 'formularioDePago']);

Route::post('/process_payment', [PaymentController::class, 'procesoDePago']);