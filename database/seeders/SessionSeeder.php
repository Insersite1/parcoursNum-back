<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 


class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sessions')->insert([
            [
                'image' => 'session1.png',
                'nom' => 'Session 1',
                'par' => 'Manager 1',
                'date_debut' => '2025-01-01',
                'date_fin' => '2025-01-10',
                'file' => 'file1.pdf',
                //'action_id' => 1,
                'description' => 'Description de la session 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image' => 'session2.png',
                'nom' => 'Session 2',
                'par' => 'Manager 2',
                'date_debut' => '2025-02-01',
                'date_fin' => '2025-02-10',
                'file' => 'file2.pdf',
                //'action_id' => 2,
                'description' => 'Description de la session 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Ajoutez d'autres enregistrements selon vos besoins
        ]);
        //
    }
}
