<?php

use Illuminate\Support\Facades\Route;

Route::get('skin-analysis', 'SkinAnalysisController@index')->name('skin_analysis.index');
Route::post('skin-analysis/recommend', 'SkinAnalysisController@recommend')->name('skin_analysis.recommend');
Route::post('skin-analysis/ai-review', 'SkinAnalysisController@aiReview')->name('skin_analysis.ai_review');
Route::middleware('auth')->group(function () {
    Route::post('skin-analysis/save', 'SkinAnalysisController@save')->name('skin_analysis.save');
    Route::get('account/skin-analysis', 'SkinAnalysisController@history')->name('account.skin_analysis.index');
});
