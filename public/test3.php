<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
$debug = (bool)($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? true);

$kernel = new Kernel($env, $debug);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
$repo = $em->getRepository('App\Entity\Client\Vehicle');

try {
    $filters = new \stdClass();
    $filters->limit = 10;
    $filters->paymentStatus = 'En retard';

    $results = $repo->findCatalogByFilters($filters);
    echo "Vehicles count (En retard - EXPECTED NO SYNTAX ERROR): " . count($results) . "\n";

    echo "SUCCESS\n";
}
catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}