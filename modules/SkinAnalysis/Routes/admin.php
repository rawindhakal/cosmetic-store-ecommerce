<?php

use Illuminate\Support\Facades\Route;

Route::get('skin-analysis', 'SkinAnalysisSettingController@index')->name('admin.skin_analysis.index');
Route::put('skin-analysis/settings', 'SkinAnalysisSettingController@update')->name('admin.skin_analysis.update');
