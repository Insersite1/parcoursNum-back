<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

/**
 * Description: Authentifier un utilisateur et générer un token JWT.
 * Méthode: POST
 * Entrée: email (string), password (string)
 * Sortie: Token JWT + informations de l'utilisateur + statut 200 en cas de succès,
 * message d'erreur + statut 401 ou 403 en cas d'échec.
 */


    public function login(Request $request)
    {
        // Valider les données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
                'statut' => 'false'
            ], 401);
        }
        if ($user->statut == 'Inactive') {
            return response()->json([
                'message' => 'Votre compte est bloqué. Veuillez contacter l\'administrateur.',
                'statut' => 'Inactive'
            ], 403);
        }
        if (!$token = JWTAuth::fromUser($user)) {
            return response()->json([
                'message' => 'Erreur lors de la génération du token.',
            ], 500);
        }
        $roleName = $user->role->name;
        return response()->json([
            'message' => 'Connexion réussie.',
            'statut' => 0,
            'token' => $token,
            'user' => $user,
            'role' => $roleName,
        ], 200);
    }
/**
 * Description: Déconnecter un utilisateur en invalidant son token.
 * Méthode: POST
 * Entrée: Aucun paramètre requis.
 * Sortie: Message de confirmation + statut 200.
 */

    public function logout()
    {
        auth()->logout();
        return  response() -> json([
            'status' => 'true',
            'message' => 'Logged out successfully',
            'token' => null
        ]);
    }
/**
 * Description: Récupérer les informations du profil de l'utilisateur authentifié.
 * Méthode: GET
 * Entrée: Aucun paramètre requis (authentification requise).
 * Sortie: Données du profil utilisateur + statut 200,
 * message d'erreur + statut 401 si non authentifié.
 */

    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'prenom' => $user->Prenom,
            'nom' => $user->nom,
            'avatar' => $user->avatar,
            'role' => $user->role,
        ]);
    }
}
