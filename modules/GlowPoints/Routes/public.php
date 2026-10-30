<?php

use Illuminate\Support\Facades\Route;

Route::get('account/glowpoints', 'GlowPointsController@index')->name('account.glowpoints.index');
Route::get('glowpoints/summary', 'GlowPointsController@summary')->name('glowpoints.summary');
Route::post('glowpoints/redeem', 'GlowPointsController@redeem')->name('glowpoints.redeem');
Route::delete('glowpoints/redeem', 'GlowPointsController@remove')->name('glowpoints.remove');
