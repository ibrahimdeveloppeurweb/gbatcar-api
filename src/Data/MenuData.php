<?php

namespace App\Data;

class MenuData
{
    /**
     * Représentation Backend du menu Angular (src/app/views/layout/sidebar/menu.ts)
     * Sert à synchroniser les accès dans la table Path.
     */
    public static function getMenu(): array
    {
        return [
            // Tableau de bord Principal
            [
                'label' => 'Tableau de Bord',
                'link' => '/gbatcar/dashboard',
                'nom' => 'MENU_DASHBOARD_MAIN'
            ],
            // Clients et Locataires
            [
                'label' => 'Clients et Locataires',
                'nom' => 'MENU_PARENT_CLIENTS',
                'subItems' => [
                    ['label' => 'Tableau de bord', 'link' => '/gbatcar/clients/dashboard', 'nom' => 'MENU_CLIENTS_DASHBOARD'],
                    ['label' => 'Tous les Clients', 'link' => '/gbatcar/clients', 'nom' => 'MENU_CLIENTS_LIST'],
                    ['label' => 'Demandes de Souscription', 'link' => '/gbatcar/clients/subscriptions', 'nom' => 'MENU_CLIENTS_SUBSCRIPTIONS']
                ]
            ],
            // Parc Automobile
            [
                'label' => 'Parc Automobile',
                'nom' => 'MENU_PARENT_VEHICLES',
                'subItems' => [
                    ['label' => 'Tableau de bord', 'link' => '/gbatcar/vehicles/dashboard', 'nom' => 'MENU_VEHICLES_DASHBOARD'],
                    ['label' => 'Catalogue & Arrivages', 'link' => '/gbatcar/vehicles/catalog', 'nom' => 'MENU_VEHICLES_CATALOG'],
                    ['label' => 'Flotte Active', 'link' => '/gbatcar/vehicles', 'nom' => 'MENU_VEHICLES_LIST'],
                    ['label' => 'Conformité & Visites', 'link' => '/gbatcar/vehicles/compliance', 'nom' => 'MENU_VEHICLES_COMPLIANCE']
                ]
            ],
            // Contrats & Dossiers
            [
                'label' => 'Contrats & Dossiers',
                'nom' => 'MENU_PARENT_CONTRACTS',
                'subItems' => [
                    ['label' => 'Tableau de bord', 'link' => '/gbatcar/contracts/dashboard', 'nom' => 'MENU_CONTRACTS_DASHBOARD'],
                    ['label' => 'Tous les Contrats', 'link' => '/gbatcar/contracts', 'nom' => 'MENU_CONTRACTS_LIST'],
                    ['label' => 'Suivi des Retards', 'link' => '/gbatcar/contracts/late', 'nom' => 'MENU_CONTRACTS_LATE']
                ]
            ],
            // Paiements & Trésorerie
            [
                'label' => 'Paiements & Trésorerie',
                'nom' => 'MENU_PARENT_PAYMENTS',
                'subItems' => [
                    ['label' => 'Tableau de bord', 'link' => '/gbatcar/payments/dashboard', 'nom' => 'MENU_PAYMENTS_DASHBOARD'],
                    ['label' => 'Encaissements', 'link' => '/gbatcar/payments', 'nom' => 'MENU_PAYMENTS_LIST'],
                    ['label' => 'Gestion des Pénalités', 'link' => '/gbatcar/payments/penalties', 'nom' => 'MENU_PAYMENTS_PENALTIES']
                ]
            ],
            // Maintenance
            [
                'label' => 'Maintenance',
                'nom' => 'MENU_PARENT_MAINTENANCE',
                'subItems' => [
                    ['label' => 'Tableau de bord', 'link' => '/gbatcar/maintenance/dashboard', 'nom' => 'MENU_MAINTENANCE_DASHBOARD'],
                    ['label' => 'Interventions', 'link' => '/gbatcar/maintenance', 'nom' => 'MENU_MAINTENANCE_LIST'],
                    ['label' => 'Alertes Sinistres', 'link' => '/gbatcar/maintenance/alerts', 'nom' => 'MENU_MAINTENANCE_ALERTS']
                ]
            ],
            // Administration
            [
                'label' => 'Administration',
                'nom' => 'MENU_PARENT_ADMIN',
                'subItems' => [
                    ['label' => 'Tableau de bord', 'link' => '/gbatcar/admin/dashboard', 'nom' => 'MENU_ADMIN_DASHBOARD'],
                    ['label' => 'Collaborateurs', 'link' => '/gbatcar/admin/users', 'nom' => 'MENU_ADMIN_USERS'],
                    ['label' => 'Permissions', 'link' => '/gbatcar/admin/permissions', 'nom' => 'MENU_ADMIN_PERMISSIONS'],
                    ['label' => 'Paramètres Globaux', 'link' => '/gbatcar/admin/settings', 'nom' => 'MENU_ADMIN_SETTINGS'],
                    ['label' => 'Notifications', 'link' => '/gbatcar/admin/notifications', 'nom' => 'MENU_ADMIN_NOTIFICATIONS']
                ]
            ],
            // Apps Web (On omet Inbox pour l'instant si non pertinent, ou on l'ajoute)
            [
                'label' => 'Chat',
                'link' => '/gbatcar/apps/chat',
                'nom' => 'MENU_APPS_CHAT'
            ],
            [
                'label' => 'Calendrier',
                'link' => '/gbatcar/apps/calendar',
                'nom' => 'MENU_APPS_CALENDAR'
            ]
        ];
    }
}