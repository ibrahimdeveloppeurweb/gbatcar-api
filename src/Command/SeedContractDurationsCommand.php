<?php

namespace App\Command;

use App\Entity\Client\ContractDuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SeedContractDurationsCommand extends Command
{
    protected static $defaultName = 'app:seed-contract-durations';
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setDescription('Seed default contract durations (1 to 90 months)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        for ($i = 1; $i <= 90; $i++) {
            $name = "$i mois";
            $existing = $this->em->getRepository(ContractDuration::class)->findOneBy(['monthsCount' => $i]);
            if (!$existing) {
                $duration = new ContractDuration();
                $duration->setName($name);
                $duration->setMonthsCount($i);
                $this->em->persist($duration);
                $io->info("Created: $name");
            }
            else {
                $io->note("Exists: $name");
            }
        }

        $this->em->flush();
        $io->success('Contract durations seeding completed successfully!');

        return Command::SUCCESS;
    }
}