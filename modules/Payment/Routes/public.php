<?php

use Illuminate\Support\Facades\Route;

Route::post('checkout/payment-proof/{orderId}', 'PaymentProofController@store')->name('payment.proof.store');
