<?php

namespace App\Http\Controllers;

use App\Mail\JeuneCreatedMail;
use App\Models\Jeune;
use App\Http\Requests\StoreJeuneRequest;
use App\Http\Requests\UpdateJeuneRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
                'avatar' => 'mimes:jpeg,png,jpg,gif',
                'nom' => 'nullable|string',
                'Prenom' => 'nullable|string',
                'email' => 'required|string|email|unique:users',
                'numTelephone' => 'required|string',
                'dateNaissance' => 'nullable|date',
                'role_id' => 'exists:roles,id',
                'sexe' => 'required|string',

            ]);

            // Création de l'utilisateur
            $user = new User();


            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = time() . '.' . $avatar->extension();
                $avatar->move(public_path('images'), $avatarName);
                $user->avatar = $avatarName;
            }

            $user->nom = $validatedData['nom'];
            $user->Prenom = $validatedData['Prenom'];
            $user->email = $validatedData['email'];
            $user->numTelephone = $validatedData['numTelephone'];
            $user->password = bcrypt('passer123');
            $user->dateNaissance = $validatedData['dateNaissance'];
            $user->statut = 'Active';
            $user->role_id = 2;
            $user->sexe = $validatedData['sexe'];
            $user->confirmation_token = Str::random(60);
            $user->save();

            // Envoi de l'email à l'utilisateur
            Mail::to($user->email)->send(new JeuneCreatedMail($user));



            return response()->json(['message' => 'Utilisateur créé avec succès.', 'user' => $user], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Retourner les erreurs de validation
            return response()->json(['message' => 'Erreur de validation.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            // Retourner les erreurs générales
            return response()->json(['message' => 'Une erreur est survenue lors de la création de l\'utilisateur.', 'error' => $e->getMessage()], 500);
        }
}


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
