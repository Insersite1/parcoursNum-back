<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Exception;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{

/**
 * Description: Récupère un rôle en fonction de son nom.
 * Méthode: GET
 * Entrée: roleName (nom du rôle à rechercher)
 * Sortie: Détails du rôle trouvé + statut 200 en cas de succès, message d'erreur + statut 404 si le rôle n'est pas trouvé, message d'erreur + statut 500 en cas d'échec.
 */
public function getRoleByName($roleName)
{
    try {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return response()->json([
                'message' => 'Rôle non trouvé.'
            ], 404);
        }

        return response()->json([
            'message' => 'Rôle trouvé avec succès.',
            'role' => $role
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Une erreur est survenue.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
