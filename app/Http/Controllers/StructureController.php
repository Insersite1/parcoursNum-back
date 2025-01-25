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
        if ($request->hasFile('couverture')) {
            $couverture = $request->file('couverture');
            $avatarName = time() . '.' . $couverture->extension();
            $couverture->move(public_path('images'), $avatarName);
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
     * Description: Récupérer les dispositifs associés à une structure.
     * Méthode: GET
     * Entrée: ID de la structure.
     * Sortie: Liste des dispositifs avec status 200,
     * ou message indiquant qu'aucun dispositif n'a été trouvé.
     */
    public function getDispositifsForStructure($structureId)
    {
        $structure = Structure::find($structureId);

        if (!$structure) {
            return response()->json(['error' => 'Structure non trouvée'], 404);
        }

        $dispositifs = StructureDispositif::where('structure_id', $structureId)
            ->join('dispositifs', 'structure_dispositifs.dispositif_id', '=', 'dispositifs.id')
            ->select('dispositifs.*')
            ->get();

        if ($dispositifs->isEmpty()) {
            return response()->json([
                'message' => 'Aucun dispositif trouvé pour cette structure.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Liste des dispositifs pour la structure donnée.',
            'data' => $dispositifs,
        ], 200);
    }

    /**
     * Description: Mettre à jour une structure existante.
     * Méthode: PUT/PATCH
     * Entrée: Données partiellement ou complètement mises à jour.
     * Sortie: Structure mise à jour avec status 200.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'nomcomplet' => 'nullable|string|max:255',
                'dateExpire' => 'nullable|date',
                'statut' => 'nullable|in:Active,Inactive',
                'couverture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Récupération de la structure existante
            $structure = Structure::findOrFail($id);

            // Si un fichier de couverture est présent, on le traite
            if ($request->hasFile('couverture')) {
                $couverture = $request->file('couverture');
                $imageName = time() . '.' . $couverture->extension();
                $couverture->move(public_path('images'), $imageName);
                $structure->couverture = $imageName; // Mise à jour du nom de l'image dans la DB
            }

            // Mise à jour des autres champs
            $structure->nomcomplet = $validatedData['nomcomplet'] ?? $structure->nomcomplet;
            $structure->dateExpire = $validatedData['dateExpire'] ?? $structure->dateExpire;
            $structure->statut = $validatedData['statut'] ?? $structure->statut;

            // Sauvegarde des modifications
            $structure->save();

            // Actualisation des données et réponse
            return response()->json([
                'message' => 'Structure mise à jour avec succès.',
                'structure' => $structure,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de la structure.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Description: Supprimer une structure.
     * Méthode: DELETE
     * Entrée: ID de la structure.
     * Sortie: Message de confirmation avec status 200,
     * ou message d'erreur avec status 404 si non trouvée.
     */
    public function destroy($id)
    {
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure non trouvée'], 404);
        }

        $structure->delete();

        return response()->json(['message' => 'Structure supprimée avec succès'], 200);
    }

    /**
     * Description: Rechercher des structures par mot-clé.
     * Méthode: GET
     * Entrée: Mot-clé pour recherche (nomcomplet, statut, dateExpire).
     * Sortie: Liste des structures correspondantes.
     */
    public function search($search)
    {
        $structures = Structure::query()
            ->where('nomcomplet', 'LIKE', '%' . $search . '%')
            ->orWhere('statut', 'LIKE', '%' . $search . '%')
            ->orWhere('dateExpire', 'LIKE', '%' . $search . '%')
            ->get();

        return response()->json($structures);
    }

    /**
     * Description: Mettre à jour le statut d'une structure.
     * Méthode: PATCH
     * Entrée: id Structure
     * Sortie: Structure mise à jour avec status 200.
     */
    public function updatestructureetat($id)
    {
        $structure = Structure::find($id);
        if ($structure) {
            if ($structure->statut == 'Active') {
                $structure->statut = 'Inactive';
            } elseif ($structure->statut == 'Inactive') {
                $structure->statut = 'Active';
            }
            $structure->save();
            return response()->json(['message' => 'État mis à jour avec succès.'], 200);
        } else {
            return response()->json(['error' => 'referent introuvable.'], 404);
        }
    }
}
