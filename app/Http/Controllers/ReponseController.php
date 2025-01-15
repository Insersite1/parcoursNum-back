<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;
use App\Models\Reponse;
use App\Models\Sondage;
use App\Models\Question;


class ReponseController extends Controller
{
    //
    /**
     * Enregistrer une réponse à une question
     */
    public function store(Request $request)
    {
        // Vérification que l'utilisateur est propriétaire de la réponse
        /*$user = auth()->user();
        if (!$user || $user->role !== 'Jeune') {
            return response()->json(['error' => 'Accès non autorisé. Seul le Référent peut créer des sondages.'], 403);
        }*/
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'texte_reponse' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $reponse = Reponse::create([
                ...$validated,
                //'user_id' => auth()->id()
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
     * lister toutes les réponses d'un sondage
     */
    public function getReponsesSondage(Sondage $sondage)
    {
        $reponses = Reponse::whereHas('question', function($query) use ($sondage) {
            $query->where('sondage_id', $sondage->id);
        })->with(['question', 'user'])->get();

        return response()->json($reponses);
    }

    /**
     * Met à jour une réponse
     * @param Request $request
     * @param Reponse $reponse
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Reponse $reponse)
    {
        // Vérification que l'utilisateur est propriétaire de la réponse
        /*$user = auth()->user();
        if (!$user || $user->role !== 'Jeune') {
            return response()->json(['error' => 'Accès non autorisé. Seul le Référent peut créer des sondages.'], 403);
        }*/

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
     * Affiche les détails d'une réponse spécifique
     * 
     * @param Reponse $reponse
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Reponse $reponse)
    {
        // Charger les relations associées (par exemple, question et utilisateur)
        $reponse->load(['question', 'user']);

        return response()->json([
            'message' => 'Détails de la réponse récupérés avec succès.',
            'reponse' => $reponse
        ], 200);
    }

}
