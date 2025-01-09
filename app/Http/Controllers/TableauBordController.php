<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Action;

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
        $role = 'jeune';
        // Obtenez les totaux pour chaque attribut
        $counts = [
            'EPC' => User::where('role', $role)->whereNotNull('EPC')->count(),
            'ETH' => User::where('role', $role)->whereNotNull('ETH')->count(),
            'API' => User::where('role', $role)->whereNotNull('API')->count(),
            'ZRR' => User::where('role', $role)->whereNotNull('ZRR')->count(),
            'AE'  => User::where('role', $role)->whereNotNull('AE')->count(),
            'QP'  => User::where('role', $role)->whereNotNull('QP')->count(),
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
        $usersByRegion = User::where('role', 'jeune')
            ->select('region', \DB::raw('COUNT(*) as nombre'))
            ->groupBy('region')
            ->get();

        // Formate le résultat sous forme de tableau à deux colonnes
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
        // Récupérer les actions avec le nombre de jeunes associés
        $result = Action::withCount(['actionUsers as jeunes_count' => function ($query) {
            $query->whereHas('user', function ($q) {
                $q->where('role', 'jeune'); // Filtrer les utilisateurs par profil "jeune"
            });
        }])->get()->map(function ($action) {
            return [
                'action_name' => $action->nom, // Nom de l'action
                'jeunes_count' => $action->jeunes_count // Nombre de jeunes
            ];
        });
    
        // Retourner le tableau
        return response()->json($result);
    }
    
}
