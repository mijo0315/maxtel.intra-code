<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('process_logs_crontab', [timekeepingController::class, 'process_logs_crontab'])->middleware(['auth'])->name('process_logs_crontab');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
