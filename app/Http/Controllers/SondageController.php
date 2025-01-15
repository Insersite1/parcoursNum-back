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
     * Liste tous les sondages
     */
    public function index()
    {
        $sondages = Sondage::with('questions')->get();
        return response()->json($sondages);
    }

    /**
     * Créer un nouveau sondage
     */
    public function store(Request $request)
    {
        // Vérification du rôle de l'utilisateur connecté
        /*$user = auth()->user();
        if (!$user || $user->role !== 'Referent') {
            return response()->json(['error' => 'Accès non autorisé. Seul le Référent peut créer des sondages.'], 403);
        }*/
    
        // Validation des données reçues
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
                //'user_id' => $user->id
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
     * Met à jour un sondage existant
     * @param Request $request
     * @param Sondage $sondage
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Sondage $sondage)
    {
        // Vérification que l'utilisateur est propriétaire du sondage
        /*$user = auth()->user();
        if (!$user || $user->role !== 'Referent') {
            return response()->json(['error' => 'Accès non autorisé. Seul le Référent peut créer des sondages.'], 403);
        }*/

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

            // Mise à jour du sondage
            $sondage->update($validated);

            // Si des questions sont fournies, mettre à jour les questions
            if ($request->has('questions')) {
                // Supprimer les anciennes questions
                $sondage->questions()->delete();

                // Créer les nouvelles questions
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
     * Affiche les détails d'un sondage spécifique
     * @param Sondage $sondage
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Sondage $sondage)
    {
        // Charger les relations associées (questions dans ce cas)
        $sondage->load('questions');

        return response()->json([
            'message' => 'Détails du sondage récupérés avec succès.',
            'sondage' => $sondage,
        ], 200);
    }


    /**
     * Supprime un sondage
     * @param Sondage $sondage
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Sondage $sondage)
    {
        // Vérification que l'utilisateur est propriétaire du sondage
        if ($sondage->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        try {
            DB::beginTransaction();

            // Suppression du sondage (les questions et réponses seront supprimées automatiquement grâce à onDelete('cascade'))
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

    /*Fonction qui permet de mettre a jour le statut de sondage*/
    public function changeStatus(Request $request, $id)
    {
        // Valider le statut fourni
        $validated = $request->validate([
            'statut' => 'required|in:actif,inactif', // Assure que le statut est soit "Active" soit "Inactive"
        ]);

        // Trouver la sondage par son ID
        $sondage = Sondage::find($id);

        if (!$sondage) {
            return response()->json(['message' => 'Sondage non trouvée.'], 404);
        }

        // Mettre à jour le statut
        $sondage->statut = $validated['statut'];
        $sondage->save();

        return response()->json([
            'message' => 'Statut de la sondage mis à jour avec succès.',
            'sondage' => $sondage,
        ], 200);
    }

    

    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


   

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sondage $sondage)
    {
        //
    }

   
}
