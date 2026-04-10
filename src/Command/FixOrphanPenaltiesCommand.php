<?php

namespace App\Command;

use App\Entity\Client\Penalty;
use App\Repository\Client\PenaltyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to link penalties that are missing a contractId to the
 * active contract of their associated vehicle.
 *
 * Usage:
 *   php bin/console app:fix-orphan-penalties
 */
class FixOrphanPenaltiesCommand extends Command
{
    private EntityManagerInterface $em;
    private PenaltyRepository $penaltyRepository;

    public function __construct(EntityManagerInterface $em, PenaltyRepository $penaltyRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->penaltyRepository = $penaltyRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:fix-orphan-penalties')
            ->setDescription('Links orphan penalties to the active contract of their associated vehicle.')
            ->setHelp('Use this to fix visibility issues for penalties created without an explicit contract context.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Fix des Pénalités Orphelines');

        $orphanPenalties = $this->penaltyRepository->findBy(['contract' => null]);
        $totalOrphans = count($orphanPenalties);

        if ($totalOrphans === 0) {
            $io->success('Aucune pénalité orpheline trouvée.');
            return Command::SUCCESS;
        }

        $io->note(sprintf('%d pénalité(s) orpheline(s) identifiée(s). Tentative de liaison...', $totalOrphans));
        $fixedCount = 0;

        $io->progressStart($totalOrphans);

        foreach ($orphanPenalties as $penalty) {
            $vehicle = $penalty->getVehicle();
            if ($vehicle) {
                $activeContract = $vehicle->getActiveContract();
                if ($activeContract) {
                    $penalty->setContract($activeContract);
                    if (!$penalty->getClient()) {
                        $penalty->setClient($activeContract->getClient());
                    }
                    $fixedCount++;
                }
            }
            $io->progressAdvance();
        }

        $this->em->flush();
        $io->progressFinish();

        if ($fixedCount > 0) {
            $io->success(sprintf('%d pénalité(s) ont été liées avec succès à un contrat actif.', $fixedCount));
        }
        else {
            $io->warning('Aucune pénalité n\'a pu être liée (aucun contrat actif trouvé pour les véhicules concernés).');
        }

        return Command::SUCCESS;
    }
}