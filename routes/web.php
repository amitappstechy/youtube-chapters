<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('frontend.auth.youtube-url-landing');
});


//Get Trans Script of youtube video
Route::group(['namespace' => 'App\Http\Controllers\Frontend', 'as' => 'front.'], function () {
    Route::post('transcript','YouTubeTransScriptController@get_transcript')->name('youtube-video.transcript');
    Route::post('open-ai-response','YouTubeTransScriptController@get_open_ai_response')->name('youtube-video.open-ai-response');
});