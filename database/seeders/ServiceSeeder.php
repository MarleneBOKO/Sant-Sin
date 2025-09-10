<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['codeserv' => '1', 'libelle' => 'Cellule Audit Interne', 'direction_id' => 1],
            ['codeserv' => '2', 'libelle' => 'Département Informatique', 'direction_id' => 1],
            ['codeserv' => '3', 'libelle' => 'Direction des Finances et de la Comptabilité', 'direction_id' => 1, 'is_direction' => true],
            ['codeserv' => '4', 'libelle' => 'Direction Ressources Humaines', 'direction_id' => 1],
            ['codeserv' => '5', 'libelle' => 'Direction des Affaires Directes et Réassurances', 'direction_id' => 1],
            ['codeserv' => '6', 'libelle' => 'Direction du Courtage', 'direction_id' => 1],
            ['codeserv' => '7', 'libelle' => 'Direction Santé', 'direction_id' => 1],
            ['codeserv' => '8', 'libelle' => 'Directeur DFC', 'direction_id' => 2],
            ['codeserv' => '9', 'libelle' => 'Sécrétaire du DFC', 'direction_id' => 2],
            ['codeserv' => '10', 'libelle' => 'Comptabilité', 'direction_id' => 2],
            ['codeserv' => '11', 'libelle' => 'Trésorerie', 'direction_id' => 2],
            ['codeserv' => '12', 'libelle' => 'Chef Département Informatique', 'direction_id' => 3],
            ['codeserv' => '13', 'libelle' => 'Etudes, Développement logiciels métiers', 'direction_id' => 3],
            ['codeserv' => '14', 'libelle' => 'Administration Système Réseaux', 'direction_id' => 3],
            ['codeserv' => '15', 'libelle' => 'Directeur (trice) des Resources Humaines', 'direction_id' => 4],
            ['codeserv' => '16', 'libelle' => 'Administration du personnel', 'direction_id' => 4],
            ['codeserv' => '17', 'libelle' => 'Administration et Moyens Généraux', 'direction_id' => 4],
            ['codeserv' => '18', 'libelle' => 'Directeur des affaires directes', 'direction_id' => 5],
            ['codeserv' => '19', 'libelle' => 'Sécrétaire du DADR', 'direction_id' => 5],
            ['codeserv' => '20', 'libelle' => 'Développement et Recherche', 'direction_id' => 5],
            ['codeserv' => '21', 'libelle' => 'BancAssurance', 'direction_id' => 5],
            ['codeserv' => '22', 'libelle' => 'Grds Cpte et Réassurance', 'direction_id' => 5],
            ['codeserv' => '23', 'libelle' => 'Service Sinistre', 'direction_id' => 5],
            ['codeserv' => '24', 'libelle' => 'Force de ventes,Coordination des agences', 'direction_id' => 5],
            ['codeserv' => '25', 'libelle' => 'Bureau Direct Siège', 'direction_id' => 5],
            ['codeserv' => '26', 'libelle' => 'Bureau Direct Steinmetz', 'direction_id' => 5],
            ['codeserv' => '27', 'libelle' => 'Bureau Direct Nord', 'direction_id' => 5],
            ['codeserv' => '28', 'libelle' => 'Directeur du courtage', 'direction_id' => 6],
            ['codeserv' => '29', 'libelle' => 'Sécrétaire du DC', 'direction_id' => 6],
            ['codeserv' => '30', 'libelle' => 'Service Production', 'direction_id' => 6],
            ['codeserv' => '31', 'libelle' => 'Directeur Santé', 'direction_id' => 7],
            ['codeserv' => '32', 'libelle' => 'Chef Service Santé', 'direction_id' => 7],
            ['codeserv' => '33', 'libelle' => 'Service Règlement Sinistre', 'direction_id' => 7],
            ['codeserv' => '34', 'libelle' => 'Caisse', 'direction_id' => 2],
            ['codeserv' => '35', 'libelle' => 'Cellule Contröle Medical', 'direction_id' => 1],
            ['codeserv' => '36', 'libelle' => 'Direction Sinistre', 'direction_id' => 1],
            ['codeserv' => '37', 'libelle' => 'Service Automobile', 'direction_id' => 8],
            ['codeserv' => '38', 'libelle' => 'Service Recours et Contentieux', 'direction_id' => 8],
            ['codeserv' => '39', 'libelle' => 'Service Incendie,Risques Divers,Technique et Transports', 'direction_id' => 8],
            ['codeserv' => '40', 'libelle' => 'Cellule Réassurance', 'direction_id' => 1],
            ['codeserv' => '41', 'libelle' => 'Sécretariat Vie', 'direction_id' => 9],
            ['codeserv' => '42', 'libelle' => 'Directeur Vie', 'direction_id' => 9],
            ['codeserv' => '43', 'libelle' => 'Direction Vie', 'direction_id' => 1],
            ['codeserv' => '44', 'libelle' => 'Direction Générale Adjoint', 'direction_id' => 1],
        ];

        foreach ($services as $service) {
            Service::create([
                'codeserv' => $service['codeserv'],
                'libelle' => $service['libelle'],
                'direction_id' => $service['direction_id'],
                'is_direction' => $service['is_direction'] ?? false,
                'created_at' => null,
                'updated_at' => null,
            ]);
        }
    }
}
