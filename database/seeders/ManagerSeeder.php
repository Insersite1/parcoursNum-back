<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nom' => 'Doe3',
                'Prenom' => 'John3',
                'email' => 'manager66@example.com',
                'password' => Hash::make('manager123'),
                'statut' => 'Active',
                'numTelephone' => '123456789',
                'role_id' => 3,
                'structure_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Smith',
                'Prenom' => 'Jane',
                'email' => 'manager02@example.com',
                'password' => Hash::make('manager456'),
                'statut' => 'Active',
                'numTelephone' => '987654321',
                'role_id' => 3,
                'structure_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
