<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$repo = $container->get('App\Repository\Client\VehicleRepository');

$filters = new \stdClass();
$filters->limit = 10;
$filters->paymentStatus = 'À jour';

$results = $repo->findCatalogByFilters($filters);
echo "Vehicles count (A jour): " . count($results) . "\n";

$filters2 = new \stdClass();
$filters2->limit = 10;
$results2 = $repo->findCatalogByFilters($filters2);
echo "Vehicles count (All): " . count($results2) . "\n";

// Get the actual SQL
$activeStatusesList = "('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')";
$query = $repo->createQueryBuilder('v')
    ->leftJoin('v.client', 'c')
    ->leftJoin('v.contracts', 'ctr', 'WITH', 'ctr.status IN ' . $activeStatusesList)
    ->leftJoin('v.vehicleDemands', 'vd')
    ->leftJoin('vd.contract', 'ctrf', 'WITH', 'ctrf.status IN ' . $activeStatusesList)
    ->where('v.deletedAt IS NULL');

echo "SQL: " . $query->getQuery()->getSQL() . "\n";
echo "SQL: " . $query->getQuery()->getSQL() . "\n";