<?php

use App\Entity\Client\Contract;
use App\Entity\Client\Payment;
use App\Kernel;

require __DIR__ . '/vendor/autoload.php';

// Set environment variables for the kernel
putenv('DATABASE_URL=mysql://root:root@127.0.0.1:8889/gbatcar');
$_ENV['DATABASE_URL'] = 'mysql://root:root@127.0.0.1:8889/gbatcar';

$contractUuid = 'c3a00e1a-c9fc-47e1-bcd8-0abde9609b9a';

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

$contract = $em->getRepository(Contract::class)->findOneByUuid($contractUuid);

if ($contract) {
    // Manually calculate balance using the same logic as PaymentManager
    $payments = $em->getRepository(Payment::class)->findBy(['contract' => $contract, 'status' => 'VALIDÉ']);
    $totalPaid = 0;
    $hasFeePayment = false;
    foreach ($payments as $p) {
        if ($p->getType() === 'Frais de dossier') {
            $hasFeePayment = true;
            continue;
        }
        $totalPaid += $p->getAmount();
    }

    if (!$hasFeePayment && $contract->getFraisDossier() > 0) {
        $totalPaid = max(0, $totalPaid - $contract->getFraisDossier());
    }

    $contract->setPaidAmount($totalPaid);

    $em->flush();
    echo "Contract {$contract->getReference()} balance updated. New Paid Amount: $totalPaid\n";
}
else {
    echo "Contract not found.\n";
}