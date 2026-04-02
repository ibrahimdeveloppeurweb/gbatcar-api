<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$kernel = new App\Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$repo = $container->get('doctrine')->getRepository(\App\Entity\Client\Vehicle::class);

$vehicles = $repo->findBy(['annee' => 2023], ['id' => 'DESC'], 10);
foreach ($vehicles as $v) {
    echo "Vehicle " . $v->getId() . " " . $v->getMarque() . " " . $v->getModele() . " stat: '" . $v->getStatut() . "'\n";
    $vd = $v->getVehicleDemands();
    foreach ($vd as $demand) {
        $c = $demand->getContract();
        echo "  Fleet Contract " . ($c ? $c->getId() : '') . " status: '" . ($c ? $c->getStatus() : 'null') . "'\n";
    }
    $ctr = $v->getContracts();
    foreach ($ctr as $c) {
        echo "  Direct Contract " . $c->getId() . " status: '" . $c->getStatus() . "'\n";
    }
}