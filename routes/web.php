<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pay/{order_id}/{payment_method_code}', [PaymentController::class, 'pay']);
