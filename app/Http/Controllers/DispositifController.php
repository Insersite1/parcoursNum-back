<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DispositifController extends Controller
{
    /**
     * Description: Récupérer la liste de tous les dispositifs.
     * Méthode: GET
     * Entrée: Aucun paramètre requis.
     * Sortie: Liste des dispositifs avec un statut 200.
     */
    public function index()
    {
        $dispositifs = Dispositif::all();
        return response()->json($dispositifs, 200);
    }

    /**
     * Description: Créer un nouveau dispositif.
     * Méthode: POST
     * Entrée:
     *  - name (string, requis)
     *  - couverture (image, optionnel, max: 2MB, formats: jpeg, png, jpg, gif)
     *  - DateDebut (date, requis)
     *  - DateFin (date, requis, doit être après ou égal à DateDebut)
     *  - statut (string, requis, valeurs: Active, Inactive)
     *  - pays (string, requis)
     * Sortie: Données du dispositif créé avec un statut 201 ou erreurs de validation avec un statut 422.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'couverture' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after_or_equal:DateDebut',
            'statut' => 'required|in:Active,Inactive',
            'pays' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $dispositif = Dispositif::create($request->all());
    
        return response()->json($dispositif, 201);
    }

    /**
     * Description: Récupérer un dispositif par son ID.
     * Méthode: GET
     * Entrée: ID du dispositif (int, requis).
     * Sortie: Données du dispositif avec un statut 200 ou message d'erreur avec un statut 404 si non trouvé.
     */
    public function show($id)
    {
        $dispositif = Dispositif::find($id);

        if (!$dispositif) {
            return response()->json(['message' => 'Dispositif non trouvé'], 404);
        }

        return response()->json($dispositif, 200);
    }

    /**
     * Description: Mettre à jour un dispositif existant.
     * Méthode: PUT/PATCH
     * Entrée:
     *  - name (string, optionnel)
     *  - couverture (string, optionnel)
     *  - DateDebut (date, optionnel)
     *  - DateFin (date, optionnel, doit être après ou égal à DateDebut)
     *  - statut (string, optionnel, valeurs: Active, Inactive)
     *  - pays (string, optionnel)
     * Sortie: Données du dispositif mis à jour avec un statut 200 ou message d'erreur avec un statut 404 si non trouvé.
     */
    public function update(Request $request, $id)
    {
        $dispositif = Dispositif::find($id);

        if (!$dispositif) {
            return response()->json(['message' => 'Dispositif non trouvé'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'couverture' => 'nullable|string',
            'DateDebut' => 'sometimes|date',
            'DateFin' => 'sometimes|date|after_or_equal:DateDebut',
            'statut' => 'sometimes|in:Active,Inactive',
            'pays' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $dispositif->update($request->all());

        return response()->json($dispositif, 200);
    }

    /**
     * Description: Supprimer un dispositif.
     * Méthode: DELETE
     * Entrée: ID du dispositif (int, requis).
     * Sortie: Message de confirmation avec un statut 200 ou message d'erreur avec un statut 404 si non trouvé.
     */
    public function destroy($id)
    {
        $dispositif = Dispositif::find($id);

        if (!$dispositif) {
            return response()->json(['message' => 'Dispositif non trouvé'], 404);
        }

        $dispositif->delete();

        return response()->json(['message' => 'Dispositif supprimé avec succès'], 200);
    }
}
