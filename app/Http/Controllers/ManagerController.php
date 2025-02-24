<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sceance;
use Illuminate\Support\Facades\DB;


class ManagerController extends Controller
{
    /**
     * Description: Récupérer la liste des utilisateurs ayant le rôle de manager.
     * Méthode: GET
     * Entrée: Aucune
     * Sortie: Liste des utilisateurs avec le rôle de manager + status 200 en cas de succès,
     *         message d'erreur + status 500 en cas d'échec.
     */
    public function index()
    {
        try {

            $users = User::with('role', 'structure')
                ->where('role_id', 3)
                ->get();

            return response()->json($users);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Une erreur s\'est produite',
                'error' => $e->getMessage(),
            ], 500);
        }

    }



      /**
     * Description: Créer un nouvel utilisateur avec un rôle spécifique.
     * Méthode: POST
     * Entrée: nom, Prenom, email, numTelephone, avatar (optionnel), structure_id, role_id
     * Sortie: Utilisateur créé + status 201 en cas de succès,
     *         message d'erreur + status 500 en cas d'échec.
     */
    public function store(Request $request)
{
    try {
        $request->validate([
            'nom' => 'required|string',
            'Prenom' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'numTelephone' => 'required|string|max:15',
            'avatar' => 'mimes:jpeg,png,jpg,gif',
            'structure_id' => 'required|exists:structures,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = new User();

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->extension();
            $avatar->move(public_path('images'), $avatarName);
            $user->avatar = $avatarName;
        }

        $user->nom = $request->nom;
        $user->Prenom = $request->Prenom;
        $user->email = $request->email;
        $user->password = 'passer123';
        $user->statut = 'Active';
        $user->numTelephone = $request->numTelephone;
        $user->role_id = $request->role_id;
        $user->structure_id = $request->structure_id;

        $user->save();

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Erreur de validation',
            'errors' => $e->errors(),
        ], 422);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Une erreur est survenue lors de la création de l\'utilisateur',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Description: Récupérer la liste des jeunes associés à la même structure que le manager connecté.
     * Méthode: GET
     * Entrée: Aucune (utilise l'utilisateur connecté)
     * Sortie: Liste des jeunes + status 200 en cas de succès,
     *         message d'erreur + status 403 si l'utilisateur n'est pas manager,
     *         message d'erreur + status 500 en cas d'échec.
     */
    public function getJeunesByManager(Request $request)
    {
        try {
            $currentUser = Auth::user();


            $roleJeune = Role::where('name', 'jeune')->first();
            if (!$roleJeune) {
                return response()->json(['message' => 'Le rôle "jeune" est introuvable.'], 500);
            }

            $jeunes = User::where('role_id', $roleJeune->id)
                ->where('structure_id', $currentUser->structure_id)
                ->get();

            return response()->json(['jeunes' => $jeunes], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des jeunes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




/**
     * Description: Assigner un jeune à une séance spécifique.
     * Méthode: POST
     * Entrée: user_id (identifiant du jeune), sceance_id (identifiant de la séance)
     * Sortie: Message de confirmation + status 200 en cas de succès,
     *         message d'erreur + status 403 si l'utilisateur n'est pas un jeune,
     *         message d'erreur + status 400 si des informations sont manquantes,
     *         message d'erreur + status 500 en cas d'échec.
*/

 public function assignJeuneToSceance(Request $request)
 {
     $validated = $request->validate([
         'user_id' => 'required|exists:users,id',
         'sceance_id' => 'required|exists:sceances,id',
     ]);

     DB::beginTransaction();

     try {
         $jeune = User::find($validated['user_id']);
         if ($jeune->role_id !== 2) {
             return response()->json(['message' => 'Seuls les utilisateurs avec un rôle "jeune" peuvent être assignés à une séance.'], 403);
         }
         $sceance = Sceance::find($validated['sceance_id']);
         if ($sceance->jeunes()->where('user_id', $jeune->id)->exists()) {
             return response()->json(['message' => 'Le jeune est déjà assigné à cette séance.'], 200);
         }
         $sceance->jeunes()->attach($jeune->id);
         $session = $sceance->session;

         if (!$session) {
             return response()->json(['message' => 'La séance n\'est associée à aucune session.'], 400);
         }
         $action = $session->action;

         if (!$action) {
             return response()->json(['message' => 'Aucune action associée à cette session.'], 400);
         }
         $structureDispositif = $action->structureDispositif;

         if (!$structureDispositif) {
             return response()->json(['message' => 'Aucune structure-dispositif associée à cette action.'], 400);
         }
         $dispositif = $structureDispositif->dispositif;

         if (!$dispositif) {
             return response()->json(['message' => 'Aucun dispositif associé à cette structure-dispositif.'], 400);
         }
         $jeune->dispositif_id = $dispositif->id;
         $jeune->save();
         DB::commit();
         return response()->json([
             'message' => 'Le jeune a été assigné à la séance avec succès.',
             'dispositif' => $dispositif,
         ], 200);
     } catch (Exception $e) {
         DB::rollBack();

         return response()->json([
             'message' => 'Une erreur est survenue lors de l\'assignation.',
             'error' => $e->getMessage(),
         ], 500);
     }
 }













}
