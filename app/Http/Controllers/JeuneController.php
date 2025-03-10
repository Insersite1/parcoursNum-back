<?php

namespace App\Http\Controllers;

use App\Mail\JeuneCreatedMail;
use App\Models\User;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;


class JeuneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::with('role')
                ->where('role_id', 2)
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
 * Crée un nouvel utilisateur avec les informations fournies dans la requête.
 *
 * Cette fonction valide les données envoyées dans la requête, traite les fichiers (avatar),
 * crée un nouvel utilisateur, assigne les valeurs aux champs correspondants, puis sauvegarde
 * l'utilisateur dans la base de données. Si tout se passe bien, un email de bienvenue est envoyé
 * à l'utilisateur et une réponse de succès est retournée. En cas d'erreur, des messages d'erreur
 * détaillés sont retournés.
 *
 * @param \Illuminate\Http\Request $request La requête contenant les données de l'utilisateur à créer.
 *
  * @return \Illuminate\Http\JsonResponse La réponse JSON avec le message de succès ou d'erreur.
 */


 public function store(Request $request)
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


         // Validation des données
         $validatedData = $request->validate([
             'avatar' => 'mimes:jpeg,png,jpg,gif',
             'nom' => 'nullable|string',
             'Prenom' => 'nullable|string',
             'email' => 'required|string|email|unique:users',
             'numTelephone' => 'required|string',
             'sexe' => 'required|string',
         ]);

         // Création de l'utilisateur
         $user = new User();

         // Traitement de l'avatar
         if ($request->hasFile('avatar')) {
             $avatar = $request->file('avatar');
             $avatarName = time() . '.' . $avatar->extension();
             $avatar->move(public_path('images'), $avatarName);
             $user->avatar = $avatarName;
         }

         // Assignation des valeurs
         $user->nom = $validatedData['nom'];
         $user->Prenom = $validatedData['Prenom'];
         $user->email = $validatedData['email'];
         $user->numTelephone = $validatedData['numTelephone'];
         $user->password = bcrypt('passer123');
         $user->statut = 'Active';
         // Attribuer dynamiquement l'ID du rôle "jeune"
         $roleJeune = Role::where('name', 'Jeune')->first();
         if ($roleJeune) {
             $user->role_id = $roleJeune->id;
         } else {
             return response()->json(['message' => 'Rôle "jeune" non trouvé.'], 500);
         }

         $user->sexe = $validatedData['sexe'];
         $user->confirmation_token = Str::random(60);

         // Associer automatiquement la structure du manager à l'utilisateur
         $user->structure_id = $currentUser->structure_id;

         // Sauvegarde de l'utilisateur
         $user->save();

         // Génération du token
         $token = $user->createToken('UserToken')->plainTextToken;

         // Charger les détails de la structure associée
         $user->load('structure');

         // Envoi de l'email à l'utilisateur
         Mail::to($user->email)->send(new JeuneCreatedMail($user));

         // Réponse avec succès
         return response()->json(['message' => 'Utilisateur créé avec succès.', 'user' => $user, 'token' => $token], 201);

     } catch (ValidationException $e) {
         return response()->json(['message' => 'Erreur de validation.', 'errors' => $e->errors()], 422);
     } catch (Exception $e) {
         return response()->json(['message' => 'Une erreur est survenue lors de la création de l\'utilisateur.', 'error' => $e->getMessage()], 500);
     }
 }


/**
 * Confirme l'inscription de l'utilisateur en vérifiant le token de confirmation.
 *
 * @param \Illuminate\Http\Request $request Les données de la requête contenant le token.
 * @return \Illuminate\Http\JsonResponse Réponse JSON avec un message de succès ou d'erreur.
 */
    public function confirmInscription(Request $request)
    {
        $validatedData = $request->validate([
            'token' => 'required|string',
        ]);


        $user = User::where('confirmation_token', $validatedData['token'])->first();

        if ($user) {

            $user->confirmation_token = null;
            $user->statut = 'Active';
            $user->save();


            return response()->json([
                'message' => 'Inscription confirmée avec succès !',
                'user' => $user,
            ], 200);
        } else {
            // Si le token est invalide ou expiré
            return response()->json([
                'message' => 'Token de confirmation invalide.',
            ], 400);
        }
    }


/**
 * Récupère les détails d'un utilisateur par son identifiant, y compris ses informations de rôle et de structure.
 *
 * @param int $id L'identifiant de l'utilisateur.
 * @return \Illuminate\Http\JsonResponse La réponse JSON contenant l'utilisateur ou un message d'erreur.
 */

    public function show(Request $request)
    {
        try {
            // Récupérer l'ID de l'utilisateur connecté
            $userId = Auth::id();

            // Vérifier si l'utilisateur a le rôle et structure 'jeune'
            $user = User::with('role','structure')->where('role_id', 2)->findOrFail($userId);

            return response()->json([
                'message' => 'Utilisateur trouvé avec succès.',
                'user' => $user
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




/**
 * Supprime un utilisateur par son ID.
 *
 * @param int $id L'ID de l'utilisateur à supprimer.
 * @return \Illuminate\Http\JsonResponse Réponse JSON indiquant le succès ou l'erreur.
 */
    public function destroy($id)
{
    try {

        $user = User::findOrFail($id);

        if ($user->avatar) {
            unlink(public_path('images') . '/' . $user->avatar);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    } catch (Exception $e) {

        return response()->json(['message' => 'Une erreur est survenue lors de la suppression de l\'utilisateur.', 'error' => $e->getMessage()], 500);
    }
}

/**
 * Récupère le rôle d'un utilisateur par son ID de rôle.
 *
 * @param int $roleId L'ID du rôle à récupérer.
 * @return \Illuminate\Http\JsonResponse Réponse JSON avec le rôle trouvé ou une erreur.
 */
        public function getRoleByUserId($roleId)
        {
            try {

                $role = Role::findOrFail($roleId);

                $roleName = $role->name;
                return response()->json([
                    'message' => 'Rôle trouvé avec succès.',
                    'role' => $roleName
                ], 200);

            } catch (ValidationException $e) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé.'
                ], 404);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Une erreur est survenue.',
                    'error' => $e->getMessage()
                ], 500);
            }
    }

      /**
 * Modifier le mot de passe d'un jeune avec JWT.
 */
    public function updatePassword(Request $request)
    {
        // Validation des données
        $request->validate([
            'token' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Trouver l'utilisateur via le token de confirmation
        $user = User::where('confirmation_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token invalide ou utilisateur non trouvé.'], 404);
        }

        // Mise à jour du mot de passe
        $user->password = Hash::make($request->new_password);
        $user->confirmation_token = null; // Invalider le token après utilisation
        $user->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès.']);
    }




/**
 * Description: Complète le profil d'un utilisateur connecté en mettant à jour les informations manquantes.
 * Méthode: POST
 * Entrée:
 *  - dateNaissance (date, optionnelle)
 *  - Adresse (string, optionnelle)
 *  - ville (string, optionnelle)
 *  - codePostal (string, optionnelle)
 *  - region (string, optionnelle)
 *  - bibiographie (string, optionnelle)
 *  - situation (string, optionnelle)
 *  - etatCivil (string, optionnelle)
 *  - situationTravail (string, optionnelle)
 *  - QP, ZRR, ETH, EPC, API, AE (boolean, optionnelles)
 *  - NumSecuriteSocial (string, optionnelle)
 * Sortie:
 *  - Réponse JSON avec un message de succès et les données utilisateur mises à jour en cas de succès (status 200).
 *  - Réponse JSON avec un message d'erreur en cas de validation échouée (status 422) ou erreur serveur (status 500).
 */

    public function completeProfile(Request $request)
    {
        try {
            // Récupérer l'utilisateur connecté
            $currentUser = Auth::user();

            // Vérifier si l'utilisateur est bien connecté et a le rôle approprié
            if (!$currentUser || $currentUser->role->name != "jeune") {
                return response()->json(['message' => 'Accès non autorisé ou utilisateur non authentifié.'], 403);
            }

            // Validation des données supplémentaires
            $validatedData = $request->validate([
                'dateNaissance' => 'required|date',
                'Adresse' => 'required|string',
                'ville' => 'required|string',
                'codePostal' => 'required|string',
                'region' => 'required|string',
                'bibiographie' => 'required|string',
                'etatCivil' => 'required|string',
                'situationTravail' => 'required|string',
                'QP' => 'nullable|boolean',
                'ZRR' => 'nullable|boolean',
                'ETH' => 'nullable|boolean',
                'EPC' => 'nullable|boolean',
                'API' => 'nullable|boolean',
                'AE' => 'nullable|boolean',
                'NumSecuriteSocial' => 'required|string',
            ]);
            // Mise à jour des cases à cocher (par défaut `false` si non envoyé)
            $currentUser->QP = $validatedData['QP'] ?? false;
            $currentUser->ZRR = $validatedData['ZRR'] ?? false;
            $currentUser->ETH = $validatedData['ETH'] ?? false;
            $currentUser->EPC = $validatedData['EPC'] ?? false;
            $currentUser->API = $validatedData['API'] ?? false;
            $currentUser->AE = $validatedData['AE'] ?? false;
            Log::info('Data to be saved: ', $validatedData);
            // Mettre à jour les champs de l'utilisateur existant
            $currentUser->fill($validatedData);
            $currentUser->etat = "complet";
            $currentUser->save();
            Log::info($request->all());  // Cela affiche toutes les données envoyées dans la requête


            return response()->json(['message' => 'Profil mis à jour avec succès.', 'user' => $currentUser], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Erreur de validation.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue.', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * Description: Mettre à jour les informations complètes d'un utilisateur avec le rôle "Jeune".
     * Méthode: PUT ou PATCH
     * Entrée:
     *   - id (identifiant de l'utilisateur),
     *   - Données utilisateur (nom, prénom, email, téléphone, etc.).
     * Sortie:
     *   - Message de succès + status 200 en cas de mise à jour réussie,
     *   - Message d'erreur de validation + status 422 si les données sont invalides,
     *   - Message d'erreur + status 404 si l'utilisateur n'existe pas,
     *   - Message d'erreur + status 500 en cas d'erreur serveur.
     * Particularité:
     *   - Prise en charge de l'upload et la suppression de l'avatar.
     *   - Conversion automatique des champs booléens.
     */

    public function updateJeuneComplet(Request $request, $id)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'nom' => 'nullable|string|max:255',
                'Prenom' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'numTelephone' => 'required|string|max:15',
                'dateNaissance' => 'nullable|date',
                'statut' => 'nullable|in:Active,Inactive',
                'situation' => 'nullable|string',
                'sexe' => 'required|in:M,F',
                'etatCivil' => 'nullable|string',
                'situationTravail' => 'nullable|string',
                'QP' => 'nullable|boolean',
                'ZRR' => 'nullable|boolean',
                'ETH' => 'nullable|boolean',
                'EPC' => 'nullable|boolean',
                'API' => 'nullable|boolean',
                'AE' => 'nullable|boolean',
                'Adresse' => 'nullable|string|max:255',
                'bibiographie' => 'nullable|string',
                'codePostal' => 'nullable|string|max:10',
                'region' => 'nullable|string|max:255',
                'ville' => 'nullable|string|max:255',
                'NumSecuriteSocial' => 'nullable|string|max:20',
                'role_id' => 'nullable|exists:roles,id',
                'structure_id' => 'nullable|exists:structures,id',
            ]);

            // Récupération de l'utilisateur
            $user = User::findOrFail($id);

            // Gestion de l'avatar
            if ($request->hasFile('avatar')) {
                // Suppression de l'ancien avatar s'il existe
                if ($user->avatar && file_exists(public_path('images/' . $user->avatar))) {
                    unlink(public_path('images/' . $user->avatar));
                }
                // Téléchargement du nouvel avatar
                $avatar = $request->file('avatar');
                $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
                $avatar->move(public_path('images'), $avatarName);

                $user->avatar = $avatarName;
            }
            // Mise à jour de tous les champs
            foreach ($validatedData as $key => $value) {
                if ($value !== null) {
                    // Conversion des valeurs boolean
                    if (in_array($key, ['QP', 'ZRR', 'ETH', 'EPC', 'API', 'AE'])) {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    $user->$key = $value;
                }
            }

            // Sauvegarde des modifications
            $user->save();

            return response()->json([
                'message' => 'Utilisateur mis à jour avec succès.',
                'user' => $user
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'utilisateur.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Description: Récupérer le nombre d'actions, le nombre de sessions  et de séances associées à un utilisateur ayant le rôle "Jeune".
     * Méthode: GET
     * Entrée: id (identifiant de l'utilisateur).
     * Sortie: Nombre d'actions + nombre de séances + nombre de sessions + status 200 en cas de succès,
     *         message d'erreur + status 404 si l'utilisateur est introuvable ou n'a pas le rôle requis.
     */
    public function getJeuneUserStatistics($id)
    {
        /*$user = User::with(['actions', 'sceances','sessions'])*/
        $user = User::with(['actions'])
            ->where('id', $id)
            ->where('role_id', 2)
            ->first();
        if (!$user) {
            return response()->json([
                'error' => 'Utilisateur non trouvé ou ne correspond pas au rôle Jeune.'
            ], 404);
        }

        $data = [
            'actions_count' => $user->actions->count()
           /* 'sceances_count' => $user->sceances->count(),*/
           /* 'sessions_count' => $user->sessions->count(),*/
        ];

        return response()->json($data);
    }

    /**
     * Récupère la liste des séances futures d'un jeune.
     *
     * @param int $id L'identifiant du jeune
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSeanceByJeuneID($id)
    {
        try {
            $user = User::where('id', $id)
                ->where('role_id', 2)
                ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé ou n\'est pas un jeune.'
                ], 404);
            }

            // Récupérer les séances futures
            $currentDate = Carbon::now();
            $sceances = $user->sceance()
                ->where('date_fin', '>', $currentDate)
                ->orderBy('date_fin', 'desc')
                ->get();

            // Vérifier si toutes les sceance ont une date_fin antérieure à la date actuelle
            if ($sceances->isEmpty()) {
                return response()->json([
                    'message' => 'Toutes les sceances du jeune sont déjà achevées.'
                ], 404);
            }

            return response()->json([
                'message' => 'Séances récupérées avec succès',
                'sceances' => $sceances
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la liste des sessions futures d'un jeune.
     *
     * @param int $id L'identifiant du jeune
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSessionByJeuneID($id)
    {
        try {
            $user = User::where('id', $id)
                ->where('role_id', 2)
                ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé ou n\'est pas un jeune.'
                ], 404);
            }

            // Récupérer les sessions futures
            $currentDate = Carbon::now();
            $sessions = $user->sessions()
                ->where('date_fin', '>', $currentDate)
                ->orderBy('date_fin', 'desc')
                ->get();

            // Vérifier si toutes les sessions ont une date_fin antérieure à la date actuelle
            if ($sessions->isEmpty()) {
                return response()->json([
                    'message' => 'Toutes les sessions du jeune sont déjà achevées.'
                ], 404);
            }

            return response()->json([
                'message' => 'Sessions récupérées avec succès',
                'sessions' => $sessions
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}



