<?php

use Illuminate\Support\Facades\Route;

Route::get('pos', 'PosController@index')->name('admin.pos.index');
Route::get('pos/catalog', 'PosController@catalog')->name('admin.pos.catalog');
Route::get('pos/barcode/{code}', 'PosController@barcode')->name('admin.pos.barcode');
Route::post('pos/sale', 'PosController@sale')->name('admin.pos.sale');
Route::get('pos/customers', 'PosController@customers')->name('admin.pos.customers');
Route::post('pos/customers', 'PosController@storeCustomer')->name('admin.pos.customers.store');
Route::get('pos/holds', 'PosController@holds')->name('admin.pos.holds');
Route::post('pos/holds', 'PosController@hold')->name('admin.pos.holds.store');
Route::delete('pos/holds/{id}', 'PosController@deleteHold')->name('admin.pos.holds.destroy');
Route::post('pos/quick-product', 'PosController@quickProduct')->name('admin.pos.quick_product');
Route::post('pos/register/open', 'PosController@openRegister')->name('admin.pos.register.open');
Route::get('pos/register/summary', 'PosController@registerSummary')->name('admin.pos.register.summary');
Route::post('pos/register/cash', 'PosController@cashMovement')->name('admin.pos.register.cash');
Route::post('pos/register/close', 'PosController@closeRegister')->name('admin.pos.register.close');

Route::get('pos/receipt/{order}', 'PosAdminController@receipt')->name('admin.pos.receipt');
Route::get('pos/sales', 'PosAdminController@sales')->name('admin.pos.sales');
Route::get('pos/sales/{order}/return', 'PosAdminController@returnForm')->name('admin.pos.return');
Route::post('pos/sales/{order}/return', 'PosAdminController@processReturn')->name('admin.pos.return.store');
Route::get('pos/registers', 'PosAdminController@registers')->name('admin.pos.registers');
Route::get('pos/registers/{id}', 'PosAdminController@registerReport')->name('admin.pos.registers.show');
Route::get('pos/barcodes', 'PosAdminController@barcodes')->name('admin.pos.barcodes');
Route::post('pos/barcodes', 'PosAdminController@saveBarcode')->name('admin.pos.barcodes.save');
Route::post('pos/barcodes/generate', 'PosAdminController@generateBarcodes')->name('admin.pos.barcodes.generate');
Route::get('pos/labels', 'PosAdminController@labels')->name('admin.pos.labels');
Route::get('pos/settings', 'PosAdminController@settings')->name('admin.pos.settings');
Route::put('pos/settings', 'PosAdminController@updateSettings')->name('admin.pos.settings.update');
Route::post('pos/sales/{order}/verify', 'PosAdminController@verify')->name('admin.pos.verify');
