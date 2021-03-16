<?php

use App\Http\Controllers\API\AssetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

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

Route::middleware('auth:sanctum')->group(function(){    
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('asset/register', [AssetController::class, 'register']);
    
    Route::post('asset', [AssetController::class, 'all']);
    Route::post('asset/{id}', [AssetController::class, 'update']);
    Route::post('asset/photo/{id}', [AssetController::class, 'updatePhoto']);
    Route::post('asset/delete/{id}', [AssetController::class, 'delete']);

});

//route dibawah yang bisa digunakan oleh user saat belum login.
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);