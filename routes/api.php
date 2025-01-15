<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispositifController;
use App\Http\Controllers\JeuneController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\StructureDispositifController;
use Illuminate\Http\Request;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SceanceController;
use App\Http\Controllers\ManagerController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TableauBordController;

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
Route::get('sessions/find/{search}', [SessionController::class, 'search']);

/////Midellware//////
Route::middleware(['super_admin'])->group(function () {
    // Routes accessibles uniquement par le super_admin
});

Route::middleware(['super_admin_manager'])->group(function () {
    // Routes accessibles par le super_admin et le manager
});

Route::middleware(['super_admin_manager_referant'])->group(function () {
    // Routes accessibles aux super_admins, managers et référents
});

Route::middleware(['jeune'])->group(function () {
    // Routes accessibles aux jeunes
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



/// Action

Route::apiResource('/actions', controller: \App\Http\Controllers\ActionController::class);


//////////////Dispositif///////////

Route::get('dispositifs', [DispositifController::class, 'index']); // Récupérer tous les dispositifs
Route::post('dispositifs', [DispositifController::class, 'store']); //  Créer un dispositif
Route::get('dispositifs/{id}', [DispositifController::class, 'show']); // Récupérer un dispositif
Route::put('dispositifs/{id}', [DispositifController::class, 'update']); // Mettre à jour un dispositif
Route::delete('dispositifs/{id}', [DispositifController::class, 'destroy']); // Supprimer un dispositif


///////////////////////les structures
Route::get('structures', [StructureController::class, 'index']);
Route::post('structures', [StructureController::class, 'store']);
Route::get('structures/{id}', [StructureController::class, 'show']);
Route::put('/structures/{id}', [StructureController::class, 'update']);
Route::delete('structures/{id}', [StructureController::class, 'destroy']);
Route::put('/updatestructureetat/{id}', [StructureController::class,'updatestructureetat']);

/////Structure Dispositif ////////////////

Route::post('/structures/{structure}/add-dispositif', [StructureController::class, 'addDispositifStructure']);

/////////////////// Liste des dispositifs pour la structure donnée /////////

Route::get('structures/{structureId}/dispositifs', [StructureController::class, 'getDispositifsForStructure']);


//Manager

Route::apiResource('/Manager',controller: \App\Http\Controllers\ManagerController::class);

//Jeune

Route::apiResource('/Jeune',controller: \App\Http\Controllers\JeuneController::class);
Route::post('confirm-inscription', [JeuneController::class, 'confirmInscription'])->name('confirmInscription');
Route::get('Jeune/{id}/role', [JeuneController::class,'getRoleByUserId']);
Route::put('/users/{id}', [JeuneController::class, 'updateJeuneComplet']);


//Référent
Route::apiResource('referants', controller: \App\Http\Controllers\ReferantController::class);


//Dashboard
/*Route::get('/youth-statistics', [TableauBordController::class, 'getYoungUserStatistics']);*/


//Dashbor

Route::get('/user-counts', [TableauBordController::class, 'getCounts']);
Route::get('/users-by-region', [TableauBordController::class, 'getUsersByRegion']);


Route::get('tableau-bord/nombre-jeunes-par-dispositif', [TableauBordController::class, 'nombreJeunesParDispositif']);
Route::get('/actions-jeunes', [TableauBordController::class, 'getJeunesByAction']);
Route::get('/users-by-region', [TableauBordController::class, 'getUsersByRegion']);


Route::post('/login',[\App\Http\Controllers\AuthController::class,'login']);
Route::post('/register',[\App\Http\Controllers\AuthController::class,'register']);

