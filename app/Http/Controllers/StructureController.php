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
/**
 * Description: Modifie le statut d'une structure (Active/Inactive).
 * Méthode: PUT
 * Entrée: 
 *    - statut (doit être "Active" ou "Inactive")
 *    - id (identifiant de la structure)
 * Sortie:
 *    - 200 en cas de succès avec les détails de la structure mise à jour.
 *    - 404 si la structure n'est pas trouvée.
 */
    public function changeStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|in:Active,Inactive', 
        ]);
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure non trouvée.'], 404);
        }
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
        $validated = $request->validate([
            'dispositif_id' => 'required|exists:dispositifs,id', 
        ]);
        $structure = Structure::findOrFail($structureId);
        $exists = StructureDispositif::where('structure_id', $structureId)
            ->where('dispositif_id', $validated['dispositif_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Le dispositif est déjà associé à cette structure.',
            ], 409); 
        }
        $structureDispositif = StructureDispositif::create([
            'structure_id' => $structureId,
            'dispositif_id' => $validated['dispositif_id'],
        ]);

        return response()->json([
            'message' => 'Dispositif ajouté avec succès à la structure.',
            'data' => $structureDispositif,
        ], 201); 
    }
/**
 * Description: Récupère la liste des dispositifs associés à une structure donnée.
 * Méthode: GET
 * Entrée: 
 *    - structureId (identifiant de la structure)
 * Sortie:
 *    - 200 avec la liste des dispositifs associés à la structure, ou un message indiquant qu'aucun dispositif n'a été trouvé.
 *    - 404 si la structure n'est pas trouvée.
 */

    public function getDispositifsForStructure($structureId)
    {
        $structure = Structure::find($structureId);

        if (!$structure) {
            return response()->json(['error' => 'Structure not found'], 404);
        }

        $dispositifs = StructureDispositif::where('structure_id', $structureId)
            ->join('dispositifs', 'structure_dispositifs.dispositif_id', '=', 'dispositifs.id')
            ->select('dispositifs.*') 
            ->get();

        if ($dispositifs->isEmpty()) {
            return response()->json([
                'message' => 'Aucun dispositif trouvé pour cette structure.',
                'data' => []
            ], 200);
        }
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
    $structure = Structure::find($structureId);

    if (!$structure) {
        return response()->json(['message' => 'Structure non trouvée'], 404);
    }
    $jeunes = $structure->users()
        ->whereHas('role', function ($query) {
            $query->where('name', 'Jeune'); 
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
    $structure = Structure::find($structureId);

    if (!$structure) {
        return response()->json(['message' => 'Structure non trouvée'], 404);
    }
    $referants = $structure->users()
        ->whereHas('role', function ($query) {
            $query->where('name', 'Referent');
        })
        ->with('referant') 
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
    $structure = Structure::find($structureId);

    if (!$structure) {
        return response()->json(['message' => 'Structure non trouvée'], 404);
    }
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
    $validated = $request->validate([
        'action_id' => 'required|exists:actions,id',
    ]);

    $structure = Structure::findOrFail($structureId);

    $exists = $structure->actions()->where('action_id', $validated['action_id'])->exists();

    if ($exists) {
        return response()->json([
            'message' => 'L\'action est déjà associée à cette structure.',
        ], 409); 
    }
    $structure->actions()->attach($validated['action_id']);

    return response()->json([
        'message' => 'Action ajoutée avec succès à la structure.',
    ], 201); 
}
}
