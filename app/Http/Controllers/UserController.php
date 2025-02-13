<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
 /**
 * Description: Crée un nouvel utilisateur.
 * Méthode: POST
 * Entrée: nom, Prenom, email, password, numTelephone, role_id, Adresse (facultatif), dateNaissance (facultatif)
 * Sortie: L'utilisateur créé avec statut 201 en cas de succès.
 */ 
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'Prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'numTelephone' => 'required|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'Adresse' => 'nullable|string|max:255',
            'dateNaissance' => 'nullable|date',

        ]);
        $user = User::create($request->all());
        return response()->json($user, 201);
    }

}
