<?php

use App\Kernel;
use App\Entity\Client\MaintenanceType;

require __DIR__ . '/vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

$types = [
    'Vidange & Filtres', 'Plaquettes de frein', 'Révision Générale',
    'Changement Pneus', 'Révision pneumatiques', 'Contrôle technique',
    'Remplacement batterie', 'Freins & Disques', 'Électrique',
    'Carrosserie', 'Autre'
];

foreach ($types as $name) {
    echo "Processing: $name... ";
    $existing = $em->getRepository(MaintenanceType::class)->findOneBy(['name' => $name]);
    if (!$existing) {
        $type = new MaintenanceType();
        $type->setName($name);
        $em->persist($type);
        echo "Created.\n";
    }
    else {
        echo "Exists.\n";
    }
}

$em->flush();
echo "Done.\n";