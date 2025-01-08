<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use App\Models\Structure;
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

}
