<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Models\Sondage;
use App\Models\Question;
use App\Http\Requests\StoreSondageRequest;
use App\Http\Requests\UpdateSondageRequest;


class SondageController extends Controller
{
/**
 * Description: Récupère la liste de tous les sondages avec leurs questions associées.
 * Méthode: GET
 * Entrée: Aucune
 * Sortie: Liste des sondages avec leurs questions avec statut 200 en cas de succès.
 */
    public function index()
    {
        $sondages = Sondage::with('questions')->get();
        return response()->json($sondages);
    }
/**
 * Description: Crée un nouveau sondage avec ses questions associées.
 * Méthode: POST
 * Entrée: titre, description, date_debut, date_fin, est_publie, pour_tous_utilisateurs, questions (tableau d'objets avec titre_question, type_question, options, obligatoire)
 * Sortie: Le sondage créé avec statut 201 en cas de succès.
 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'est_publie' => 'boolean',
            'pour_tous_utilisateurs' => 'boolean',
            'questions' => 'required|array',
            'questions.*.titre_question' => 'required|string',
            'questions.*.type_question' => 'required|in:texte_court,choix_unique,choix_multiple',
            'questions.*.options' => 'nullable|array',
            'questions.*.obligatoire' => 'boolean'
        ]);
    
        try {
            DB::beginTransaction();
    
            $sondage = Sondage::create([
                ...$validated,
            ]);
    
            foreach ($request->questions as $questionData) {
                $sondage->questions()->create($questionData);
            }
    
            DB::commit();
            return response()->json($sondage->load('questions'), 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du sondage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
 * Description: Met à jour un sondage existant et ses questions associées.
 * Méthode: PUT
 * Entrée: titre, description, date_debut, date_fin, est_public, pour_tous_utilisateurs, statut, questions (tableau d'objets avec titre_question, type_question, options, obligatoire)
 * Sortie: Le sondage mis à jour avec statut 200 en cas de succès.
 */
    public function update(Request $request, Sondage $sondage)
    {
        $validated = $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date|after:date_debut',
            'est_public' => 'boolean',
            'pour_tous_utilisateurs' => 'boolean',
            'statut' => 'in:actif,inactif',
            'questions' => 'sometimes|required|array',
            'questions.*.titre_question' => 'required|string',
            'questions.*.type_question' => 'required|in:texte_court,choix_unique,choix_multiple',
            'questions.*.options' => 'nullable|array',
            'questions.*.obligatoire' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            $sondage->update($validated);
            if ($request->has('questions')) {
                $sondage->questions()->delete();
                foreach ($request->questions as $questionData) {
                    $sondage->questions()->create($questionData);
                }
            }

            DB::beginTransaction();

            return response()->json([
                'message' => 'Sondage mis à jour avec succès',
                'sondage' => $sondage->fresh('questions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du sondage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

/**
 * Description: Récupère les détails d'un sondage spécifique avec ses questions associées.
 * Méthode: GET
 * Entrée: Sondage (modèle de sondage)
 * Sortie: Détails du sondage avec statut 200 en cas de succès.
 */
    public function show(Sondage $sondage)
    {
        $sondage->load('questions');

        return response()->json([
            'message' => 'Détails du sondage récupérés avec succès.',
            'sondage' => $sondage,
        ], 200);
    }
/**
 * Description: Supprime un sondage si l'utilisateur est autorisé à le faire.
 * Méthode: DELETE
 * Entrée: Sondage (modèle de sondage)
 * Sortie: Message de confirmation de suppression avec statut 200 en cas de succès, ou message de non autorisation avec statut 403.
 */
    public function destroy(Sondage $sondage)
    {
        if ($sondage->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        try {
            DB::beginTransaction();

            $sondage->delete();

            DB::commit();

            return response()->json([
                'message' => 'Sondage supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression du sondage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
 * Description: Change le statut (actif/inactif) d'un sondage.
 * Méthode: PUT
 * Entrée: statut (actif/inactif)
 * Sortie: Le sondage avec le statut mis à jour avec statut 200 en cas de succès.
 */
    public function changeStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|in:actif,inactif', 
        ]);

        $sondage = Sondage::find($id);

        if (!$sondage) {
            return response()->json(['message' => 'Sondage non trouvée.'], 404);
        }

        $sondage->statut = $validated['statut'];
        $sondage->save();

        return response()->json([
            'message' => 'Statut de la sondage mis à jour avec succès.',
            'sondage' => $sondage,
        ], 200);
    }

   
}
