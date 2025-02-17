<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class SceanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sceances')->insert([
            [
                'nom' => 'Séance 1',
                'par' => 'Manager 1',
                'session_code' => 'S1',
                'description' => 'Description de la séance 1',
                'date_debut' => '2025-01-02',
                'date_fin' => '2025-01-05',
                //'session_id' => 1,
                'couverture' => 'couverture1.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Séance 2',
                'par' => 'Manager 2',
                'session_code' => 'S2',
                'description' => 'Description de la séance 2',
                'date_debut' => '2025-02-02',
                'date_fin' => '2025-02-05',
                //'session_id' => 2,
                'couverture' => 'couverture2.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Ajoutez d'autres enregistrements selon vos besoins
        ]);
        //
    }
}
