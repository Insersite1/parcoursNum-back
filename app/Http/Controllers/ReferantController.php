<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Referant;

class ReferantController extends Controller
{
    /**
     * Liste des référants.
     */
    public function index()
    {
        // Récupère les utilisateurs avec role_id = 4 et leur structure associée
        $referants = User::where('role_id', 4)
                         ->with('structure') // Charge la relation
                         ->get();

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
                'avatar' =>'nullable|mimes:jpeg,png,jpg,gif',  //'nullable|string',
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'numTelephone' => 'nullable|string|max:20',
                'password' => 'nullable|string', // Le mot de passe est requis avec une longueur minimale
                'sexe' => 'nullable|in:M,F',
                'adresse' => 'nullable|string|max:255',
                'structure_id' => 'nullable|exists:structures,id',
            ]);
            
            $avatarName = null;
            $referant=new User();
            // Gestion de l'avatar
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = time() . '.' . $avatar->extension();
                $avatar->move(public_path('images'), $avatarName);
                 $referant->avatar = $avatarName;
            }
            // Création du référant
            $referant = User::create([
                'avatar' => $avatarName, // Avtar par défaut si pas fourni
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
        $referant = Referant::findOrFail($id);
        $referant->update($request->all());
        return response()->json($referant);
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




    public function updatereferantsetat($id)
    {
        $User = User::find($id); // Trouver l'utilisateur par son ID
        if ($User) {
            // Basculer l'état en fonction de la valeur actuelle
            if ($User->statut == 'Active') {
                $User->statut = 'Inactive';
            } elseif ($User->statut == 'Inactive') {
                $User->statut = 'Active';
            }
            
            $User->save(); // Enregistrer les modifications
            
            return response()->json(['message' => 'État mis à jour avec succès.'], 200);
        } else {
            return response()->json(['error' => 'referent introuvable.'], 404);
        }
    }
    





}
