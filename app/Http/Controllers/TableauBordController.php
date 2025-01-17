<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ActionUser;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\Dispositif;
use App\Models\Action;
use Illuminate\Support\Facades\DB;

class TableauBordController extends Controller
{
    /**
     * Récupère les totaux pour chaque attribut (EPC, ETH, API, etc.) pour les utilisateurs ayant le rôle "Jeune".
     *
     * @return JsonResponse Résultats sous forme de JSON avec les totaux pour chaque attribut.
     */
    public function getCounts()
    {
        // Récupérer l'ID du rôle 'Jeune'
        $roleId = Role::where('name', 'Jeune')->value('id');

        // Compter les totaux pour chaque attribut, avec un rôle spécifique
        $counts = [
            'EPC' => User::where('role_id', $roleId)->where('EPC', true)->count(),
            'ETH' => User::where('role_id', $roleId)->where('ETH', true)->count(),
            'API' => User::where('role_id', $roleId)->where('API', true)->count(),
            'ZRR' => User::where('role_id', $roleId)->where('ZRR', true)->count(),
            'AE'  => User::where('role_id', $roleId)->where('AE', true)->count(),
            'QP'  => User::where('role_id', $roleId)->where('QP', true)->count(),
        ];

        // Retourner les résultats sous forme de réponse JSON
        return response()->json($counts);
    }

    /**
     * Récupère le nombre d'utilisateurs par région.
     *
     * @return JsonResponse Résultats sous forme de tableau avec 'region' et 'nombre'.
     */
    public function getUsersByRegion()
    {
        // Requête pour compter les utilisateurs par région
        $usersByRegion = User::select('region', DB::raw('COUNT(*) as nombre'))
            ->groupBy('region')
            ->get();

        // Formater le résultat sous forme de tableau à deux colonnes
        $formattedResult = $usersByRegion->map(function ($item) {
            return [
                'region' => $item->region,
                'nombre' => $item->nombre,
            ];
        });

        return response()->json($formattedResult);
    }

    /**
     * Récupère le nombre d'utilisateurs (jeunes) associés à chaque action.
     *
     * @return JsonResponse Résultats sous forme de tableau avec le nom de l'action et le nombre de jeunes associés.
     */
    public function getJeunesByAction()
    {
        // Requête pour récupérer les actions avec le nombre de jeunes associés
        $result = DB::table('action_user')
            ->join('users', 'action_user.user_id', '=', 'users.id') // Joindre avec la table des utilisateurs
            ->join('actions', 'action_user.action_id', '=', 'actions.id') // Joindre avec la table des actions
            ->where('users.role_id', 2) // Filtrer uniquement les utilisateurs avec le rôle "Jeune" (ID 2)
            ->select('actions.nom as action_name', DB::raw('COUNT(action_user.user_id) as jeunes_count')) // Récupérer le nom de l'action et le nombre de jeunes
            ->groupBy('actions.nom') // Grouper par nom de l'action
            ->get();

        // Retourner le résultat en JSON
        return response()->json($result);
    }

    /**
     * Récupère le nombre de jeunes associés à chaque dispositif.
     *
     * @return JsonResponse Résultats sous forme de tableau avec le nom du dispositif et le nombre de jeunes.
     */
    public function nombreJeunesParDispositif(): JsonResponse
    {
        // Récupérer le rôle "jeune"
        $roleJeune = Role::where('name', 'Jeune')->first();

        // Récupérer les dispositifs avec le nombre de "jeunes" associés
        $dispositifs = Dispositif::with(['structures.users' => function ($query) use ($roleJeune) {
            $query->where('role_id', $roleJeune->id);
        }])->get();

        // Préparer les résultats
        $resultats = $dispositifs->map(function ($dispositif) {
            $nombreJeunes = $dispositif->structures->sum(function ($structure) {
                return $structure->users->count();
            });

            return [
                'dispositif' => $dispositif->name,
                'nombre_jeunes' => $nombreJeunes,
            ];
        });

        return response()->json($resultats);
    }
}
