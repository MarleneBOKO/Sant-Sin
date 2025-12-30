<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Notification;
use App\Models\LigneSuivi;
use App\Models\DelaiTraitement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Vérifier les délais et créer des notifications
     */
    public function verifierDelais()
    {
        $factures = LigneSuivi::whereIn('Statut_Ligne', [0, 5, 1, 2])
            ->whereNull('rejete')
            ->get();

        foreach ($factures as $facture) {
            $this->verifierDelaiFacture($facture);
        }
    }

    /**
     * Vérifier le délai pour une facture spécifique
     */
    public function verifierDelaiFacture(LigneSuivi $facture)
    {
        $isIndividuel = !empty($facture->Nom_Assure);
        $codeEtape = $this->getCodeEtapeFromStatut($facture->Statut_Ligne);

        $delai = DelaiTraitement::getDelaiByEtape($codeEtape, $isIndividuel);

        if (!$delai) {
            return;
        }

        $dateDebut = $this->getDateDebutEtape($facture);

        if (!$dateDebut) {
            return;
        }

        $joursEcoules = Carbon::parse($dateDebut)->diffInDays(Carbon::now());
        $pourcentageDelai = ($joursEcoules / $delai->jourscalendaire) * 100;

        // Notification si > 80% du délai
        if ($pourcentageDelai >= 80 && $pourcentageDelai < 100) {
            $this->creerNotificationDelaiApproche($facture, $delai, $joursEcoules);
        }

        // Notification si délai dépassé
        if ($pourcentageDelai >= 100) {
            $this->creerNotificationDelaiDepasse($facture, $delai, $joursEcoules);
        }
    }

    /**
     * Créer notification lors d'un changement de statut
     */
// app/Services/NotificationService.php

public function notifierChangementStatut(LigneSuivi $facture, $ancienStatut, $nouveauStatut)
{
    $messages = [
        0 => 'Facture enregistrée',
        5 => 'Facture transmise au médecin',
        6 => 'Retour médecin reçu',
        1 => 'Facture traitée',
        2 => 'Facture transmise à la trésorerie',
        3 => 'Facture réglée',
        4 => 'Facture clôturée',
    ];

    $usersAConcerner = $this->getUsersForStatut($nouveauStatut);

    // CAS 1 : Pas d'utilisateurs trouvés -> On notifie l'admin actuel
    if ($usersAConcerner->isEmpty()) {
        $adminUser = auth()->user();
        if ($adminUser) {
            try {
                Notification::create([
                    'user_id'    => $adminUser->id, // Correction ici
                    'facture_id' => $facture->Id_Ligne,
                    'type'       => 'changement_statut',
                    'titre'      => 'Nouvelle action requise (Admin)',
                    'message'    => "Facture #{$facture->Reference_Facture} : {$messages[$nouveauStatut]}",
                    'priorite'   => 'moyenne',
                    'lue'        => false,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur notification admin: ' . $e->getMessage());
            }
        }
        return;
    }

    // CAS 2 : Notification des utilisateurs concernés
    foreach ($usersAConcerner as $user) {
        try {
            Notification::create([
                'user_id'    => $user->id,
                'facture_id' => $facture->Id_Ligne,
                'type'       => 'changement_statut',
                'titre'      => 'Nouvelle action requise',
                'message'    => "Facture #{$facture->Reference_Facture} : {$messages[$nouveauStatut]}",
                'priorite'   => 'moyenne',
                'lue'        => false,
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur notification user {$user->id}: " . $e->getMessage());
        }
    }
}


    /**
     * Notification délai approche (80%)
     */
    private function creerNotificationDelaiApproche(LigneSuivi $facture, $delai, $joursEcoules)
    {
        $joursRestants = $delai->jourscalendaire - $joursEcoules;

        // Vérifier si notification déjà envoyée
        $existe = Notification::where('facture_id', $facture->Id_Ligne)
            ->where('type', 'delai_approche')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->exists();

        if ($existe) {
            return;
        }

        $users = $this->getUsersForStatut($facture->Statut_Ligne);

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'facture_id' => $facture->Id_Ligne,
                'type' => 'delai_approche',
                'titre' => '⚠️ Délai bientôt dépassé',
                'message' => "Facture #{$facture->Reference_Facture} : Plus que {$joursRestants} jour(s) pour {$delai->operation}",
                'priorite' => 'haute',
                'date_limite' => Carbon::now()->addDays($joursRestants),
                'lue' => false,
            ]);
        }
    }

    /**
     * Notification délai dépassé
     */
    private function creerNotificationDelaiDepasse(LigneSuivi $facture, $delai, $joursEcoules)
    {
        // Vérifier si notification déjà envoyée
        $existe = Notification::where('facture_id', $facture->Id_Ligne)
            ->where('type', 'delai_depassement')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->exists();

        if ($existe) {
            return;
        }

        $joursDepassement = $joursEcoules - $delai->jourscalendaire;
        $users = $this->getUsersForStatut($facture->Statut_Ligne);

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'facture_id' => $facture->Id_Ligne,
                'type' => 'delai_depassement',
                'titre' => '🚨 DÉLAI DÉPASSÉ',
                'message' => "Facture #{$facture->Reference_Facture} : Délai dépassé de {$joursDepassement} jour(s) pour {$delai->operation}",
                'priorite' => 'haute',
                'lue' => false,
            ]);
        }
    }

    /**
     * Convertir statut en code étape
     */
    private function getCodeEtapeFromStatut($statut)
    {
        $mapping = [
            0 => 0, // Reception-Enregistrement
            5 => 1, // Médecins
            6 => 1, // Médecins (retour)
            1 => 2, // Traitement (Régleur)
            2 => 3, // Trésorerie
        ];

        return $mapping[$statut] ?? null;
    }

    /**
     * Obtenir la date de début de l'étape
     */
    private function getDateDebutEtape(LigneSuivi $facture)
    {
        switch ($facture->Statut_Ligne) {
            case 0:
                return $facture->Date_Enregistrement;
            case 5:
                return $facture->datetransMedecin;
            case 6:
                return $facture->dateRetourMedecin;
            case 1:
                return $facture->Date_Demande;
            case 2:
                return $facture->Date_Transmission;
            default:
                return null;
        }
    }

    /**
     * Obtenir les utilisateurs concernés par un statut
     */
    private function getUsersForStatut($statut)
    {
        $profiles = [];

        switch ($statut) {
            case 0:
            case 5:
            case 6:
                $profiles = ['RSI', 'RSTP']; // Régleurs
                break;
            case 1:
            case 2:
                $profiles = ['TRESO']; // Trésoriers
                break;
        }

        return User::whereHas('profil', function ($query) use ($profiles) {
            $query->whereIn('code_profil', $profiles);
        })->get();
    }
}
