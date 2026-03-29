<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

$today = new \DateTimeImmutable('today');
$statuses = ['À venir', 'Partiel'];

echo "--- Diagnostic Overdue ---\n";
echo "Date du jour (utilisée pour comparaison) : " . $today->format('Y-m-d') . "\n";

$qb = $em->createQueryBuilder();
$results = $qb->select('s.id, s.expectedDate, s.status, c.reference, c.status as contractStatus, c.uuid')
    ->from('App\Entity\Client\PaymentSchedule', 's')
    ->join('s.contract', 'c')
    ->where('s.expectedDate < :today')
    ->andWhere('s.status IN (:statuses)')
    ->setParameter('today', $today)
    ->setParameter('statuses', $statuses)
    ->setMaxResults(20)
    ->getQuery()
    ->getResult();

if (empty($results)) {
    echo "AUCUNE échéance trouvée avec expectedDate < " . $today->format('Y-m-d') . " et status À venir/Partiel.\n";
    
    // Check if there are any installments at all
    $anyCount = $em->getRepository('App\Entity\Client\PaymentSchedule')->count([]);
    echo "Nombre total d'échéances en base : " . $anyCount . "\n";
    
    // Check one installment to see date format
    $one = $em->getRepository('App\Entity\Client\PaymentSchedule')->findOneBy([], ['id' => 'DESC']);
    if ($one) {
        echo "Exemple date d'une échéance : " . ($one->getExpectedDate() ? $one->getExpectedDate()->format('Y-m-d H:i:s') : 'NULL') . " | Status : '" . $one->getStatus() . "'\n";
    }
} else {
    echo "Trouvé " . count($results) . " échéances qui DEVRAIENT être passées en retard :\n";
    foreach ($results as $res) {
        echo sprintf("- ID: %d | Date: %s | Status: %s | Contrat: %s | Statut Contrat: %s\n",
            $res['id'],
            $res['expectedDate'] instanceof \DateTimeInterface ? $res['expectedDate']->format('Y-m-d') : 'Non-Date',
            $res['status'],
            ($res['reference'] ?? 'SANS REF'),
            $res['contractStatus']
        );
    }
}
