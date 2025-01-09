<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ReferantController extends Controller
{
    /**
     * Liste des référants.
     */
    public function index()
    {
        // Récupère tous les utilisateurs avec role_id = 4 (référants)
        $referants = User::where('role_id', 4)->get();

        return response()->json([
            'success' => true,
            'data' => $referants,
        ]);
    }

    /**
     * Création d'un nouveau référant.
     */
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'avatar' => 'required|string|max:255', // Avatar est optionnel
                'nom' => 'required|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'numTelephone' => 'required|string|max:20',
                'password' => 'required|string|min:8', // Le mot de passe est requis avec une longueur minimale
                'sexe' => 'nullable|in:M,F',
                'adresse' => 'nullable|string|max:255',
                'structure_id' => 'required|exists:structures,id',
            ]);
        
            // Gestion de l'avatar
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }
        
            // Création du référant
            $referant = User::create([
                'avatar' =>  "photo", // Avatar par défaut si pas fourni
                'nom' => $validated['nom'],
                'Prenom' => $validated['prenom'] ?? null,
                'email' => $validated['email'],
                'numTelephone' => $validated['numTelephone'],
                'password' => Hash::make($validated['password']), // Hashage du mot de passe
                'statut' => 'Active',
                'sexe' => $validated['sexe'] ?? null,
                'Adresse' => $validated['adresse'] ?? null,
                'role_id' => 4,
                'structure_id' => $validated['structure_id'],
            ]);
        
            // Réponse
            return response()->json([
                'success' => true,
                'message' => 'Référant créé avec succès.',
                'data' => $referant->makeHidden(['password', 'remember_token']), // Masquer le mot de passe
            ], 201);
        
        } catch (\Exception $e) {
            // Gestion des erreurs générales
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du référant.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Affiche un référant spécifique.
     */
    public function show($id)
    {
        $referant = User::where('role_id', 4)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $referant,
        ]);
    }

    /**
     * Mise à jour d'un référant.
     */
    public function update(Request $request, $id)
    {
        $referant = User::where('role_id', 4)->findOrFail($id);

        // Validation des données
        $validated = $request->validate([
            'avatar' => 'nullable|image|max:2048',
            'nom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $referant->id,
            'numTelephone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'statut' => 'nullable|in:Active,Inactive',
            'sexe' => 'nullable|in:M,F',
            'adresse' => 'nullable|string|max:255',
            'structure_id' => 'nullable|exists:structures,id',
        ]);

        // Gestion de l'avatar (si fourni)
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $referant->avatar = $avatarPath;
        }

        // Mise à jour des autres champs
        $referant->fill(array_filter($validated)); // Mise à jour des champs non nuls
        if (isset($validated['password'])) {
            $referant->password = Hash::make($validated['password']); // Hachage du mot de passe
        }
        $referant->save();

        return response()->json([
            'success' => true,
            'message' => 'Référant mis à jour avec succès.',
            'data' => $referant,
        ]);
    }

    /**
     * Suppression d'un référant.
     */
    public function destroy($id)
    {
        $referant = User::where('role_id', 4)->findOrFail($id);
        $referant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Référant supprimé avec succès.',
        ]);
    }
}
