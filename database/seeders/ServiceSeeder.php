<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['codeserv' => '1', 'libelle' => 'Cellule Audit Interne', 'codedir' => 1],
            ['codeserv' => '2', 'libelle' => 'Département Informatique', 'codedir' => 1],
            ['codeserv' => '3', 'libelle' => 'Direction des Finances et de la Comptabilité', 'codedir' => 1, 'is_direction' => true],
            ['codeserv' => '4', 'libelle' => 'Direction Ressources Humaines', 'codedir' => 1],
            ['codeserv' => '5', 'libelle' => 'Direction des Affaires Directes et Réassurances', 'codedir' => 1],
            ['codeserv' => '6', 'libelle' => 'Direction du Courtage', 'codedir' => 1],
            ['codeserv' => '7', 'libelle' => 'Direction Santé', 'codedir' => 1],
            ['codeserv' => '8', 'libelle' => 'Directeur DFC', 'codedir' => 2],
            ['codeserv' => '9', 'libelle' => 'Sécrétaire du DFC', 'codedir' => 2],
            ['codeserv' => '10', 'libelle' => 'Comptabilité', 'codedir' => 2],
            ['codeserv' => '11', 'libelle' => 'Trésorerie', 'codedir' => 2],
            ['codeserv' => '12', 'libelle' => 'Chef Département Informatique', 'codedir' => 3],
            ['codeserv' => '13', 'libelle' => 'Etudes, Développement logiciels métiers', 'codedir' => 3],
            ['codeserv' => '14', 'libelle' => 'Administration Système Réseaux', 'codedir' => 3],
            ['codeserv' => '15', 'libelle' => 'Directeur (trice) des Resources Humaines', 'codedir' => 4],
            ['codeserv' => '16', 'libelle' => 'Administration du personnel', 'codedir' => 4],
            ['codeserv' => '17', 'libelle' => 'Administration et Moyens Généraux', 'codedir' => 4],
            ['codeserv' => '18', 'libelle' => 'Directeur des affaires directes', 'codedir' => 5],
            ['codeserv' => '19', 'libelle' => 'Sécrétaire du DADR', 'codedir' => 5],
            ['codeserv' => '20', 'libelle' => 'Développement et Recherche', 'codedir' => 5],
            ['codeserv' => '21', 'libelle' => 'BancAssurance', 'codedir' => 5],
            ['codeserv' => '22', 'libelle' => 'Grds Cpte et Réassurance', 'codedir' => 5],
            ['codeserv' => '23', 'libelle' => 'Service Sinistre', 'codedir' => 5],
            ['codeserv' => '24', 'libelle' => 'Force de ventes,Coordination des agences', 'codedir' => 5],
            ['codeserv' => '25', 'libelle' => 'Bureau Direct Siège', 'codedir' => 5],
            ['codeserv' => '26', 'libelle' => 'Bureau Direct Steinmetz', 'codedir' => 5],
            ['codeserv' => '27', 'libelle' => 'Bureau Direct Nord', 'codedir' => 5],
            ['codeserv' => '28', 'libelle' => 'Directeur du courtage', 'codedir' => 6],
            ['codeserv' => '29', 'libelle' => 'Sécrétaire du DC', 'codedir' => 6],
            ['codeserv' => '30', 'libelle' => 'Service Production', 'codedir' => 6],
            ['codeserv' => '31', 'libelle' => 'Directeur Santé', 'codedir' => 7],
            ['codeserv' => '32', 'libelle' => 'Chef Service Santé', 'codedir' => 7],
            ['codeserv' => '33', 'libelle' => 'Service Règlement Sinistre', 'codedir' => 7],
            ['codeserv' => '34', 'libelle' => 'Caisse', 'codedir' => 2],
            ['codeserv' => '35', 'libelle' => 'Cellule Contröle Medical', 'codedir' => 1],
            ['codeserv' => '36', 'libelle' => 'Direction Sinistre', 'codedir' => 1],
            ['codeserv' => '37', 'libelle' => 'Service Automobile', 'codedir' => 8],
            ['codeserv' => '38', 'libelle' => 'Service Recours et Contentieux', 'codedir' => 8],
            ['codeserv' => '39', 'libelle' => 'Service Incendie,Risques Divers,Technique et Transports', 'codedir' => 8],
            ['codeserv' => '40', 'libelle' => 'Cellule Réassurance', 'codedir' => 1],
            ['codeserv' => '41', 'libelle' => 'Sécretariat Vie', 'codedir' => 9],
            ['codeserv' => '42', 'libelle' => 'Directeur Vie', 'codedir' => 9],
            ['codeserv' => '43', 'libelle' => 'Direction Vie', 'codedir' => 1],
            ['codeserv' => '44', 'libelle' => 'Direction Générale Adjoint', 'codedir' => 1],
        ];

        foreach ($services as $service) {
            Service::create([
                'codeserv' => $service['codeserv'],
                'libelle' => $service['libelle'],
                'codedir' => $service['codedir'],
                'is_direction' => $service['is_direction'] ?? false,
                'created_at' => null,
                'updated_at' => null,
            ]);
        }
    }
}
