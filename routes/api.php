<?php

use App\Http\Controllers\DispositifController;
use App\Http\Controllers\JeuneController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\StructureDispositifController;
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



/// Action
Route::post('/actions', [\App\Http\Controllers\ActionController::class, 'actions']);

//////////////Dispositif///////////

Route::get('dispositifs', [DispositifController::class, 'index']); // Récupérer tous les dispositifs
Route::post('dispositifs', [DispositifController::class, 'store']); // Créer un dispositif
Route::get('dispositifs/{id}', [DispositifController::class, 'show']); // Récupérer un dispositif
Route::put('dispositifs/{id}', [DispositifController::class, 'update']); // Mettre à jour un dispositif
Route::delete('dispositifs/{id}', [DispositifController::class, 'destroy']); // Supprimer un dispositif


///////////////////////les structures
Route::get('structures', [StructureController::class, 'index']);
Route::post('structures', [StructureController::class, 'store']);
Route::get('structures/{id}', [StructureController::class, 'show']);
Route::put('structures/{id}', [StructureController::class, 'update']);
Route::delete('structures/{id}', [StructureController::class, 'destroy']);


/////Structure Dispositif ////////////////

Route::post('/structures/{structure}/add-dispositif', [StructureController::class, 'addDispositifStructure']);

/////////////////// Liste des dispositifs pour la structure donnée /////////

Route::get('structures/{structureId}/dispositifs', [StructureController::class, 'getDispositifsForStructure']);


//Manager

Route::apiResource('/Manager',controller: \App\Http\Controllers\ManagerController::class);
//Jeune

Route::apiResource('/Jeune',controller: \App\Http\Controllers\JeuneController::class);

Route::post('confirm-inscription', [JeuneController::class, 'confirmInscription'])->name('confirmInscription');

//Référent
Route::resource('referants', controller: \App\Http\Controllers\ReferantController::class);

