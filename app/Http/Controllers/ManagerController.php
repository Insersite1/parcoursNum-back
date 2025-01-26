<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        // Validation des données reçues
        $request->validate([
            'nom' => 'required|string',
            'Prenom' => 'required|string',
            'email' => 'required|email|unique:users,email',
            // 'password' => 'required',
            'numTelephone' => 'required|string|max:15',
            'avatar' => 'mimes:jpeg,png,jpg,gif',
            'structure_id' => 'required|exists:structures,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Création d'un utilisateur
        $user = new User();

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->extension();
            $avatar->move(public_path('images'), $avatarName);
            $user->image = $avatarName;
        }

        $user->nom = $request->nom;
        $user->Prenom = $request->Prenom;
        $user->email = $request->email;
        $user->password = 'passer123';
        $user->statut = 'Active';
        $user->numTelephone = $request->numTelephone;
        $user->role_id = $request->role_id;
        $user->structure_id = $request->structure_id;

        // Sauvegarder dans la base de données
        $user->save();

        // Réponse JSON
        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Gérer les erreurs de validation
        return response()->json([
            'message' => 'Erreur de validation',
            'errors' => $e->errors(),
        ], 422);
    } catch (Exception $e) {
        // Gérer d'autres exceptions générales
        return response()->json([
            'message' => 'Une erreur est survenue lors de la création de l\'utilisateur',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(Manager $manager)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manager $manager)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateManagerRequest $request, Manager $manager)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manager $manager)
    {
        //
    }


/**
 * Récupère une liste des jeunes associés à la même structure que le manager.
 *
 * Cette fonction vérifie si l'utilisateur connecté est authentifié et possède le rôle de manager.
 * Elle retourne uniquement les jeunes (utilisateurs ayant le rôle avec `role_id = 2`) appartenant
 * à la même structure que le manager (basé sur le `structure_id` de l'utilisateur connecté).
 * En cas d'erreur, des messages explicites sont retournés pour faciliter le débogage.
 *
 * @param \Illuminate\Http\Request $request La requête envoyée par le client.
 *
 * @return \Illuminate\Http\JsonResponse Une réponse JSON contenant la liste des jeunes ou un message d'erreur.
 */
public function getJeunes(Request $request)
{
    try {
        // Récupérer l'utilisateur connecté
        $currentUser = Auth::user();

        // Vérifier si l'utilisateur est authentifié
        if (!$currentUser) {
            return response()->json([
                'message' => 'Accès interdit. Utilisateur non authentifié.',
            ], 401);
        }

        // Vérifier si l'utilisateur est un manager
        if (!isset($currentUser->role_id) || $currentUser->role_id != 3) {
            return response()->json([
                'message' => 'Accès interdit. Seuls les managers peuvent voir cette liste.',
            ], 403);
        }

        // Récupérer les jeunes dans la même structure que le manager
        $jeunes = User::where('role_id', 2)
                      ->where('structure_id', $currentUser->structure_id) 
                      ->get();

        // Retourner la liste des jeunes
        return response()->json(['jeunes' => $jeunes], 200);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Une erreur est survenue lors de la récupération des jeunes.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
