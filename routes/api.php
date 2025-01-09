<?php

use App\Http\Controllers\JeuneController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Jeune
Route::post('confirm-inscription', [JeuneController::class, 'confirmInscription'])->name('confirmInscription');




//Structure

Route::apiResource('/Structure',controller: \App\Http\Controllers\StructureController::class);



//Manager

Route::apiResource('/Manager',controller: \App\Http\Controllers\ManagerController::class);
