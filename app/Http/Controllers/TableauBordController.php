<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\Dispositif;




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
        // Obtenez les totaux pour chaque attribut
        $counts = [
            'EPC' => User::whereNotNull('EPC')->count(),
            'ETH' => User::whereNotNull('ETH')->count(),
            'API' => User::whereNotNull('API')->count(),
            'ZRR' => User::whereNotNull('ZRR')->count(),
            'AE'  => User::whereNotNull('AE')->count(),
            'QP'  => User::whereNotNull('QP')->count(),
        ];
    
        // Retournez les résultats sous forme de réponse JSON
        return response()->json($counts);
    }
     

    public function getYouthStatistics()
    {
        $currentDate = Carbon::now();
    
        $statistics = [
            'M' => [
                'under_14' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) < 14', [$currentDate])
                    ->count(),
                '15_19' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 15 AND 19', [$currentDate])
                    ->count(),
                '20_24' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 20 AND 24', [$currentDate])
                    ->count(),
                '25_29' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 25 AND 29', [$currentDate])
                    ->count(),
                '30_34' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 30 AND 34', [$currentDate])
                    ->count(),
                '35_39' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 35 AND 39', [$currentDate])
                    ->count(),
                '40_44' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 40 AND 44', [$currentDate])
                    ->count(),
                'over_45' => User::where('sexe', 'M')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) > 45', [$currentDate])
                    ->count(),
            ],
            'F' => [
                'under_14' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) < 14', [$currentDate])
                    ->count(),
                '15_19' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 15 AND 19', [$currentDate])
                    ->count(),
                '20_24' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 20 AND 24', [$currentDate])
                    ->count(),
                '25_29' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 25 AND 29', [$currentDate])
                    ->count(),
                '30_34' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 30 AND 34', [$currentDate])
                    ->count(),
                '35_39' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 35 AND 39', [$currentDate])
                    ->count(),
                '40_44' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) BETWEEN 40 AND 44', [$currentDate])
                    ->count(),
                'over_45' => User::where('sexe', 'F')
                    ->where('role', 'jeune')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dateNaissance, ?) > 45', [$currentDate])
                    ->count(),
            ]
        ];
    
        return response()->json($statistics);
    }

    public function getUsersByRegion()
    {
        $usersByRegion = User::selectRaw('region, COUNT(*) as user_count')
            ->groupBy('region')
            ->get();

        // Formate le résultat sous forme de tableau à deux colonnes
        $formattedResult = $usersByRegion->map(function ($item) {
            return [
                'region' => $item->region,
                'user_count' => $item->user_count,
            ];
        });

        return response()->json($formattedResult);
    }

    public function nombreJeunesParDispositif(): JsonResponse
    {
        // Récupérer le rôle "jeune"
        $roleJeune = Role::where('name', 'jeune')->first();

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
    
}