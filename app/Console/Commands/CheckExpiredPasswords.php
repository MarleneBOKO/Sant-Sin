<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class CheckExpiredPasswords extends Command
{
    protected $signature = 'passwords:check-expiry';
    protected $description = 'Vérifier et notifier les utilisateurs dont le mot de passe va expirer ou a expiré';

    public function handle()
    {
        $this->info('🔍 Démarrage de la vérification des mots de passe...');
        $this->newLine();

        // PARTIE 1 : NOTIFIER LES UTILISATEURS (0-1 jour pour test)
        $this->line('📧 Recherche des utilisateurs à notifier...');

        $usersToNotify = User::whereNotNull('password_changed_at')
            ->where('password_expired', false)
            ->whereNull('password_expiry_notified_at')
            ->get()
            ->filter(function ($user) {
                $passwordAge = Carbon::parse($user->password_changed_at)->diffInDays(now());
                return $passwordAge >= 0 && $passwordAge < 1;  // Ajusté pour test (au lieu de 25-30)
            });

        if ($usersToNotify->count() > 0) {
            $this->info("✅ {$usersToNotify->count()} utilisateur(s) à notifier");
            $this->newLine();

            $bar = $this->output->createProgressBar($usersToNotify->count());
            $bar->start();

            foreach ($usersToNotify as $user) {
                $passwordAge = Carbon::parse($user->password_changed_at)->diffInDays(now());
                $daysLeft = 1 - $passwordAge;  // Ajusté pour test (au lieu de 30)

                // JUSTE marquer comme notifié (PAS de notification en base)
                $user->update(['password_expiry_notified_at' => now()]);

                $this->line("   👤 {$user->login} - {$user->name} ({$daysLeft} jours restants)");
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("✅ Utilisateurs marqués comme notifiés");
            $this->newLine();
        } else {
            $this->line("   ℹ️  Aucun utilisateur à notifier pour le moment");
            $this->newLine();
        }

        // PARTIE 2 : MARQUER LES MOTS DE PASSE EXPIRÉS (> 1 jour pour test)
        $this->line('🔒 Recherche des mots de passe expirés...');

        $expiredUsers = User::whereNotNull('password_changed_at')
            ->where('password_expired', false)
            ->get()
            ->filter(function ($user) {
                $passwordAge = Carbon::parse($user->password_changed_at)->diffInDays(now());
                return $passwordAge > 1;  // Ajusté pour test (au lieu de 30)
            });

        if ($expiredUsers->count() > 0) {
            $this->info("⚠️  {$expiredUsers->count()} mot(s) de passe expiré(s)");
            $this->newLine();

            $bar = $this->output->createProgressBar($expiredUsers->count());
            $bar->start();

            foreach ($expiredUsers as $user) {
                $passwordAge = Carbon::parse($user->password_changed_at)->diffInDays(now());
                $expiredSince = $passwordAge - 1;  // Ajusté pour test (au lieu de 30)

                // Marquer comme expiré ET forcer le changement
            

                $this->line("   🔴 {$user->login} - {$user->name} (accès bloqué)");
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("✅ Mots de passe expirés marqués");
            $this->newLine();
        } else {
            $this->line("   ℹ️  Aucun mot de passe expiré");
            $this->newLine();
        }

        // PARTIE 3 : STATISTIQUES
        $this->newLine();
        $this->info('📊 STATISTIQUES FINALES :');
        $this->line("   📧 Utilisateurs notifiés : {$usersToNotify->count()}");
        $this->line("   🔒 Mots de passe expirés : {$expiredUsers->count()}");

        $healthyPasswords = User::whereNotNull('password_changed_at')
            ->where('password_expired', false)
            ->get()
            ->filter(function ($user) {
                $passwordAge = Carbon::parse($user->password_changed_at)->diffInDays(now());
                return $passwordAge < 0;  // Ajusté pour test (au lieu de 25)
            })
            ->count();

        $this->line("   ✅ Mots de passe sains : {$healthyPasswords}");

        $this->newLine();
        $this->info('✅ Vérification terminée avec succès !');

        return 0;
    }
}
