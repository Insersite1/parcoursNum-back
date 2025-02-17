<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class SceanceUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sceance_user')->insert([
            ['user_id' => 4, 'sceance_id' => 1],
            ['user_id' => 1, 'sceance_id' => 2],
            ['user_id' => 5, 'sceance_id' => 1],
            // Ajoutez d'autres enregistrements selon vos besoins
        ]);
        //
    }
}
