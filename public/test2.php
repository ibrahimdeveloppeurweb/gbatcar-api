<?php
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
$repo = $em->getRepository('App\Entity\Client\Vehicle');

$filters = new \stdClass();
$filters->limit = 10;
$filters->paymentStatus = 'À jour';

$results = $repo->findCatalogByFilters($filters);
echo "Vehicles count (A jour): " . count($results) . "\n";

$filters2 = new \stdClass();
$filters2->limit = 10;
$results2 = $repo->findCatalogByFilters($filters2);
echo "Vehicles count (All): " . count($results2) . "\n";

$query = $repo->createQueryBuilder('v')
    ->leftJoin('v.client', 'c')
    ->leftJoin('v.contracts', 'ctr', 'WITH', "ctr.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')")
    ->leftJoin('v.vehicleDemands', 'vd')
    ->leftJoin('vd.contract', 'ctrf', 'WITH', "ctrf.status IN ('ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'Actif', 'En cours', 'Validé')")
    ->where('v.deletedAt IS NULL');

echo "SQL: " . $query->getQuery()->getSQL() . "\n";
