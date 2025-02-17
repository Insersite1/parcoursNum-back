<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\StructureDispositif;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActionController extends Controller
{
    /**
     * Description: Retourne une liste paginée des actions avec leurs relations.
     * Méthode: GET
     * Entrée: per_page (optionnel) pour spécifier le nombre d'éléments par page.
     * Sortie: Liste paginée des actions avec métadonnées de pagination.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $actions = Action::with(['structureDispositif', 'user'])->paginate($perPage);

        return response()->json([
            'data' => $actions->items(),
            'total_pages' => $actions->lastPage(),
            'current_page' => $actions->currentPage(),
            'per_page' => $actions->perPage(),
            'total_items' => $actions->total()
        ], 200);
    }

    /**
     * Description: Afficher une action spécifique par son identifiant.
     * Méthode: GET
     * Entrée: id (identifiant de l'action)
     * Sortie: Détails de l'action + statut 200 si trouvée, message d'erreur + statut 404 sinon.
     */
    public function show($id)
    {
        $action = Action::with(['structureDispositif', 'user'])->find($id);

        if (!$action) {
            return response()->json(['message' => 'Action non trouvée'], 404);
        }

        return response()->json($action, 200);
    }

    /**
     * Description: Créer une nouvelle action.
     * Méthode: POST
     * Entrée: Données de l'action incluant nom, place, type, dates, description, couleur, structure, dispositif, auteur.
     * Sortie: Nouvelle action créée + statut 201 en cas de succès, message d'erreur + statut 500 en cas d'échec.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'couverture' => 'nullable|mimes:jpeg,png,jpg,gif',
                'nom' => 'required|string',
                'place' => 'required|string',
                'type' => 'required|string',
                'DateDebut' => 'required|date',
                'DateFin' => 'required|date|after_or_equal:DateDebut',
                'description' => 'required|string',
                'couleur' => 'required|string',
                'users' => 'nullable|array',
                'users.*' => 'required|integer|exists:users,id',
                'structure_id' => 'required|exists:structures,id',
                'dispositif_id' => 'required|exists:dispositifs,id',
                'auteur' => 'required|string',
            ]);

            $structureDispositif = StructureDispositif::firstOrCreate([
                'structure_id' => $validatedData['structure_id'],
                'dispositif_id' => $validatedData['dispositif_id'],
            ]);

            $validatedData['structure_dispositif_id'] = $structureDispositif->id;
            $userId = $validatedData['users'][0] ?? null;

            $action = new Action();
            if ($request->hasFile('couverture')) {
                $couverture = $request->file('couverture');
                $avatarName = time() . '.' . $couverture->extension();
                $couverture->move(public_path('images'), $avatarName);
                $action->couverture = $avatarName;
            }
            $action->fill($validatedData);
            $action->statut = "Active";
            $action->user_id = $userId;
            $action->save();

            if (isset($validatedData['users'])) {
                $action->users()->attach($validatedData['users']);
            }

            return response()->json(['message' => 'Action créée avec succès.', 'action' => $action], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Erreur de validation.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Description: Mettre à jour une action existante.
     * Méthode: PUT/PATCH
     * Entrée: id (identifiant de l'action) + champs modifiés.
     * Sortie: Action mise à jour + statut 200 en cas de succès, message d'erreur + statut 404 si non trouvée.
     */
    public function update(Request $request, $id)
    {
        $action = Action::find($id);

        if (!$action) {
            return response()->json(['message' => 'Action non trouvée'], 404);
        }

        $validatedData = $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'structure_dispositif_id' => 'sometimes|required|exists:structure_dispositifs,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'fichier' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('fichier')) {
            if ($action->fichier) {
                Storage::delete($action->fichier);
            }
            $filePath = $request->file('fichier')->store('actions_files');
            $validatedData['fichier'] = $filePath;
        }

        $action->update($validatedData);

        return response()->json($action, 200);
    }

    /**
     * Description: Supprimer une action existante.
     * Méthode: DELETE
     * Entrée: id (identifiant de l'action)
     * Sortie: Message de confirmation + statut 200 en cas de succès, message d'erreur + statut 404 si non trouvée.
     */
    public function destroy($id)
    {
        $action = Action::find($id);

        if (!$action) {
            return response()->json(['message' => 'Action non trouvée'], 404);
        }

        if ($action->fichier) {
            Storage::delete($action->fichier);
        }

        $action->delete();

        return response()->json(['message' => 'Action supprimée avec succès'], 200);
    }
}
