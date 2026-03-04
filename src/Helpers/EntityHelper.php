<?php

namespace App\Helpers;

use App\Entity\Client\Home;
use App\Entity\Client\Invoice;
use App\Entity\Client\Customer;
use App\Entity\Client\Promotion;
use App\Entity\Client\Subdivision;
use App\Entity\Client\InvoiceFolder;
use App\Entity\Client\OptionFolder;
use App\Entity\Client\FolderCustomer;

class EntityHelper
{
  

    const ETAT = [
        'ACTIF' => 'ACTIF',
        'INACTIF' => 'INACTIF'
    ];

    const STATUS = [
        'OUVERT' => 'OUVERT',
        'CLOS' => 'CLOS'
    ];

    const CONCERN = [
        'AGENCY' => 'AGENCY',
        'TRUSTEE' => 'TRUSTEE'
    ];

    const DEFAULT_ACCOUNT = [
        'SYNDIC' => 'Compte comptable par défaut pour les tiers "syndics"',
        'FOURNISSEUR' => 'Compte comptable par défaut pour les tiers "fournisseurs"',
        'SALARIE' => 'Compte comptable par défaut pour les tiers "salariés"',
        'PRODUIT_VENDU' => 'Compte comptable par défaut pour les produits vendus (utilisé si non défini dans la fiche produit)',
        'PRODUIT_VENDU_EXPORTE' => 'Compte comptable par défaut pour les produits vendus et exportés hors de la CEE (utilisé si non définie dans la fiche produit)',
        'PRODUIT_ACHETE' => 'Compte comptable par défaut pour les produits achetés dans le même pays (utilisé si non défini dans la fiche produit)',
        'PRODUIT_ACHETE_IMPORTE' => 'Compte comptable par défaut pour les produits achetés et importés hors de la CEE (utilisé si non défini dans la fiche produit)',
        'SERVICE_VENDU' => 'Compte comptable par défaut pour les services vendus (utilisé si non défini dans la fiche service)',
        'SERVICE_VENDU_EXPORTE' => 'Compte comptable par défaut pour les services vendus et exportés hors de la CEE (utilisé si non définie dans la fiche produit)',
        'SERVICE_ACHETE' => 'Compte comptable par défaut pour les services achetés dans le pays (utilisé si non défini dans la fiche service)',
        'SERVICE_ACHETE_IMPORTE' => 'Compte comptable par défaut pour les services achetés et importés hors de la CEE (utilisé si non défini dans la fiche service)',
        'TVA_ACHAT' => 'Compte comptable par défaut pour la TVA sur les achats (utilisé s\'il n\'est pas défini dans la configuration du dictionnaire TVA)',
        'TVA_VENTE' => 'Compte comptable par défaut pour la TVA sur les ventes (utilisé s\'il n\'est pas défini dans la configuration du dictionnaire TVA)',
        'PAIEMENT_TVA' => 'Compte comptable par défaut pour le paiement de la TVA',
        'VIREMENT_INTERNE' => 'Compte comptable par défaut pour les virements internes',
        'DONS' => 'Compte comptable pour la comptabilisation des dons',
        'CAPITAL' => 'Compte comptable à utiliser par défaut pour le capital (module emprunt)',
        'INTERET' => 'Compte comptable à utiliser par défaut pour les intérêts (module emprunt)',
        'ASSURANCE' => 'Compte comptable à utiliser par défaut pour l\'assurance (module emprunt)',
        'OPERATION_ATTENTE' => 'Compte comptable par défaut pour les opérations en attente'
    ];
}
