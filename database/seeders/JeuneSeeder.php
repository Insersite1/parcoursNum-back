<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JeuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nom' => 'User',
            'Prenom' => 'Regular',
            'email' => 'user@example.com',
            'numTelephone' => '782904656',
            'password' => Hash::make('passer'),
            'role_id' => 2,
            'statut' => 'Active',
        ]);
        User::create([
            'nom' => 'User2',
            'Prenom' => 'Regular2',
            'email' => 'user2@example.com',
            'numTelephone' => '762904656',
            'password' => Hash::make('passer'),
            'role_id' => 2,
            'statut' => 'Active',
        ]);
    }
}
