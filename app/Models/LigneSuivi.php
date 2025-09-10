<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneSuivi extends Model
{
    protected $table = 'Ligne_Suivi';
    protected $primaryKey = 'Id_Ligne';

    public $timestamps = false;

    protected $fillable = [
        'Id_Ligne',
        'Code_Prestataire',
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
        'Code_Souscripteur',
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
        'Date_Demande' => 'datetime',
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
        'datecorretion' => 'datetime',
        'Montant_Ligne' => 'decimal:2',
        'montrejete' => 'decimal:2',
    ];

    /**
     * Relations à charger par défaut
     */
    protected $with = ['souscripteur', 'prestataire'];

    /**
     * Relation avec le souscripteur (via la table partenaires)
     */
    public function souscripteur()
    {
        return $this->belongsTo(Partenaire::class, 'Code_Souscripteur', 'id')
                    ->where('type', 'souscripteur');
    }

    /**
     * Relation avec le prestataire (via la table partenaires)
     */
    public function prestataire()
    {
        return $this->belongsTo(Partenaire::class, 'Code_Prestataire', 'id')
                    ->where('type', 'prestataire');
    }

    /**
     * Accessor pour récupérer le nom du souscripteur
     */
    public function getNomSouscripteurAttribute()
    {
        return $this->souscripteur?->nom ?? '';
    }

    /**
     * Accessor pour récupérer le nom du prestataire
     */
    public function getNomPrestataireAttribute()
    {
        return $this->prestataire?->nom ?? '';
    }

    /**
     * Accessor pour déterminer l'affichage selon le profil
     */
    public function getAffichageAssureOuPrestataireAttribute()
    {
        return $this->Nom_Assure ?: ($this->prestataire?->nom ?? '');
    }

    /**
     * Accessor pour déterminer l'affichage selon le profil
     */
    public function getAffichageSouscripteurOuReferenceAttribute()
    {
        return $this->souscripteur?->nom ?: $this->Reference_Facture;
    }

    public function redacteur()
    {
        return $this->belongsTo(\App\Models\User::class, 'Redacteur');
    }

    /**
     * Détermine si la facture est déjà traitée
     *
     * @return bool
     */
    public function estTraitee()
    {
        return $this->Statut_Ligne == 1;
    }

    /**
     * Accessor pour savoir si la facture est traitée
     */
    public function getEstTraiteeAttribute()
    {
        return $this->estTraitee();
    }
}
