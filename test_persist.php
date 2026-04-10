<?php

use App\Kernel;
use App\Entity\Client\MaintenanceType;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

try {
    echo "Attempting to create MaintenanceType...\n";
    $type = new MaintenanceType();
    $type->setName("Test Type " . uniqid());

    // Check if uuid is set
    echo "UUID: " . ($type->getUuid() ?? 'NULL') . "\n";

    $em->persist($type);
    $em->flush();
    echo "Success! ID: " . $type->getId() . "\n";
}
catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}