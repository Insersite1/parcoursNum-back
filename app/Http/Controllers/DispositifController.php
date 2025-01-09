<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DispositifController extends Controller
{
    // Récupérer tous les dispositifs
    public function index()
    {
        $dispositifs = Dispositif::all();
        return response()->json($dispositifs, 200);
    }

    // Créer un nouveau dispositif
    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'couverture' => 'nullable|string',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after_or_equal:DateDebut',
            'statut' => 'required|in:Active,Inactive',
            'pays' => 'required|string|max:255',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Création du dispositif
        $dispositif = Dispositif::create($request->all());

        return response()->json($dispositif, 201);
    }

    // Récupérer un dispositif par son ID
    public function show($id)
    {
        $dispositif = Dispositif::find($id);

        if (!$dispositif) {
            return response()->json(['message' => 'Dispositif non trouvé'], 404);
        }

        return response()->json($dispositif, 200);
    }

    // Mettre à jour un dispositif
    public function update(Request $request, $id)
    {
        $dispositif = Dispositif::find($id);

        if (!$dispositif) {
            return response()->json(['message' => 'Dispositif non trouvé'], 404);
        }

        // Validation des données
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

        // Mise à jour du dispositif
        $dispositif->update($request->all());

        return response()->json($dispositif, 200);
    }

    // Supprimer un dispositif
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
