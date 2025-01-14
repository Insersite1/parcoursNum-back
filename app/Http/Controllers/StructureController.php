<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use App\Models\Session;
use App\Models\Structure;
use App\Models\StructureDispositif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StructureController extends Controller
{
  /*
  Description: Récupérer toutes les structures
  */
    public function index()
    {
        $structures = Structure::all();
        return response()->json($structures, 200);
    }

    // Créer une nouvelle structure
  /*  public function store(Request $request)
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
            'couverture' => 'photo',
        ]);
        return response()->json($structure, 201);
    }*/
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomcomplet' => 'required|string|max:255',
            'dateExpire' => 'required|date',
            'statut' => 'required|in:Active,Inactive',
            'couverture' => 'nullable|image|max:2048', // Assurez-vous que 'couverture' est un fichier image valide
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['nomcomplet', 'dateExpire', 'statut']);

        // Gestion de la couverture (image)
        if ($request->hasFile('couverture')) {
            $data['couverture'] = $request->file('couverture')->store('couvertures', 'public');
        }

        $structure = Structure::create($data);

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

    public function update(Request $request, Structure $structure)
    {
        $validator = Validator::make($request->all(), [
            'nomcomplet' => 'sometimes|required|string|max:255',
            'dateExpire' => 'sometimes|required|date',
            'statut' => 'sometimes|required|in:Active,Inactive',
            'couverture' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['nomcomplet', 'dateExpire', 'statut']);

        // Gestion de la couverture
        if ($request->hasFile('couverture')) {
            // Supprimer l'ancienne couverture si elle existe
            if ($structure->couverture) {
                Storage::disk('public')->delete($structure->couverture);
            }
            $data['couverture'] = $request->file('couverture')->store('couvertures', 'public');
        }

        $structure->update($data);

        return response()->json($structure);
    }
    //Supprimer Dispositif
    public function destroy($id)
    {
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Dispositif non trouvé'], 404);
        }

        $structure->delete();

        return response()->json(['message' => 'Dispositif supprimé avec succès'], 200);
    }


    /*Fonction de recherche*/
    public function search($search)
    {
        $query = Structure::query();

        $query->where('nomcomplet', 'LIKE', '%' . $search . '%')
            ->orWhere('statut', 'LIKE', '%' . $search . '%')
            ->orWhere(function ($dateQuery) use ($search) {
                $dateQuery->where('dateExpire', 'LIKE', '%' . $search . '%');
            });

        $structures = $query->get();

        return response()->json($structures);
    }

    /*Fonction qui permet de mettre a jour le statut de structure*/
    public function changeStatus(Request $request, $id)
    {
        // Valider le statut fourni
        $validated = $request->validate([
            'statut' => 'required|in:Active,Inactive', // Assure que le statut est soit "Active" soit "Inactive"
        ]);

        // Trouver la structure par son ID
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure non trouvée.'], 404);
        }

        // Mettre à jour le statut
        $structure->statut = $validated['statut'];
        $structure->save();

        return response()->json([
            'message' => 'Statut de la structure mis à jour avec succès.',
            'structure' => $structure,
        ], 200);
    }

}
