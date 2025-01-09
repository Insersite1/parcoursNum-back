<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
     {
        User::create([
            'avatar' => null,
            'nom' => 'SuperAdmine',
            'email' => 'superadmin@example.com',
            'numTelephone' => '123456789',
            'password' => Hash::make('superadmin123'),
            'role_id' => 1,
            'statut' => 'Active',
            'sexe' => 'M',
        ]);

    }
}
