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

        User::create([
            'avatar' => 'default_avatar.png',
            'nom' => 'Doe',
            'Prenom' => 'John',
            'email' => 'john.doe111@example.com',
            'numTelephone' => '789012342',
            'password' => Hash::make('passer'),
            'statut' => 'Active',
            'situation' => 'Étudiant',
            'sexe' => 'M',
            'etatCivil' => 'Célibataire',
            'situationTravail' => 'Sans emploi',
            'QP' => true,
            'ZRR' => false,
            'ETH' => true,
            'EPC' => false,
            'API' => false,
            'AE' => true,
            'Adresse' => '123 Rue Principale',
            'bibiographie' => 'Un jeune motivé et prêt à relever des défis.',
            'dateNaissance' => '2000-01-01',
            'codePostal' => '75001',
            'region' => 'Île-de-France',
            'ville' => 'Paris',
            'NumSecuriteSocial' => '123456789012345',
            'role_id' => 2,
            'structure_id' => 1,
        ]);
    }
}
