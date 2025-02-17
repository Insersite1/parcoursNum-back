<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;
use App\Models\Reponse;
use App\Models\Sondage;
use App\Models\Question;


class ReponseController extends Controller
{
/**
 * Description: Crée une nouvelle réponse pour une question donnée.
 * Méthode: POST
 * Entrée: question_id (identifiant de la question), texte_reponse (texte de la réponse)
 * Sortie: La réponse créée + statut 201 en cas de succès, message d'erreur + statut 500 en cas d'échec.
 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'texte_reponse' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $reponse = Reponse::create([
                ...$validated,
            ]);

            DB::commit();

            return response()->json($reponse, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement de la réponse',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
 * Description: Récupère toutes les réponses associées à un sondage.
 * Méthode: GET
 * Entrée: sondage (l'instance du sondage dont les réponses doivent être récupérées)
 * Sortie: Liste des réponses avec leurs questions et utilisateurs associés + statut 200 en cas de succès.
 */
    public function getReponsesSondage(Sondage $sondage)
    {
        $reponses = Reponse::whereHas('question', function($query) use ($sondage) {
            $query->where('sondage_id', $sondage->id);
        })->with(['question', 'user'])->get();

        return response()->json($reponses);
    }
/**
 * Description: Met à jour une réponse existante.
 * Méthode: PUT
 * Entrée: reponse (l'instance de la réponse à mettre à jour), texte_reponse (texte de la réponse mis à jour)
 * Sortie: Message de confirmation de mise à jour de la réponse + statut 200 en cas de succès, message d'erreur + statut 500 en cas d'échec.
 */
    public function update(Request $request, Reponse $reponse)
    {
        $validated = $request->validate([
            'texte_reponse' => 'required|string'
        ]);

        try {

            DB::beginTransaction();

            $reponse->update($validated);

            DB::commit();


            return response()->json([
                'message' => 'Réponse mise à jour avec succès',
                'reponse' => $reponse
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la réponse',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
 * Description: Récupère les détails d'une réponse spécifique.
 * Méthode: GET
 * Entrée: reponse (l'instance de la réponse à afficher)
 * Sortie: Détails de la réponse, question associée et utilisateur + statut 200 en cas de succès.
 */
    public function show(Reponse $reponse)
    {
        $reponse->load(['question', 'user']);

        return response()->json([
            'message' => 'Détails de la réponse récupérés avec succès.',
            'reponse' => $reponse
        ], 200);
    }

}
