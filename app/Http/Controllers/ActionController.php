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
     * Liste paginée des actions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Nombre d'actions par page (par défaut : 10)
        $perPage = $request->get('per_page', 10);

        // Récupérer les actions paginées avec les relations nécessaires
        $actions = Action::with(['structureDispositif', 'user'])->paginate($perPage);

        // Retourner la réponse JSON avec pagination
        return response()->json([
            'data' => $actions->items(),
            'total_pages' => $actions->lastPage(),
            'current_page' => $actions->currentPage(),
            'per_page' => $actions->perPage(),
            'total_items' => $actions->total()
        ], 200);
    }

    /**
     * Afficher une action spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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
     * Créer une nouvelle action.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
{
    try {
        // Validation des données
        $validatedData = $request->validate([
            'nom' => 'required|string',
            'place' => 'required|string',
            'couverture' => 'nullable|mimes:jpeg,png,jpg',
            'type' => 'required|string',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after_or_equal:DateDebut',
            'description' => 'required|string',
            'couleur' => 'required|string',
            'user_id' => 'exists:users,id',
            'structure_id' => 'required|exists:structures,id',
            'dispositif_id' => 'required|exists:dispositifs,id',
            'auteur'=> 'required|string',
        ]);

        // Validation ou création de l'association
        $structureDispositif = StructureDispositif::firstOrCreate(
            [
                'structure_id' => $validatedData['structure_id'],
                'dispositif_id' => $validatedData['dispositif_id']
            ]
        );

        $validatedData['structure_dispositif_id'] = $structureDispositif->id;

        $action = new Action();

        $action->nom = $validatedData['nom'];
        $action->place = $validatedData['place'];
        $action->type = $validatedData['type'];
        $action->DateDebut = $validatedData['DateDebut'];
        $action->DateFin = $validatedData['DateFin'];
        $action->description = $validatedData['description'];
        $action->couleur = $validatedData['couleur'];
        $action->structure_dispositif_id = $validatedData['structure_dispositif_id'];
        $action->auteur = $validatedData['auteur'];


    if (!isset($validatedData['user_id'])) {

    $usersWithRole2 = User::where('role_id', 2)->get();

    if ($usersWithRole2->isNotEmpty()) {

        $action->users()->attach($usersWithRole2->pluck('id')->toArray());
    } else {
        return response()->json(['message' => 'Aucun utilisateur avec le rôle spécifié trouvé.'], 400);
    }
}

    $action->user_id = $validatedData['user_id'];


        $action->save();


        $action->users()->attach($validatedData['user_id'], ['action_id' => $action->id]);


        if ($request->hasFile('couverture')) {
            $file = $request->file('couverture');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName);
            $action->couverture = $fileName;
            $action->save();
        }

        return response()->json(['message' => 'Action créée avec succès.', 'action' => $action], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => 'Erreur de validation.', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Une erreur est survenue lors de la création de l\'action.', 'error' => $e->getMessage()], 500);
    }
}






    /**
     * Mettre à jour une action existante.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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

        // Gérer le fichier s'il existe
        if ($request->hasFile('fichier')) {
            // Supprimer l'ancien fichier
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
     * Supprimer une action.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $action = Action::find($id);

        if (!$action) {
            return response()->json(['message' => 'Action non trouvée'], 404);
        }

        // Supprimer le fichier associé s'il existe
        if ($action->fichier) {
            Storage::delete($action->fichier);
        }

        $action->delete();

        return response()->json(['message' => 'Action supprimée avec succès'], 200);
    }
}
