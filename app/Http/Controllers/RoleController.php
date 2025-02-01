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
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }

        /**
 * Récupère le rôle d'un utilisateur par son nom de rôle.
 *
 * @param string $roleName Le nom du rôle à récupérer.
 * @return \Illuminate\Http\JsonResponse Réponse JSON avec le rôle trouvé ou une erreur.
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
