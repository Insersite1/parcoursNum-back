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
use Symfony\Component\HttpFoundation\JsonResponse;



class TableauBordController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

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

    public function getUsersByRegion()
    {
        // Utilisation correcte de DB pour effectuer la requête
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




    public function nombreJeunesParDispositif(): JsonResponse
    {
        // Récupérer le rôle "jeune"
        $roleJeune = Role::where('name', 'Jeune')->first();

        // Vérifier si le rôle "jeune" existe
        /*if (!$roleJeune) {
            return response()->json(['error' => 'Rôle "jeune" non trouvé.'], 404);
        }*/

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


    public function getYoungUserStatistics()
    {
        $currentDate = Carbon::now();
        $ageRanges = [
            'under_14' => [null, 13],
            '15_19' => [15, 19],
            '20_24' => [20, 24],
            '25_29' => [25, 29],
            '30_34' => [30, 34],
            '35_39' => [35, 39],
            '40_44' => [40, 44],
        ];

        $statistics = ['M' => [], 'F' => []];

        // Identifier le rôle "jeune"
        $jeuneRoleId = Role::where('name', 'Jeune')->value('id');

        foreach (['M', 'F'] as $sexe) {
            foreach ($ageRanges as $key => [$min, $max]) {
                $query = User::where('sexe', $sexe)
                    ->where('role_id', $jeuneRoleId)
                    ->whereRaw('EXTRACT(YEAR FROM AGE(dateNaissance)) >= ?', [$min ?? 0]);

                if ($max !== null) {
                    $query->whereRaw('EXTRACT(YEAR FROM AGE(dateNaissance)) <= ?', [$max]);
                }

                $statistics[$sexe][$key] = $query->count();
            }
        }

        return response()->json($statistics);
    }


}
