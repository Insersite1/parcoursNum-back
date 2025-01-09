<?php

namespace App\Http\Controllers;

use App\Models\Jeune;
use App\Http\Requests\StoreJeuneRequest;
use App\Http\Requests\UpdateJeuneRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class JeuneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::with('role', 'structure')->get();
            return response()->json($users);
        } catch (Exception $e) {
            return response()->json($e->getMessage());

        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            // Validation des données
            $validatedData = $request->validate([
                // 'avatar' => 'mimes:jpeg,png,jpg,gif',
                'nom' => 'nullable|string',
                'Prenom' => 'nullable|string',
                'email' => 'required|string|email|unique:users',
                'numTelephone' => 'required|string',
                'password' => 'required',
                'Adresse' => 'nullable|string',
                'bibiographie' => 'nullable',
                'dateNaissance' => 'nullable|date',
                'codePostal' => 'nullable|string',
                'region' => 'nullable|string',
                'ville' => 'nullable|string',
                'role_id' => 'required|exists:roles,id',
                'NumSecuriteSocial'=>'required|'
            ]);

            // Création de l'utilisateur
            $user = new User();
            
            // $user->avatar = $request->file('avatar') ? $request->file('avatar')->store('avatars', 'public') : null;
            $user->nom = $validatedData['nom'];
            $user->Prenom = $validatedData['Prenom'];
            $user->email = $validatedData['email'];
            $user->numTelephone = $validatedData['numTelephone'];
            $user->password = bcrypt('passer123');
            $user->Adresse = $validatedData['Adresse'];
            $user->bibiographie = $validatedData['bibiographie'];
            $user->dateNaissance = $validatedData['dateNaissance'];
            $user->codePostal = $validatedData['codePostal'];
            $user->region = $validatedData['region'];
            $user->ville = $validatedData['ville'];
            $user->statut = 'Active';
            $user->NumSecuriteSocial = $validatedData['NumSecuriteSocial'];
            $user->role_id = 2;

            $user->save();

            return response()->json(['message' => 'Utilisateur créé avec succès.', 'user' => $user], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Retourner les erreurs de validation
            return response()->json(['message' => 'Erreur de validation.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Retourner les erreurs générales
            return response()->json(['message' => 'Une erreur est survenue lors de la création de l\'utilisateur.', 'error' => $e->getMessage()], 500);
        }
}




    /**
     * Display the specified resource.
     */
    public function show(Jeune $jeune)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jeune $jeune)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJeuneRequest $request, Jeune $jeune)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jeune $jeune)
    {
        //
    }
}
