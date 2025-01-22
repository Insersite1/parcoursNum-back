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
            'couverture' =>'nullable|mimes:jpeg,png,jpg,gif',
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

    // Récupérer une structure par son ID
    public function show($id)
    {
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure non trouvé'], 404);
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




    public function updatestructureetat($id)
    {
        $structure = Structure::find($id); // Trouver l'utilisateur par son ID
        if ($structure) {
            // Basculer l'état en fonction de la valeur actuelle
            if ($structure->statut == 'Active') {
                $structure->statut = 'Inactive';
            } elseif ($structure->statut == 'Inactive') {
                $structure->statut = 'Active';
            }
            
            $structure->save(); // Enregistrer les modifications
            
            return response()->json(['message' => 'État mis à jour avec succès.'], 200);
        } else {
            return response()->json(['error' => 'referent introuvable.'], 404);
        }
    }








 public function destroy($id)
    {
        $structure = Structure::find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure non trouvée'], 404);
        }

        $structure->delete();

        return response()->json(['message' => 'Structure supprimée avec succès'], 200);
    }

}
