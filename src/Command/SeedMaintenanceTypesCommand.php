<?php

namespace App\Command;

use App\Entity\Client\MaintenanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SeedMaintenanceTypesCommand extends Command
{
    protected static $defaultName = 'app:seed-maintenance-types';
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setDescription('Seed default maintenance types');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $types = [
            'Vidange & Filtres', 'Plaquettes de frein', 'Révision Générale',
            'Changement Pneus', 'Révision pneumatiques', 'Contrôle technique',
            'Remplacement batterie', 'Freins & Disques', 'Électrique',
            'Carrosserie', 'Autre'
        ];

        foreach ($types as $name) {
            $existing = $this->em->getRepository(MaintenanceType::class)->findOneBy(['name' => $name]);
            if (!$existing) {
                $type = new MaintenanceType();
                $type->setName($name);
                $this->em->persist($type);
                $io->info("Created: $name");
            }
            else {
                $io->note("Exists: $name");
            }
        }

        $this->em->flush();
        $io->success('Seeding completed successfully!');

        return Command::SUCCESS;
    }
}