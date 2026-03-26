<?php

use App\Entity\Client\Contract;
use App\Entity\Client\Client;
use App\Entity\Client\Vehicle;
use App\Kernel;
use DateTimeImmutable;

require __DIR__ . '/vendor/autoload.php';

// Set environment variables for the kernel
putenv('DATABASE_URL=mysql://root:root@127.0.0.1:8889/gbatcar');
$_ENV['DATABASE_URL'] = 'mysql://root:root@127.0.0.1:8889/gbatcar';

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$contractManager = $container->get('App\Manager\Client\ContractManager');

// 1. Setup Mock data (Client & Vehicle)
$client = $em->getRepository(Client::class)->findOneBy([]);
if (!$client) {
    $client = new Client();
    $client->setFirstName('Test');
    $client->setLastName('Client');
    $em->persist($client);
}

$vehicle = $em->getRepository(Vehicle::class)->findOneBy([]);
if (!$vehicle) {
    $vehicle = new Vehicle();
    $vehicle->setImmatriculation('TEST-' . rand(100, 999));
    $vehicle->setMarque('Toyota');
    $vehicle->setModele('Corolla');
    $em->persist($vehicle);
}
$em->flush();

// 2. Create Contract
$data = (object)[
    'clientId' => $client->getUuid(),
    'vehicleId' => $vehicle->getUuid(),
    'startDate' => (new DateTimeImmutable())->format('Y-m-d'),
    'durationInMonths' => 12,
    'dailyRate' => 1000,
    'caution' => 200000,
    'fraisDossier' => 25000,
    'paymentFrequency' => 'Monthly'
];

$contract = $contractManager->create($data);
echo "Contract created: {$contract->getReference()}\n";
echo "Total Amount (expected: 12000 + 175000 = 187000? No, 12,000 rent + 175,000 caution): " . $contract->getTotalAmount() . "\n";

// 3. Validate Contract
$contractManager->validate($contract);
echo "Contract validated.\n";

// 4. Check Payments
$payments = $contract->getPayments();
echo "Number of payments: " . count($payments) . "\n";
foreach ($payments as $p) {
    echo "- Payment: {$p->getType()} | Amount: {$p->getAmount()} | Status: {$p->getStatus()}\n";
}

echo "Final Contract Paid Amount: " . $contract->getPaidAmount() . "\n";