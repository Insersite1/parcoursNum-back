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
     * Display a listing of the resource.
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
            $request->validate([
                'nom' => 'nullable|string|max:255',
                'session_code' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'session_id' => 'nullable|exists:sessions,id', // Vérifie si la session existe
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

            // Réponse en cas de succès
            return response()->json([
                'status' => 'success',
                'message' => 'Séance créée avec succès',
                'data' => $sceance
            ], 201);

        } catch (Exception $e) {
            // Gestion des erreurs
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
     * Show the form for editing the specified resource.
     */
    public function edit(Sceance $sceance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, $id)
    // {
    //     try {
    //         // Récupération de la séance à mettre à jour
    //         $sceance = Sceance::findOrFail($id);

    //         // Validation des données
    //         $request->validate([
    //             'nom' => 'required|string|max:255',
    //             'par' => 'nullable|string|max:255', // Champ facultatif
    //             'session_code' => 'required|string|max:255',
    //             'description' => 'nullable|string',
    //             'date_debut' => 'required|date',
    //             'date_fin' => 'required|date|after_or_equal:date_debut',
    //             'session_id' => 'nullable|exists:sessions,id', // Relation clé étrangère
    //             'couverture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         ]);

    //         // Mise à jour des champs texte et numériques
    //         $sceance->nom = $request->nom;
    //         $sceance->par = $request->par;
    //         $sceance->session_code = $request->session_code;
    //         $sceance->description = $request->description;
    //         $sceance->date_debut = $request->date_debut;
    //         $sceance->date_fin = $request->date_fin;
    //         $sceance->session_id = $request->session_id;

    //         // Gestion de la couverture
    //         if ($request->hasFile('couverture')) {
    //             // Suppression de l'ancienne image si elle existe
    //             if ($sceance->couverture) {
    //                 Storage::disk('public')->delete($sceance->couverture);
    //             }
    //             // Stockage de la nouvelle image
    //             $couverturePath = $request->file('couverture')->store('sceances/couvertures', 'public');
    //             $sceance->couverture = $couverturePath;
    //         }

    //         // Sauvegarde des modifications
    //         $sceance->save();

    //         // Réponse en cas de succès
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Séance mise à jour avec succès',
    //             'data' => $sceance
    //         ], 200);

    //     } catch (ModelNotFoundException $e) {
    //         // Réponse en cas de séance introuvable
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Séance introuvable',
    //         ], 404);
    //     } catch (Exception $e) {
    //         // Réponse en cas d'erreur générale
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Une erreur est survenue lors de la mise à jour de la séance.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function update(Request $request, $id)
    {
        try {
            // Récupération de la séance à mettre à jour
            $sceance = Sceance::findOrFail($id);

            // Validation des données
            $request->validate([
                'nom' => 'sometimes|required|string|max:255',
                'par' => 'nullable|string|max:255', // Champ facultatif
                'session_code' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'sometimes|required|date',
                'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
                'session_id' => 'nullable|exists:sessions,id', // Relation clé étrangère
            ]);

            // Mise à jour des champs texte et numériques
            $sceance->nom = $request->nom;
            $sceance->par = $request->par;
            $sceance->session_code = $request->session_code;
            $sceance->description = $request->description;
            $sceance->date_debut = $request->date_debut;
            $sceance->date_fin = $request->date_fin;
            $sceance->session_id = $request->session_id;

            // Gestion de la couverture
            if ($request->hasFile('couverture')) {
                // Suppression de l'ancienne image si elle existe
                if ($sceance->couverture) {
                    Storage::disk('public')->delete($sceance->couverture);
                }
                // Stockage de la nouvelle image
                $couverturePath = $request->file('couverture')->store('sceances/couverture', 'public');
                $sceance->couverture = $couverturePath;
            }

            // Sauvegarde des modifications
            $sceance->save();

            // Réponse en cas de succès
            return response()->json([
                'status' => 'success',
                'message' => 'Séance mise à jour avec succès',
                'data' => $sceance
            ], 200);

        } catch (ModelNotFoundException $e) {
            // Réponse en cas de séance introuvable
            return response()->json([
                'status' => 'error',
                'message' => 'Séance introuvable',
            ], 404);
        } catch (Exception $e) {
            // Réponse en cas d'erreur générale
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour de la séance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
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


    public function getSceanceBySessionID($session_id)
    {
        // Recherche toutes les séances associées à la session donnée
        $sceances = Sceance::where('session_id', $session_id)->get();

        // Vérifie si aucune séance n'a été trouvée pour cette session
        if ($sceances->isEmpty()) {
            return response()->json(['message' => 'Aucune séance trouvée pour cette session'], 404);
        }

        // Retourne les séances trouvées au format JSON
        return response()->json($sceances);
    }
}
