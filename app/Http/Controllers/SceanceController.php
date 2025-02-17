<?php

namespace App\Http\Controllers;

use App\Models\Sceance;
use App\Http\Requests\StoreSceanceRequest;
use App\Http\Requests\UpdateSceanceRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class SceanceController extends Controller
{

/**
 * Description: Récupère la liste de toutes les séances avec leurs sessions associées.
 * Méthode: GET
 * Entrée: Aucune
 * Sortie: Liste des séances avec statut 201 en cas de succès.
 */
    public function index()
    {
        $sceances = Sceance::with('session')->get();
        return response()->json([
            'status' => 'success',
            'data' => $sceances
        ], 201);
    }
/**
 * Description: Crée une nouvelle séance.
 * Méthode: POST
 * Entrée: nom, session_code, description, date_debut, date_fin, session_id, couverture (facultatif)
 * Sortie: La séance créée avec statut 201 en cas de succès, message d'erreur + statut 500 en cas d'échec.
 */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'nullable|string|max:255',
                'session_code' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'session_id' => 'nullable|exists:sessions,id', 
                'couverture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Gestion de l'upload de l'image de couverture
            $couverturePath = null;
            if ($request->hasFile('couverture')) {
                $couverturePath = $request->file('couverture')->store('sceances/couverture', 'public');
            }

            // Création de la nouvelle séance
            $sceance = new Sceance();
            $sceance->nom = $request->nom;
            $sceance->par = $request->par;; 
            $sceance->session_code = $request->session_code;
            $sceance->description = $request->description;
            $sceance->date_debut = $request->date_debut;
            $sceance->date_fin = $request->date_fin;
            $sceance->session_id = $request->session_id;
            $sceance->couverture = $couverturePath;

            $sceance->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Séance créée avec succès',
                'data' => $sceance
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la création de la séance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sceance = Sceance::with('session')->find($id);
        if (!$sceance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Séance introuvable'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $sceance
        ], 200);
    }
/**
 * Description: Met à jour une séance existante.
 * Méthode: PUT
 * Entrée: id (identifiant de la séance), nom, par, session_code, description, date_debut, date_fin, session_id, couverture (facultatif)
 * Sortie: Message de confirmation de mise à jour de la séance avec statut 200 en cas de succès, message d'erreur + statut 404 ou 500 en cas d'échec.
 */
    public function update(Request $request, $id)
    {
        try {
            $sceance = Sceance::findOrFail($id);
            $request->validate([
                'nom' => 'sometimes|required|string|max:255',
                'par' => 'nullable|string|max:255', 
                'session_code' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'sometimes|required|date',
                'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
                'session_id' => 'nullable|exists:sessions,id', 
            ]);

            $sceance->nom = $request->nom;
            $sceance->par = $request->par;
            $sceance->session_code = $request->session_code;
            $sceance->description = $request->description;
            $sceance->date_debut = $request->date_debut;
            $sceance->date_fin = $request->date_fin;
            $sceance->session_id = $request->session_id;

            if ($request->hasFile('couverture')) {
                if ($sceance->couverture) {
                    Storage::disk('public')->delete($sceance->couverture);
                }
                $couverturePath = $request->file('couverture')->store('sceances/couverture', 'public');
                $sceance->couverture = $couverturePath;
            }
            $sceance->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Séance mise à jour avec succès',
                'data' => $sceance
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Séance introuvable',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour de la séance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

/**
 * Description: Supprime une séance existante.
 * Méthode: DELETE
 * Entrée: id (identifiant de la séance)
 * Sortie: Message de confirmation de suppression de la séance avec statut 200 en cas de succès, message d'erreur + statut 404 si la séance n'est pas trouvée.
 */
    public function destroy($id)
    {
        $sceance = Sceance::find($id);
        if (!$sceance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Séance introuvable'
            ], 404);
        }

        if ($sceance->couverture) {
            Storage::disk('public')->delete($sceance->couverture);
        }

        $sceance->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Séance supprimée avec succès'
        ], 200);
    }
}
