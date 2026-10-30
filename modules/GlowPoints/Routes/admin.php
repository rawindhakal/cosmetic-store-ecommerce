<?php

use Illuminate\Support\Facades\Route;

Route::get('glowpoints', 'GlowPointController@index')->name('admin.glowpoints.index');
Route::post('glowpoints/adjust', 'GlowPointController@adjust')->name('admin.glowpoints.adjust');
Route::get('glowpoints/settings', 'GlowPointSettingController@edit')->name('admin.glowpoints.settings');
Route::put('glowpoints/settings', 'GlowPointSettingController@update')->name('admin.glowpoints.settings.update');
