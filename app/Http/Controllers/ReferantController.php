<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\Referant;

class ReferantController extends Controller
{

/**
 * Description: Récupère la liste de tous les référants avec leurs structures associées.
 * Méthode: GET
 * Entrée: Aucune entrée nécessaire.
 * Sortie: Liste des référants + statut 200 en cas de succès, message d'erreur + statut 500 en cas d'échec.
 */
    public function index()
    {
        $referants = User::where('role_id', 4)
                         ->with('structure') 
                         ->get();

        return response()->json([
            'success' => true,
            'data' => $referants,
        ]);
    }

/**
 * Description: Crée un nouveau référant avec les informations fournies.
 * Méthode: POST
 * Entrée: avatar, nom, prénom, email, numéro de téléphone, mot de passe, sexe, adresse, structure_id
 * Sortie: Nouveau référant créé + statut 201 en cas de succès, message d'erreur + statut 500 en cas d'échec.
 */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'avatar' =>'nullable|mimes:jpeg,png,jpg,gif',  
                'nom' => 'nullable|string|max:255',
                'Prenom' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'numTelephone' => 'nullable|string|max:20',
                'password' => 'nullable|string', 
                'sexe' => 'nullable|in:M,F',
                'Adresse' => 'nullable|string|max:255',
                'structure_id' => 'nullable|exists:structures,id',
            ]);
            
           $avatarName = null;
            $referant=new User();
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = time() . '.' . $avatar->extension();
                $avatar->move(public_path('images'), $avatarName);
                 $referant->avatar = $avatarName;
            }
            $referant = User::create([
                'avatar' => $avatarName, 
                'nom' => $validated['nom'],
                'Prenom' => $validated['Prenom'] ?? null,
                'email' => $validated['email'],
                'numTelephone' => $validated['numTelephone'],
                'password' => Hash::make($validated['password']), 
                'statut' => 'Active',
                'sexe' => $validated['sexe'] ?? null,
                'Adresse' => $validated['Adresse'] ?? null,
                'role_id' => 4, 
                'structure_id' => $validated['structure_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Référant créé avec succès.',
                'data' => $referant->makeHidden(['password', 'remember_token']), 
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du référant.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

/**
 * Description: Récupère les détails d'un référant en fonction de son identifiant.
 * Méthode: GET
 * Entrée: id (identifiant du référant)
 * Sortie: Détails du référant + statut 200 en cas de succès, message d'erreur + statut 404 si le référant est introuvable.
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
 * Description: Met à jour les informations d'un référant existant.
 * Méthode: PUT
 * Entrée: id (identifiant du référant), nom, prénom, numéro de téléphone, email, sexe, adresse, structure_id
 * Sortie: Référant mis à jour + statut 200 en cas de succès, message d'erreur + statut 404 si le référant est introuvable.
 */
     public function update(Request $request, $id)
     {
         $request->validate([
             'nom' =>'required|string|max:255',
             'Prenom' =>'required|string|max:255',
             'numTelephone' =>'required|string|max:255',
             'email' => 'required|string|email|unique:users,email,' . $id,
             'sexe' => 'required|string',
             'Adresse' => 'required|string',
             'structure_id' => 'nullable|exists:structures,id',


         ]);
     
         $user = User::find($id);
         if (!$user) {
             return response()->json(['message' => 'Utilisateur non trouvé'], 404);
         }
         $user->nom = $request->input('nom');
         $user->Prenom = $request->input('Prenom', null);
         $user->numTelephone = $request->input('numTelephone', null); 
         $user->email = $request->input('email', null); 
         $user->sexe = $request->input('sexe', null); 
         $user->Adresse = $request->input('Adresse', null);
         $user->structure_id = $request->input('structure_id'); 


         $user->save();
     
         return response()->json([
             'success' => true,
             'message' => 'Utilisateur mis à jour avec succès',
             'data' => $user
         ]);
     }
     
/**
 * Description: Supprime un référant en fonction de son identifiant.
 * Méthode: DELETE
 * Entrée: id (identifiant du référant)
 * Sortie: Message de confirmation de suppression + statut 200 en cas de succès, message d'erreur + statut 404 si le référant est introuvable.
 */
  
    public function destroyreferent($id)
    {
        $referant = User::where('role_id', 4)->findOrFail($id);
        $referant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Référant supprimé avec succès.',
        ]);
    }

/**
 * Description: Met à jour l'état (actif/inactif) d'un référant.
 * Méthode: PUT
 * Entrée: id (identifiant du référant)
 * Sortie: Message de confirmation de mise à jour de l'état + statut 200 en cas de succès, message d'erreur + statut 404 si le référant est introuvable.
 */
    public function updatereferantsetat($id)
    {
        $User = User::find($id); 
        if ($User) {
            if ($User->statut == 'Active') {
                $User->statut = 'Inactive';
            } elseif ($User->statut == 'Inactive') {
                $User->statut = 'Active';
            }
            
            $User->save(); 
            
            return response()->json(['message' => 'État mis à jour avec succès.'], 200);
        } else {
            return response()->json(['error' => 'referent introuvable.'], 404);
        }
    }
    





}
