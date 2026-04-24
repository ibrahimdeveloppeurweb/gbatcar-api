<?php

namespace App\Helpers;

/**
 * RouteHelper - Aide à la gestion des permissions de routes GbatCar.
 *
 * Ce helper regroupe les routes API par domaine fonctionnel.
 * Il est utilisé dans UserFixture et RoleManager pour attribuer
 * les bons droits d'accès aux rôles de la plateforme.
 *
 * Architecture des routes GbatCar :
 * - /api/private/admin/...     → Routes d'administration (gestion des utilisateurs, rôles, etc.)
 * - /api/private/vehicule/...  → Gestion de la flotte de véhicules
 * - /api/private/contrat/...   → Gestion des contrats de location
 * - /api/private/client/...    → Gestion des clients (locataires)
 * - /api/private/paiement/...  → Gestion des paiements
 * - /api/private/maintenance/... → Suivi de la maintenance
 * - /api/private/incident/...  → Gestion des incidents
 * - /api/private/extra/...     → Paramètres et fonctionnalités transversales
 */
class RouteHelper
{
    // =========================================================================
    // MÉTHODES UTILITAIRES INTERNES
    // =========================================================================

    /**
     * Filtre les routes en excluant celles correspondant aux actions sensibles.
     * Actions exclues : delete, validate, activate
     */
    private static function excludeSensitiveActions(array $paths): array
    {
        return array_values(array_filter($paths, function ($path) {
            return !preg_match('#delete|validate|activate#', $path->getNom());
        }));
    }

    /**
     * Filtre une liste de routes à partir d'un tableau de patterns regex.
     */
    private static function filterByPatterns(array $paths, array $patterns): array
    {
        $result = [];
        foreach ($paths as $path) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $path->getChemin())) {
                    $result[] = $path;
                    break;
                }
            }
        }
        return $result;
    }

    // =========================================================================
    // SUPER ADMIN — Accès complet à toutes les routes privées
    // =========================================================================

    /**
     * Toutes les routes privées de l'administration.
     * Réservé au Super Administrateur GbatCar.
     */
    public static function ADMIN_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private#',
            '#^/printer#',
            '#^/admin#',
        ]);
    }

    // =========================================================================
    // MANAGER — Accès opérationnel (sans gestion des utilisateurs/rôles)
    // =========================================================================

    /**
     * Routes accessibles au Gestionnaire/Manager.
     * Accès complet aux modules métier, sans gestion des droits d'accès.
     */
    public static function MANAGER_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private/vehicule#',
            '#^/api/private/contrat#',
            '#^/api/private/client#',
            '#^/api/private/paiement#',
            '#^/api/private/maintenance#',
            '#^/api/private/incident#',
            '#^/api/private/extra/shared#',
        ]);
    }

    /**
     * Routes Manager avec actions sensibles bloquées (delete, validate, activate).
     * Pour un Manager en mode lecture/écriture sans suppression.
     */
    public static function MANAGER_RESTRICTED_ROUTE(array $paths): array
    {
        return self::excludeSensitiveActions(self::MANAGER_ROUTE($paths));
    }

    // =========================================================================
    // VEHICULES — Gestion de la flotte
    // =========================================================================

    /**
     * Routes liées à la gestion des véhicules.
     */
    public static function VEHICULE_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private/vehicule#',
            '#^/api/private/maintenance#',
            '#^/api/private/incident#',
        ]);
    }

    /**
     * Routes Véhicules sans actions sensibles.
     */
    public static function VEHICULE_RESTRICTED_ROUTE(array $paths): array
    {
        return self::excludeSensitiveActions(self::VEHICULE_ROUTE($paths));
    }

    // =========================================================================
    // CONTRATS — Gestion des locations
    // =========================================================================

    /**
     * Routes liées aux contrats de location.
     */
    public static function CONTRAT_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private/contrat#',
            '#^/api/private/client#',
            '#^/api/private/paiement#',
        ]);
    }

    /**
     * Routes Contrats sans actions sensibles.
     */
    public static function CONTRAT_RESTRICTED_ROUTE(array $paths): array
    {
        return self::excludeSensitiveActions(self::CONTRAT_ROUTE($paths));
    }

    // =========================================================================
    // CLIENTS — Gestion des locataires
    // =========================================================================

    /**
     * Routes liées à la gestion des clients/locataires.
     */
    public static function CLIENT_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private/client#',
        ]);
    }

    /**
     * Routes Clients sans actions sensibles.
     */
    public static function CLIENT_RESTRICTED_ROUTE(array $paths): array
    {
        return self::excludeSensitiveActions(self::CLIENT_ROUTE($paths));
    }

    // =========================================================================
    // PAIEMENTS — Gestion financière
    // =========================================================================

    /**
     * Routes liées aux paiements et à la trésorerie.
     */
    public static function PAIEMENT_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private/paiement#',
        ]);
    }

    /**
     * Routes Paiements sans actions sensibles.
     */
    public static function PAIEMENT_RESTRICTED_ROUTE(array $paths): array
    {
        return self::excludeSensitiveActions(self::PAIEMENT_ROUTE($paths));
    }

    // =========================================================================
    // MAINTENANCE — Suivi technique
    // =========================================================================

    /**
     * Routes liées à la maintenance et aux incidents.
     */
    public static function MAINTENANCE_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private/maintenance#',
            '#^/api/private/incident#',
        ]);
    }

    /**
     * Routes Maintenance sans actions sensibles.
     */
    public static function MAINTENANCE_RESTRICTED_ROUTE(array $paths): array
    {
        return self::excludeSensitiveActions(self::MAINTENANCE_ROUTE($paths));
    }

    // =========================================================================
    // EXTRA — Routes transversales (paramètres, recherche, notifications)
    // =========================================================================

    /**
     * Routes transversales accessibles à tous les utilisateurs authentifiés.
     */
    public static function EXTRA_ROUTE(array $paths): array
    {
        return self::filterByPatterns($paths, [
            '#^/api/private/extra#',
        ]);
    }

    /**
     * Toutes les routes liées au menu frontend (MenuData).
     */
    public static function MENU_ROUTE(array $paths): array
    {
        return array_values(array_filter($paths, function ($path) {
            return strpos($path->getNom(), 'MENU_') === 0;
        }));
    }
}