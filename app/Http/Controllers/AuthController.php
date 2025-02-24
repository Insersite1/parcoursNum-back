<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     // Valider les données du formulaire
    //     $data = $request->validate([
    //         "Prenom" => "required",
    //         "nom" => "required",
    //         "Adresse" => "required",
    //         "numTelephone" => "required|unique:users|min:9",
    //         "sexe" => "required",
    //         "email" => "required|email|unique:users",
    //         "dateNaissance" => "required",
    //         "password" => "required|min:6",
    //         "avatar" => "nullable|image|mimes:jpeg,png,jpg,gif",
    //     ]);

    //     try {
    //         // Traitement de l'upload de l'image
    //         if ($request->hasFile('avatar')) {
    //             $filename = time() . '_' . $request->file('avatar')->getClientOriginalName();
    //             $path = $request->file('avatar')->storeAs('images', $filename, 'public');
    //             $data['avatar'] = '/images/' . $path;
    //         }

    //         // Hash du mot de passe avant de le stocker
    //         $data['password'] = Hash::make($data['password']);

    //         // Définir le statut par défaut à "debloquer"
    //         $data['statut'] = 'Active';
    //         $data['role_id'] = 1;

    //         // Création de l'utilisateur
    //         $user = User::create($data);

    //         // Réponse avec les données de l'utilisateur
    //         return response()->json([
    //             'statut' => 201,
    //             'data' => $user,
    //             "token" => null,
    //         ], 201);

    //     } catch (\Exception $e) {
    //         // En cas d'erreur, retourne un message d'erreur JSON
    //         return response()->json([
    //             "statut" => false,
    //             "message" => "Erreur lors de l'inscription",
    //             "error" => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function login(Request $request)
    {
        // Valider les données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        // Rechercher l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        // Si l'utilisateur n'est pas trouvé ou que le mot de passe ne correspond pas
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
        // Générer un token JWT pour l'utilisateur
        if (!$token = JWTAuth::fromUser($user)) {
            return response()->json([
                'message' => 'Erreur lors de la génération du token.',
//                'statut' => false
            ], 500);
        }
        // Charger le rôle associé à l'utilisateur
        $roleName = $user->role->name;


        // Retourner la réponse avec le token et les informations de l'utilisateur
        return response()->json([
            'message' => 'Connexion réussie.',
            'statut' => 0,
            'token' => $token,
            'user' => $user,
            'role' => $roleName,
        ], 200);
    }

    public function logout()
    {
        auth()->logout();
        return  response() -> json([
            'status' => 'true',
            'message' => 'Logged out successfully',
            'token' => null
        ]);
    }

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