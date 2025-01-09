<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Http\Requests\StoreSessionRequest;
use App\Http\Requests\UpdateSessionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;


class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sessions = Session::with(['user', 'action'])->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $sessions
        ]);
        //
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
            // Validation
            $request->validate([
                'nom' => 'required|string',
                'description' => 'nullable|string', // nullable au lieu de required
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'file' => 'nullable|mimes:pdf,doc,docx|max:2048',
                'action_id' => 'nullable|exists:actions,id'
            ]);

            $session = new Session();

            // Assignation des champs de base
            $session->nom = $request->nom;
            $session->description = $request->description;
            $session->date_debut = $request->date_debut;
            $session->date_fin = $request->date_fin;
            $session->action_id = $request->action_id;

            // Gestion de l'image
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('sessions/images', 'public');
                $session->image = $imagePath;
            }

            // Gestion du fichier
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('sessions/files', 'public');
                $session->file = $filePath;
            }

            $session->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Session créée avec succès',
                'data' => $session
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'erreur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Récupérer la session avec ses relations
        $session = Session::with('user', 'action')->find($id);

        // Vérifier si la session existe
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Séance introuvable'
            ], 404);
        }

        // Retourner la session trouvée
        return response()->json([
            'status' => 'success',
            'data' => $session
        ], 200);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Session $session)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Récupération de la session ou erreur 404 si non trouvée
            $session = Session::findOrFail($id);

            // Validation des données
            $request->validate([
                'nom' => 'required|string',
                'description' => 'nullable|string', // Nullable comme dans store
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut', // Cohérence avec store
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'file' => 'nullable|mimes:pdf,doc,docx|max:2048',
                'action_id' => 'nullable|exists:actions,id'
            ]);

            // Mise à jour des champs de base
            $session->nom = $request->nom;
            $session->description = $request->description;
            $session->date_debut = $request->date_debut;
            $session->date_fin = $request->date_fin;
            $session->action_id = $request->action_id;

            // Gestion de l'image
            if ($request->hasFile('image')) {
                // Suppression de l'ancienne image si elle existe
                if ($session->image) {
                    Storage::disk('public')->delete($session->image);
                }
                $imagePath = $request->file('image')->store('sessions/images', 'public');
                $session->image = $imagePath;
            }

            // Gestion du fichier
            if ($request->hasFile('file')) {
                // Suppression de l'ancien fichier si existant
                if ($session->file) {
                    Storage::disk('public')->delete($session->file);
                }
                $filePath = $request->file('file')->store('sessions/files', 'public');
                $session->file = $filePath;
            }

            // Sauvegarde des modifications
            $session->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Session mise à jour avec succès',
                'data' => $session
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $session = Session::findOrFail($id);

            // Suppression des fichiers
            if ($session->image) {
                Storage::disk('public')->delete($session->image);
            }
            if ($session->file) {
                Storage::disk('public')->delete($session->file);
            }

            $session->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Session supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}
