<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use App\Models\Session;
use App\Models\Structure;
use App\Models\StructureDispositif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StructureController extends Controller
{
    /**
     * Description: Récupérer toutes les structures.
     * Méthode: GET
     * Sortie: Liste de toutes les structures avec un status 200.
     */
    public function index()
    {
        $structures = Structure::all();
        return response()->json($structures, 200);
    }

    /**
     * Description: Créer une nouvelle structure.
     * Méthode: POST
     * Entrée: nomcomplet, dateExpire, statut, couverture (Photo).
     * Sortie: Nouvelle structure créée avec status 201 en cas de succès,
     * message d'erreur + status 422 en cas d'échec.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomcomplet' => 'required|string|max:255',
            'dateExpire' => 'required|date',
            'statut' => 'required|in:Active,Inactive',
            'couverture' => 'nullable|mimes:jpg,jpeg,png,gif',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $avatarName = null;
        $structure=new Structure();
        if ($request->hasFile('couverture')) {
            $couverture = $request->file('couverture');
            $avatarName = time() . '.' . $couverture->extension();
            $couverture->move(public_path('images'), $avatarName);
             $structure->couverture = $avatarName;
        }
        $structure = Structure::create([
            'nomcomplet' => $request->nomcomplet,
            'dateExpire' => $request->dateExpire,
            'statut' => $request->statut,
            'couverture' => $avatarName,
        ]);

        return response()->json($structure, 201);
    }

    /**
     * Description: Récupérer une structure par son ID.
     * Méthode: GET
     * Entrée: ID de la structure.
     * Sortie: Structure trouvée avec un status 200,
     * ou message d'erreur avec status 404 si non trouvée.
     */
    public function show($id)
    {
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure non trouvée'], 404);
        }

        return response()->json($structure, 200);
    }

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





    /**
     * Description: Ajouter un dispositif à une structure.
     * Méthode: POST
     * Entrée: dispositif_id (ID d'un dispositif existant).
     * Sortie: Message de confirmation avec status 201 en cas de succès,
     * ou message d'erreur avec status 409 si le dispositif est déjà associé.
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


    /**
 * Description: Récupérer la liste des jeunes associés à une structure.
 * Méthode: GET
 * Entrée: ID de la structure.
 * Sortie: Liste des jeunes avec un status 200,
 * ou message d'erreur avec status 404 si la structure n'est pas trouvée.
 */
public function getJeuneByStructureID($structureId)
{
    // Vérifier si la structure existe
    $structure = Structure::find($structureId);

    if (!$structure) {
        return response()->json(['message' => 'Structure non trouvée'], 404);
    }

    // Récupérer les jeunes associés à la structure
    $jeunes = $structure->users()
        ->whereHas('role', function ($query) {
            $query->where('name', 'Jeune'); // Filtre par le nom du rôle
        })
        ->get();

    return response()->json([
        'message' => 'Liste des jeunes pour la structure donnée',
        'data' => $jeunes
    ], 200);
}
/**
 * Description: Récupérer la liste des référents associés à une structure.
 * Méthode: GET
 * Entrée: ID de la structure.
 * Sortie: Liste des référents avec un status 200,
 * ou message d'erreur avec status 404 si la structure n'est pas trouvée.
 */
public function getReferantByStructureID($structureId)
{
    // Vérifier si la structure existe
    $structure = Structure::find($structureId);

    if (!$structure) {
        return response()->json(['message' => 'Structure non trouvée'], 404);
    }

    // Récupérer les référents associés à la structure
    $referants = $structure->users()
        ->whereHas('role', function ($query) {
            $query->where('name', 'Referent'); // Filtre par le nom du rôle
        })
        ->with('referant') // Charger les détails du référent
        ->get();

    return response()->json([
        'message' => 'Liste des référents pour la structure donnée',
        'data' => $referants
    ], 200);
}
/**
 * Description: Récupérer la liste des actions associées à une structure.
 * Méthode: GET
 * Entrée: ID de la structure.
 * Sortie: Liste des actions avec un status 200,
 * ou message d'erreur avec status 404 si la structure n'est pas trouvée.
 */
public function getActionsByStructure($structureId)
{
    // Vérifier si la structure existe
    $structure = Structure::find($structureId);

    if (!$structure) {
        return response()->json(['message' => 'Structure non trouvée'], 404);
    }

    // Récupérer les actions associées à la structure
    $actions = $structure->actions;

    return response()->json([
        'message' => 'Liste des actions pour la structure donnée',
        'data' => $actions
    ], 200);
}
/**
 * Description: Ajouter une action à une structure.
 * Méthode: POST
 * Entrée: action_id (ID d'une action existante).
 * Sortie: Message de confirmation avec status 201 en cas de succès,
 * ou message d'erreur avec status 409 si l'action est déjà associée.
 */
public function addActionToStructure(Request $request, $structureId)
{
    // Valider les données entrantes
    $validated = $request->validate([
        'action_id' => 'required|exists:actions,id', // Vérifie que l'action existe
    ]);

    // Vérifier que la structure existe
    $structure = Structure::findOrFail($structureId);

    // Vérifier si l'association existe déjà
    $exists = $structure->actions()->where('action_id', $validated['action_id'])->exists();

    if ($exists) {
        return response()->json([
            'message' => 'L\'action est déjà associée à cette structure.',
        ], 409); // Code 409 : Conflit
    }

    // Ajouter l'action à la structure
    $structure->actions()->attach($validated['action_id']);

    return response()->json([
        'message' => 'Action ajoutée avec succès à la structure.',
    ], 201); // Code 201 : Ressource créée
}
}
