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

        // Vérifier si le rôle "Jeune" existe
        if (!$roleId) {
            return response()->json(['error' => 'Rôle "Jeune" non trouvé'], 404);
        }

        // Compter les totaux pour chaque attribut, avec un rôle spécifique
        $counts = [
            'EPC' => User::where('role_id', $roleId)->where('EPC', 1)->count(),
            'ETH' => User::where('role_id', $roleId)->where('ETH', 1)->count(),
            'API' => User::where('role_id', $roleId)->where('API', 1)->count(),
            'ZRR' => User::where('role_id', $roleId)->where('ZRR', 1)->count(),
            'AE' => User::where('role_id', $roleId)->where('AE', 1)->count(),
            'QP' => User::where('role_id', $roleId)->where('QP', 1)->count(),
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

        // Retourner directement le résultat sous forme de réponse JSON
        return response()->json($usersByRegion);
    }


    /**
     * Récupère le nombre d'utilisateurs (jeunes) associés à chaque action.
     *
     * @return JsonResponse Résultats sous forme de tableau avec le nom de l'action et le nombre de jeunes associés.
     */
    public function getJeunesByAction()
    {
        // Utilisation des relations Eloquent pour obtenir le nombre de jeunes par action
        $result = Action::join('action_user', 'actions.id', '=', 'action_user.action_id')
            ->join('users', 'action_user.user_id', '=', 'users.id')
            ->where('users.role_id', 2) // Filtrer uniquement les utilisateurs avec le rôle "Jeune" (ID 2)
            ->select('actions.nom as action_name', DB::raw('COUNT(action_user.user_id) as jeunes_count'))
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
        // Récupérer le rôle "Jeune"
        $roleJeune = Role::where('name', 'Jeune')->first();

        // Récupérer les dispositifs avec le nombre de jeunes associés
        $dispositifs = Dispositif::with(['structures.users' => function ($query) use ($roleJeune) {
            $query->where('role_id', $roleJeune->id);
        }])->get();

        // Préparer les résultats
        $resultats = $dispositifs->map(function ($dispositif) {
            // Calculer le nombre total de jeunes associés à chaque dispositif
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

    public function distributionJeunesByAge(): JsonResponse
    {
        // Récupérer l'ID du rôle "Jeune"
        $roleId = Role::where('name', 'Jeune')->value('id');

        // Vérifier si le rôle existe
        if (!$roleId) {
            return response()->json(['error' => 'Rôle "Jeune" non trouvé'], 404);
        }

        // Définir les tranches d'âge
        $tranches = [
            '<14' => [0, 14],
            '15-19' => [15, 19],
            '20-24' => [20, 24],
            '25-29' => [25, 29],
            '30-34' => [30, 34],
            '35-39' => [35, 39],
            '40-44' => [40, 44],
            '>45' => [45, 100], // Âge maximum supposé
        ];

        $distribution = [];

        foreach ($tranches as $tranche => [$minAge, $maxAge]) {
            // Calculer le nombre d'hommes et de femmes dans chaque tranche
            $hommes = User::where('role_id', $roleId)
                ->where('sexe', 'M') // Homme
                ->whereNotNull('dateNaissance')
                //->whereRaw('EXTRACT(YEAR FROM AGE(dateNaissance)) BETWEEN ? AND ?', [$minAge, $maxAge])
                //->count();
            ;
                $femmes = User::where('role_id', $roleId)
                ->where('sexe', 'F') // Femme
                ->whereNotNull('dateNaissance')
               // ->whereRaw('TIMESTAMPDIFF(dateNaissance, CURDATE()) BETWEEN ? AND ?', [$minAge, $maxAge])
                ->count();

            $distribution[] = [
                'tranche' => $tranche,
                'hommes' => $hommes,
                'femmes' => $femmes,
            ];
        }

        // Retourner les données en JSON
        return response()->json($distribution);
        }

}
