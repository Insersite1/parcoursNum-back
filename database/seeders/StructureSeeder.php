<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('structures')->insert([
            [
                'couverture' => 'default_cover.png',
                'nomcomplet' => 'INSERSITe',
                'dateExpire' => now()->addYear(),
                'statut' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'couverture' => null,
                'nomcomplet' => 'Mission Local',
                'dateExpire' => now()->addMonths(6),
                'statut' => 'Inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'couverture' => 'custom_cover.png',
                'nomcomplet' => 'CNAM',
                'dateExpire' => now()->addMonths(3),
                'statut' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
