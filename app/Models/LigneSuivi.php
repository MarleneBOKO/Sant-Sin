<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneSuivi extends Model
{
    protected $table = 'Ligne_Suivi';
    protected $primaryKey = 'Id_Ligne';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Id_Ligne',
        'Code_Partenaire', // Nouveau : remplace Code_Prestataire et Code_Souscripteur
        'Reference_Facture',
        'Mois_Facture',
        'Date_Debut',
        'Date_Fin',
        'Montant_Ligne',
        'Date_Enregistrement',
        'Numero_demande',
        'Date_Demande',
        'Montant_Reglement',
        'Date_Transmission',
        'Date_Cloture',
        'Numero_Cheque',
        'Statut_Ligne',
        'Nom_Assure',
        'Redacteur',
        'Numero_Reception',
        'is_evac',
        'Date_Depot',
        'delai_traitement',
        'Date_fin_reglementaire',
        'rejete',
        'motif_rejet',
        'Code_Banque',
        'Code_motifrejet',
        'date_rejet',
        'Annee_Facture',
        'montrejete',
        'userrejet',
        'annuler',
        'datetransMedecin',
        'dateEnregMedecin',
        'usertransMedecin',
        'dateEnregRMedecin',
        'usertransRMedecin',
        'dateRetourMedecin',
        'userSaisieReg',
        'datesaisiereg',
        'usertransmi',
        'dateenrtrans',
        'usercorretion',
        'datecorretion',
        'motifcorretion',
        'userEnregM',
        'code_etape',
        'nbfacture',
        'anneecourrier',
        'CodeCour',
    ];

    protected $casts = [
        'Date_Debut' => 'datetime',
        'Date_Fin' => 'datetime',
        'Date_Enregistrement' => 'datetime',
        //'Date_Demande' => 'datetime', // Removed to prevent casting issues with nvarchar column
         'Date_Transmission' => 'datetime',
        'Date_Cloture' => 'datetime',
        'Date_Depot' => 'datetime',
        'Date_fin_reglementaire' => 'datetime',
        'date_rejet' => 'datetime',
        'datetransMedecin' => 'date',
        'dateEnregMedecin' => 'date',
        'dateEnregRMedecin' => 'date',
        'dateRetourMedecin' => 'date',
        'datesaisiereg' => 'date',
        'dateenrtrans' => 'datetime',
       // 'datecorretion' => 'datetime',
        'Montant_Ligne' => 'decimal:2',
        'montrejete' => 'decimal:2',
         'Numero_Reception' => 'string',
         'Reference_Facture' => 'string'
    ];

    /**
     * Relations à charger par défaut (optionnel, ajustez si nécessaire)
     */
    protected $with = ['partenaire']; // Changé de ['souscripteur', 'prestataire'] à ['partenaire']

    /**
     * Relation unifiée avec le partenaire (remplace souscripteur et prestataire)
     * Utilise Code_partenaire pour lier à partenaires.id
     */
    public function partenaire()
    {
        return $this->belongsTo(Partenaire::class, 'Code_Partenaire', 'id');
    }

    /**
     * Accessor pour récupérer le nom du souscripteur (si type = 'souscripteur')
     */
    public function getNomSouscripteurAttribute()
    {
        return ($this->partenaire && $this->partenaire->type === 'souscripteur') ? $this->partenaire->nom : '';
    }

    /**
     * Accessor pour récupérer le nom du prestataire (si type = 'prestataire')
     */
    public function getNomPrestataireAttribute()
    {
        return ($this->partenaire && $this->partenaire->type === 'prestataire') ? $this->partenaire->nom : '';
    }

    /**
     * Accessor pour déterminer l'affichage selon le profil (Assuré ou Prestataire)
     */
    public function getAffichageAssureOuPrestataireAttribute()
    {
        return $this->Nom_Assure ?: $this->nomPrestataire; // Utilise l'accessor nomPrestataire
    }

    /**
     * Accessor pour déterminer l'affichage selon le profil (Souscripteur ou Référence)
     */
    public function getAffichageSouscripteurOuReferenceAttribute()
    {
        return $this->nomSouscripteur ?: $this->Reference_Facture; // Utilise l'accessor nomSouscripteur
    }

    /**
     * Relation avec le rédacteur (User)
     */
    /*public function redacteur()
    {
        return $this->belongsTo(\App\Models\User::class, 'Redacteur');
    }*/

    /**
     * Détermine si la facture est déjà traitée
     *
     * @return bool
     */
    public function estTraitee()
    {
        return in_array($this->Statut_Ligne, [1, 4]); // 1 = traité, 4 = clôturé
    }

    /**
     * Accessor pour savoir si la facture est traitée
     */
    public function getEstTraiteeAttribute()
    {
        return $this->estTraitee();
    }






    public function redacteur()
    {
        // Éviter l'erreur SQL en ne chargeant que si c'est numérique
        if ($this->Redacteur && is_numeric($this->Redacteur)) {
            return $this->belongsTo(User::class, 'Redacteur', 'id');
        }

        return null;
    }

    /**
     * Accessor intelligent pour obtenir le nom du rédacteur
     * Gère automatiquement ID numérique OU nom en texte
     */
    public function getRedacteurNomAttribute()
    {
        // Si le champ est vide
        if (empty($this->Redacteur)) {
            return 'Non défini';
        }

        // Si c'est un ID numérique, récupérer le nom de l'utilisateur
        if (is_numeric($this->Redacteur)) {
            try {
                $user = User::find($this->Redacteur);
                return $user ? $user->name : 'Utilisateur #' . $this->Redacteur;
            } catch (\Exception $e) {
                \Log::error('Erreur chargement rédacteur', [
                    'id' => $this->Redacteur,
                    'ligne_id' => $this->Id_Ligne,
                    'error' => $e->getMessage()
                ]);
                return 'Erreur chargement (ID: ' . $this->Redacteur . ')';
            }
        }

        // Sinon, c'est du texte direct (ancien format), le retourner tel quel
        return $this->Redacteur;
    }

    /**
     * Vérifier si le rédacteur est un ID valide
     */
    public function getHasValidRedacteurAttribute()
    {
        return !empty($this->Redacteur) && is_numeric($this->Redacteur);
    }


    /**
     * Scope pour éviter le chargement automatique du rédacteur
     */
    public function scopeWithoutRedacteur($query)
    {
        return $query->without('redacteur');
    }

    /**
     * Relation avec le prestataire (si type = 'prestataire')
     */
    public function prestataire()
    {
        return $this->belongsTo(Partenaire::class, 'Code_Partenaire')->where('type', 'prestataire');
    }
    /**
     * Relation avec le souscripteur (si type = 'souscripteur')
     */
    public function souscripteur()
    {
        return $this->belongsTo(Partenaire::class, 'Code_Partenaire')->where('type', 'souscripteur');
    }
}




