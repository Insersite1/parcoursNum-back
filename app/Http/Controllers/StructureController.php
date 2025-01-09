<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use App\Models\Structure;
use App\Models\StructureDispositif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StructureController extends Controller
{
    // Récupérer toutes les structures
    public function index()
    {
        $structures = Structure::all();
        return response()->json($structures, 200);
    }

    // Créer une nouvelle structure
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomcomplet' => 'required|string|max:255',
            'dateExpire' => 'required|date',
            'statut' => 'required|in:Active,Inactive',
            'couverture' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $structure = Structure::create([
            'nomcomplet' => $request->nomcomplet,
            'dateExpire' => $request->dateExpire,
            'statut' => $request->statut,
            'couverture' => $request->couverture,
        ]);
        return response()->json($structure, 201);
    }

    // Récupérer une structure par son ID
    public function show($id)
    {
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure non trouvé'], 404);
        }

        return response()->json($structure, 200);
    }

    /**
     * Ajouter un dispositif à une structure.
     */
    public function addDispositifStructure(Request $request, $structureId)
    {
        // Valider les données entrantes
        $validated = $request->validate([
            'dispositif_id' => 'required|exists:dispositifs,id', // Vérifie que le dispositif existe
        ]);

        // Vérifie que la structure existe
        $structure = Structure::findOrFail($structureId);

        // Vérifier si l'association existe déjà
        $exists = StructureDispositif::where('structure_id', $structureId)
            ->where('dispositif_id', $validated['dispositif_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Le dispositif est déjà associé à cette structure.',
            ], 409); // Code 409 : Conflit
        }

        // Créer l'association
        $structureDispositif = StructureDispositif::create([
            'structure_id' => $structureId,
            'dispositif_id' => $validated['dispositif_id'],
        ]);

        return response()->json([
            'message' => 'Dispositif ajouté avec succès à la structure.',
            'data' => $structureDispositif,
        ], 201); // Code 201 : Ressource créée
    }


    // Récupérer les dispositifs d'une structure

    public function getDispositifsForStructure($structureId)
    {
        // Vérifier si la structure existe
        $structure = Structure::find($structureId);

        if (!$structure) {
            return response()->json(['error' => 'Structure not found'], 404);
        }

        // Récupérer les dispositifs via la table de jonction
        $dispositifs = StructureDispositif::where('structure_id', $structureId)
            ->join('dispositifs', 'structure_dispositifs.dispositif_id', '=', 'dispositifs.id')
            ->select('dispositifs.*') // Sélectionner uniquement les colonnes de la table dispositifs
            ->get();

        // Vérifier si des dispositifs existent pour cette structure
        if ($dispositifs->isEmpty()) {
            return response()->json([
                'message' => 'Aucun dispositif trouvé pour cette structure.',
                'data' => []
            ], 200);
        }

        // Retourner la réponse avec la liste des dispositifs
        return response()->json([
            'message' => 'Liste des dispositifs pour la structure donnée',
            'data' => $dispositifs
        ], 200);
    }


}
