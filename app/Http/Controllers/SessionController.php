<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SessionController extends Controller
{
    // Affiche toutes les sessions
    public function index()
    {
        $sessions = Session::with('action')->get();
        return response()->json($sessions);
    }

    // Crée une nouvelle session
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'file' => 'nullable|file|max:2048',
            'par' => 'required|string|max:255', // Nouveau champ obligatoire
            'action_id' => 'nullable|exists:actions,id',
        ]);

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('images', 'public');
        }

        // Gestion du fichier
        if ($request->hasFile('file')) {
            $validatedData['file'] = $request->file('file')->store('files', 'public');
        }

        $session = Session::create($validatedData);

        return response()->json($session, 201);
    }

    // Affiche une session spécifique
    public function show(Session $session)
    {
        return response()->json($session->load('action'));
    }

    // Met à jour une session
    public function update(Request $request, Session $session)
    {
        $validatedData = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|image|max:2048',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
            'file' => 'nullable|file|max:2048',
            'par' => 'sometimes|required|string|max:255',
            'action_id' => 'nullable|exists:actions,id',
        ]);

        // Gestion de l'image
        if ($request->hasFile('image')) {
            if ($session->image) {
                Storage::disk('public')->delete($session->image);
            }
            $validatedData['image'] = $request->file('image')->store('images', 'public');
        }
        if ($request->hasFile('file')) {
            if ($session->file) {
                Storage::disk('public')->delete($session->file);
            }
            $validatedData['file'] = $request->file('file')->store('files', 'public');
        }

        $session->update($validatedData);

        return response()->json($session);
    }

    // Met à jour les upload une session
    // avec la methode post
    public function uploadFile(Request $request, Session $session)
    {
        // Validation des fichiers
        $validatedData = $request->validate([
            'image' => 'nullable|image|max:2048', // Validation pour les images
            'file' => 'nullable|file|max:2048',  // Validation pour d'autres fichiers
        ]);

        // Sauvegarde de l'image si elle est fournie
        if ($request->hasFile('image')) {
            $previousImage = $session->image;

            // Supprimer l'ancienne image si elle existe
            if ($previousImage) {
                Storage::disk('public')->delete($previousImage);
            }

            // Sauvegarder la nouvelle image
            $validatedData['image'] = $request->file('image')->store('images', 'public');
        }

        // Sauvegarde du fichier si fourni
        if ($request->hasFile('file')) {
            $previousFile = $session->file;

            // Supprimer l'ancien fichier si existant
            if ($previousFile) {
                Storage::disk('public')->delete($previousFile);
            }

            // Sauvegarder le nouveau fichier
            $validatedData['file'] = $request->file('file')->store('files', 'public');
        }

        // Mise à jour de la session avec les nouveaux fichiers
        $session->update($validatedData);

        return response()->json([
            'message' => 'Fichiers mis à jour avec succès',
            'session' => $session,
        ]);
    }


    // Supprime une session
    public function destroy(Session $session)
    {
        if ($session->image) {
            Storage::disk('public')->delete($session->image);
        }
        if ($session->file) {
            Storage::disk('public')->delete($session->file);
        }

        $session->delete();

        return response()->json(['message' => 'Session supprimée avec succès']);
    }

    //Rechercher une session
    /*public function search(Request $request)
    {
        $query = Session::query();

        // Recherche par nom
        if ($request->has('nom') && !empty($request->nom)) {
            $query->where('nom', 'LIKE', '%' . $request->nom . '%');
        }

        // Recherche par 'par'
        if ($request->has('par') && !empty($request->par)) {
            $query->where('par', 'LIKE', '%' . $request->par . '%');
        }

        // Recherche par date
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('date_debut', '<=', $request->date)
                ->whereDate('date_fin', '>=', $request->date);
        }

        // Recherche par action
        if ($request->has('action') && !empty($request->action)) {
            $query->whereHas('action', function ($subQuery) use ($request) {
                $subQuery->where('nom', 'LIKE', '%' . $request->action . '%'); // Assurez-vous que la table 'actions' a une colonne 'nom'
            });
        }

        // Obtenez les résultats avec les relations
       // $sessions = $query->with('action')->get();

        return response()->json($sessions);
    }*/

    public function search($search)
    {
        $query = Session::query();

        // Recherche dans les champs 'nom', 'par', et via la relation 'action'
        $query->where('nom', 'LIKE', '%' . $search . '%')
            ->orWhere('par', 'LIKE', '%' . $search . '%')
            ->orWhereHas('action', function ($subQuery) use ($search) {
                $subQuery->where('nom', 'LIKE', '%' . $search . '%'); // Recherche dans le champ 'nom' de la table 'actions'
            })
            ->orWhere(function ($dateQuery) use ($search) {
                // Recherche par date_debut ou date_fin
                $dateQuery->where('date_debut', 'LIKE', '%' . $search . '%')
                            ->orWhere('date_fin', 'LIKE', '%' . $search . '%');
            });

        // Obtenez les résultats avec les relations
        $sessions = $query->with('action')->get();

        return response()->json($sessions);
    }



}

