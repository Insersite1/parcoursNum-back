<?php

use App\Http\Controllers\SceanceController;
use App\Http\Controllers\SessionController;
use Illuminate\Http\Request;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


//sceance
Route::apiResource('sceances', SceanceController::class);

//session
Route::apiResource('sessions', SessionController::class);

// Route de recherche de session
//Route::get('sessions/search', [SessionController::class, 'search']);
Route::get('sessions/find/{search}', [SessionController::class, 'search']);



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Jeune

Route::apiResource('/Jeune',controller: \App\Http\Controllers\UserController::class);



//Structure

Route::apiResource('/Structure',controller: \App\Http\Controllers\StructureController::class);



//Manager

Route::apiResource('/Manager',controller: \App\Http\Controllers\ManagerController::class);
