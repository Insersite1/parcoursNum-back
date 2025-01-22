<?php

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

Route::apiResource('/Jeune',controller: \App\Http\Controllers\UserController::class);


//Manager

Route::apiResource('/Manager',controller: \App\Http\Controllers\ManagerController::class);
Route::apiResource('referants', controller: \App\Http\Controllers\ReferantController::class);
Route::get('/destroyreferent/{id}', [\App\Http\Controllers\ReferantController::class,'destroyreferent']);
Route::put('/updatesref/{id}',[\App\Http\Controllers\ReferantController::class,'updatesref']);
Route::put('/updatereferantsetat/{id}', [\App\Http\Controllers\ReferantController::class,'updatereferantsetat']);


// Route::put('/updatereferants/{id}',[\App\Http\Controllers\ReferantController::class,'updatereferants'])->name('updateAgent');






// structure

Route::put('/updatestruc/{id}',[\App\Http\Controllers\StructureController::class,'updatestruc']);
Route::put('/updatestructureetat/{id}', [\App\Http\Controllers\StructureController::class,'updatestructureetat']);
Route::apiResource('/structures',controller: \App\Http\Controllers\StructureController::class);


