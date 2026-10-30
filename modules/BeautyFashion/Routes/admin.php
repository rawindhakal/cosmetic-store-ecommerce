<?php

use Illuminate\Support\Facades\Route;

Route::get('beauty/details', 'DetailsController@index')->name('admin.beauty.details');
Route::get('beauty/details/{product}', 'DetailsController@edit')->name('admin.beauty.details.edit');
Route::put('beauty/details/{product}', 'DetailsController@update')->name('admin.beauty.details.update');

Route::get('beauty/size-charts', 'SizeChartController@index')->name('admin.beauty.sizecharts');
Route::get('beauty/size-charts/create', 'SizeChartController@create')->name('admin.beauty.sizecharts.create');
Route::post('beauty/size-charts', 'SizeChartController@store')->name('admin.beauty.sizecharts.store');
Route::get('beauty/size-charts/{id}/edit', 'SizeChartController@edit')->name('admin.beauty.sizecharts.edit');
Route::put('beauty/size-charts/{id}', 'SizeChartController@update')->name('admin.beauty.sizecharts.update');
Route::delete('beauty/size-charts/{id}', 'SizeChartController@destroy')->name('admin.beauty.sizecharts.destroy');

Route::get('beauty/batches', 'BatchController@index')->name('admin.beauty.batches');
Route::post('beauty/batches', 'BatchController@store')->name('admin.beauty.batches.store');
Route::post('beauty/batches/{id}/writeoff', 'BatchController@writeOff')->name('admin.beauty.batches.writeoff');
Route::delete('beauty/batches/{id}', 'BatchController@destroy')->name('admin.beauty.batches.destroy');
