<?php

namespace App\Command;

use App\Entity\Client\Client;
use App\Entity\Client\Vehicle;
use App\Manager\Client\ContractManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestContractValidationCommand extends Command
{
    protected static $defaultName = 'app:test-contract-validation';
    protected static $defaultDescription = 'Tests the split payment logic during contract validation';

    private $em;
    private $contractManager;

    public function __construct(EntityManagerInterface $em, ContractManager $contractManager)
    {
        parent::__construct();
        $this->em = $em;
        $this->contractManager = $contractManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Testing Contract Validation Split Payments');
        $io->note('Command EM Hash: ' . spl_object_hash($this->em));

        // 1. Setup Mock data
        $client = $this->em->getRepository(Client::class)->findOneBy([]);
        if (!$client) {
            $client = new Client();
            $client->setFirstName('Test');
            $client->setLastName('Client');
            $this->em->persist($client);
        }

        $vehicle = $this->em->getRepository(Vehicle::class)->findOneBy([]);
        if (!$vehicle) {
            $vehicle = new Vehicle();
            $vehicle->setImmatriculation('TEST-' . rand(100, 999));
            $this->em->persist($vehicle);
        }
        $this->em->flush();

        // 2. Create Contract
        $data = (object)[
            'clientId' => $client->getUuid(),
            'vehicleId' => $vehicle->getUuid(),
            'startDate' => (new \DateTimeImmutable())->format('Y-m-d'),
            'duration' => 12,
            'dailyRate' => 1000,
            'cautionAmount' => 200000,
            'fraisDossier' => 25000,
            'paymentFrequency' => 'Monthly',
            'notes' => 'Test contract validation'
        ];

        $contract = $this->contractManager->create($data);
        $io->note('Contract created: ' . $contract->getReference());
        $io->note('Total Amount (excludes fees): ' . $contract->getTotalAmount());

        // 3. Validate Contract
        $this->contractManager->validate($contract);
        $this->em->refresh($contract);
        $io->success('Contract validated.');

        // 4. Check Payments
        $payments = $contract->getPayments();
        $io->section('Payments generated:');
        foreach ($payments as $p) {
            $io->text("- Type: {$p->getType()} | Amount: {$p->getAmount()} | Status: {$p->getStatus()}");
        }

        $io->note('Final Contract Paid Amount: ' . $contract->getPaidAmount());

        return Command::SUCCESS;
    }
}