<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Inscription
    public function register(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'nomComplet' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:6',
                'role_id' => 'required|integer',
            ]);

            // Création de l'utilisateur
            $user = User::create([
                'nomComplet' => $request->input('nomComplet'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role_id' => $request->input('role_id'),
            ]);

            // Génération du token JWT pour l'utilisateur
            $token = JWTAuth::fromUser($user);

            // Réponse JSON
            return response()->json([
                'status' => 201,
                'data' => $user,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Connexion
    public function login(Request $request)
    {
        try {
            // Validation des données de la requête
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            // Tentative d'authentification et génération du token JWT
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Email ou mot de passe incorrect.',
                ], 401);
            }

            return response()->json([
                'status' => 200,
                'token' => $token,
            ], 200);

        } catch (JWTException $e) {
            // Gestion des erreurs liées à JWT
            return response()->json([
                'status' => 500,
                'message' => 'Impossible de générer le token.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Déconnexion
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => 200,
                'message' => 'Déconnexion réussie.',
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue lors de la déconnexion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
