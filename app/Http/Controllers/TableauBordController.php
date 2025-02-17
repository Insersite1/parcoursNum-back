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
 * Description: Cette méthode récupère le nombre d'utilisateurs (Jeunes) associés à chaque critère EPC, ETH, API, ZRR, AE et QP.
 * Méthode: GET
 * Sortie:
 *    - Retourne un tableau avec les comptages des utilisateurs pour chaque critère sous forme de JSON.
 */
    public function getCounts()
    {
        $roleId = Role::where('name', 'Jeune')->value('id');

        $counts = [
            'EPC' => User::where('role_id', $roleId)->where('EPC', true)->count(),
            'ETH' => User::where('role_id', $roleId)->where('ETH', true)->count(),
            'API' => User::where('role_id', $roleId)->where('API', true)->count(),
            'ZRR' => User::where('role_id', $roleId)->where('ZRR', true)->count(),
            'AE'  => User::where('role_id', $roleId)->where('AE', true)->count(),
            'QP'  => User::where('role_id', $roleId)->where('QP', true)->count(),
        ];

        return response()->json($counts);
    }
/**
 * Description: Cette méthode récupère le nombre d'utilisateurs par région.
 * Méthode: GET
 * Sortie:
 *    - Retourne un tableau contenant le nom de la région et le nombre d'utilisateurs associés à chaque région sous forme de JSON.
 */
    public function getUsersByRegion()
    {
        $usersByRegion = User::select('region', DB::raw('COUNT(*) as nombre'))
            ->groupBy('region')
            ->get();
        $formattedResult = $usersByRegion->map(function ($item) {
            return [
                'region' => $item->region,
                'nombre' => $item->nombre,
            ];
        });

        return response()->json($formattedResult);
    }
/**
 * Description: Cette méthode récupère le nombre de jeunes associés à chaque action.
 * Méthode: GET
 * Sortie:
 *    - Retourne un tableau contenant les noms des actions et le nombre de jeunes associés à chaque action sous forme de JSON.
 */
    public function getJeunesByAction()
    {
        $result = DB::table('action_user')
            ->join('users', 'action_user.user_id', '=', 'users.id') 
            ->join('actions', 'action_user.action_id', '=', 'actions.id') 
            ->where('users.role_id', 2) 
            ->select('actions.nom as action_name', DB::raw('COUNT(action_user.user_id) as jeunes_count')) 
            ->groupBy('actions.nom') 
            ->get();

        return response()->json($result);
    }

/**
 * Description: Cette méthode récupère le nombre de jeunes associés à chaque dispositif.
 * Méthode: GET
 * Sortie:
 *    - Retourne un tableau contenant les dispositifs et le nombre de jeunes associés sous forme de JSON.
 */
    public function nombreJeunesParDispositif(): JsonResponse
    {
        $roleJeune = Role::where('name', 'Jeune')->first();

        $dispositifs = Dispositif::with(['structures.users' => function ($query) use ($roleJeune) {
            $query->where('role_id', $roleJeune->id);
        }])->get();

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
/**
 * Description: Cette méthode récupère la répartition des jeunes par tranche d'âge et par sexe.
 * Méthode: GET
 * Entrée:
 *    - Aucun paramètre d'entrée spécifique, sauf que la méthode cherche les jeunes ayant le rôle "Jeune" (rôle identifié par 'Jeune' dans la base de données).
 * Sortie:
 *    - Retourne une répartition par tranche d'âge, incluant le nombre de jeunes hommes et femmes dans chaque tranche, sous forme de JSON.
 */

public function distributionJeunesByAge(): JsonResponse
{
    $roleId = Role::where('name', 'Jeune')->value('id');

    if (!$roleId) {
        return response()->json(['error' => 'Rôle "Jeune" non trouvé'], 404);
    }
    $tranches = [
        '<14' => [0, 14],
        '15-19' => [15, 19],
        '20-24' => [20, 24],
        '25-29' => [25, 29],
        '30-34' => [30, 34],
        '35-39' => [35, 39],
        '40-44' => [40, 44],
        '>45' => [45, 100],
    ];

    $distribution = [];

    foreach ($tranches as $tranche => [$minAge, $maxAge]) {
        $hommes = User::where('role_id', $roleId)
            ->where('sexe', 'M') 
            ->whereNotNull("dateNaissance")
            ->whereRaw('EXTRACT(YEAR FROM AGE("dateNaissance")) BETWEEN ? AND ?', [$minAge, $maxAge])
            ->count();
        ;
        $femmes = User::where('role_id', $roleId)
            ->where('sexe', 'F')
            ->whereNotNull("dateNaissance")
            ->whereRaw('EXTRACT(YEAR FROM AGE("dateNaissance")) BETWEEN ? AND ?', [$minAge, $maxAge])
            ->count();
        ;
        $distribution[] = [
            'tranche' => $tranche,
            'hommes' => $hommes,
            'femmes' => $femmes,
        ];
    }
    return response()->json($distribution);
}
}
