<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 


class SessionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('session_user')->insert([
            ['user_id' => 5, 'session_id' => 1],
            ['user_id' => 5, 'session_id' => 2],
            ['user_id' => 6, 'session_id' => 1],
            // Ajoutez d'autres enregistrements selon vos besoins
        ]);
    }
}
