<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Direction;

class DirectionSeeder extends Seeder
{
    public function run(): void
    {
        $directions = [
            [ 'codedir' => '1', 'libelle' => 'Direction Générale'],
            [ 'codedir' => '2', 'libelle' => 'Direction des Finances et de la comptabilité'],
            ['codedir' => '3', 'libelle' => 'Département Informatique'],
            [ 'codedir' => '4', 'libelle' => 'Département de l\'Administration et des Ressources Humaines'],
            [ 'codedir' => '5', 'libelle' => 'Direction du Développement Commercial'],
            [ 'codedir' => '6', 'libelle' => 'Direction Technique et Production'],
            [ 'codedir' => '7', 'libelle' => 'Direction des Sinistres et Prestations'],
            [ 'codedir' => '8', 'libelle' => 'Direction des Affaires Directes et Réassurances'],
            [ 'codedir' => '9', 'libelle' => 'Direction du Courtage'],
        ];

        foreach ($directions as $dir) {
            Direction::updateOrCreate(['codedir' => $dir['codedir']], $dir);
        }
    }
}
