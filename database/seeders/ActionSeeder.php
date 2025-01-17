<?php

namespace Database\Seeders;

use App\Models\ActionUser;
use Illuminate\Database\Seeder;
use App\Models\Action;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ajouter des actions
        $action1 = Action::create([
            'nom' => 'Action 2',
            'place' => 30,
            'couverture' => 'Régionale1',
            'user_id' => 2, // Assurez-vous que cet utilisateur existe
            'structure_dispositif_id' => 1, // Assurez-vous que cette structure existe
            'DateDebut' => '2024-01-01',
            'type' => 'Formation2',
            'couleur' => '#FF5733',
            'DateFin' => '2024-02-01',
            'description' => 'Une action destinée à former les jeunes en compétences numériques 2.',
            'auteur' => 'Admin'
        ]);


        // Associer des utilisateurs aux actions
        ActionUser::create([
            'action_id' => $action1->id,
            'user_id' => 1
        ]);
    }
}
