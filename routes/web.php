<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("/", function () {
    return view("app_home");
});

Route::post("api/start_traceroute", "LookingGlassController@dispatchNewTraceroute");
Route::post("api/check_result/{traceID}", "LookingGlassController@checkTraceResult");
Route::any("api/ipinfo", "IPDataController@getIpInfo");
