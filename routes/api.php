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
use App\Http\Controllers\SondageController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\RoleController;

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

 Route::post('sessions/{session}/upload', [SessionController::class, 'uploadFile']);
// Route de recherche de session
 //Route::get('sessions/search', [SessionController::class, 'search']);
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


//sceance
Route::apiResource('sceances', SceanceController::class);
//session
Route::apiResource('sessions', SessionController::class);
// Route de recherche de session
Route::get('sessions/find/{search}', [SessionController::class, 'search']);

//sceance
Route::apiResource('sceances', SceanceController::class);
//session
Route::apiResource('sessions', SessionController::class);
// Route de recherche de session
Route::get('sessions/find/{search}', [SessionController::class, 'search']);


/// Action

Route::apiResource('/Jeune',controller: \App\Http\Controllers\UserController::class);
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
Route::apiResource('/Manager',controller: ManagerController::class)->middleware('jwt.auth');
Route::get('/listejeunes', [ManagerController::class, 'getJeunesByManager'])->middleware('jwt.auth');
Route::post('/sceances/assign-jeune', [ManagerController::class, 'assignJeuneToSceance']);

//Jeune

Route::apiResource('/Jeune',controller: JeuneController::class)->middleware('jwt.auth');

Route::post('confirm-inscription', [JeuneController::class, 'confirmInscription'])->name('confirmInscription');
Route::get('Jeune/{id}/role', [JeuneController::class,'getRoleByUserId']);
Route::get('/apercu/{id}', [JeuneController::class, 'getJeuneUserStatistics']);

//Modifier mot passe du jeune avec jwt
Route::post('/jeune/update-password', [JeuneController::class, 'updatePassword']);
Route::get('/jeune/profile', [JeuneController::class, 'show'])->middleware('jwt.auth');

Route::middleware('jwt.auth')->post('/jeune/complete-profile', [JeuneController::class, 'completeProfile']);
Route::put('/users/{id}', [JeuneController::class, 'updateJeuneComplet']);

Route::get('/role/{name}', [RoleController::class, 'getRoleByName']);


//Référent
Route::apiResource('referants', controller: \App\Http\Controllers\ReferantController::class);
Route::get('/destroyreferent/{id}', [\App\Http\Controllers\ReferantController::class,'destroyreferent']);
Route::put('/updatesref/{id}',[\App\Http\Controllers\ReferantController::class,'updatesref']);
Route::put('/updatereferantsetat/{id}', [\App\Http\Controllers\ReferantController::class,'updatereferantsetat']);


// Route::put('/updatereferants/{id}',[\App\Http\Controllers\ReferantController::class,'updatereferants'])->name('updateAgent');


Route::put('/updatereferantsetat/{id}', [\App\Http\Controllers\ReferantController::class,'updatereferantsetat']);


// Tableau de bord
// Tableau de bord

Route::get('/user-counts', [TableauBordController::class, 'getCounts']);
Route::get('/users-by-region', [TableauBordController::class, 'getUsersByRegion']);
Route::get('/distributionJeunesByAge', [TableauBordController::class, 'distributionJeunesByAge']);
Route::get('/distributionJeunesByAge', [TableauBordController::class, 'distributionJeunesByAge']);
Route::get('tableau-bord/nombre-jeunes-par-dispositif', [TableauBordController::class, 'nombreJeunesParDispositif']);
Route::get('/actions-jeunes', [TableauBordController::class, 'getJeunesByAction']);
Route::get('/users-by-region', [TableauBordController::class, 'getUsersByRegion']);


// Authentification

Route::post('/login',[AuthController::class,'login']);
Route::post('/register',[AuthController::class,'register']);
Route::middleware('auth:api')->group(function () {
    Route::get('/user-profile', [AuthController::class, 'profile']);
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');

});

Route::get('/distributionJeunesByAge', [App\Http\Controllers\TableauBordController::class, 'distributionJeunesByAge']);


// Routes Sondages
Route::apiResource('sondages', SondageController::class);
Route::patch('/sondages/{id}/change-status', [SondageController::class, 'changeStatus']);


// Routes Réponses
Route::apiResource('reponses', ReponseController::class);
Route::get('sondages/{sondage}/reponses', [ReponseController::class, 'getReponsesSondage']);

Route::get('/structures/{structureId}/jeunes', [StructureController::class, 'getJeuneByStructureID']);
Route::get('/structures/{structureId}/referants', [StructureController::class, 'getReferantByStructureID']);
Route::get('/structures/{structureId}/actions', [StructureController::class, 'getActionsByStructure']);
Route::post('/structures/{structureId}/add-action', [StructureController::class, 'addActionToStructure']);


Route::get('/sceances/session/{session_id}', [SceanceController::class, 'getSceanceBySessionID']);
Route::get('/sessions/action/{action_id}', [SessionController::class, 'getSessionByActionID']);
