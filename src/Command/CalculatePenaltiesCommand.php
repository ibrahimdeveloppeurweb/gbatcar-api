<?php

namespace App\Command;

use App\Manager\Client\PenaltyManager;
use App\Repository\Client\ContractRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Run this command daily (via cron) to automatically calculate
 * penalties for overdue installments.
 *
 * Usage:
 *   php bin/console app:penalties:calculate
 */
class CalculatePenaltiesCommand extends Command
{
    private PenaltyManager $penaltyManager;
    private ContractRepository $contractRepository;

    public function __construct(PenaltyManager $penaltyManager, ContractRepository $contractRepository)
    {
        parent::__construct();
        $this->penaltyManager = $penaltyManager;
        $this->contractRepository = $contractRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:penalties:calculate')
            ->setDescription('Calculates daily penalties for all active contracts with overdue installments.')
            ->setHelp('This command should run daily after app:mark-overdue-schedules.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Calcul des Pénalités de Retard');

        $activeContracts = $this->contractRepository->findBy(['status' => 'VALIDÉ']);
        $totalContracts = count($activeContracts);
        $totalPenalties = 0;

        $io->progressStart($totalContracts);

        foreach ($activeContracts as $contract) {
            $penalties = $this->penaltyManager->calculatePenaltiesForContract($contract);
            $totalPenalties += count($penalties);
            $io->progressAdvance();
        }

        $io->progressFinish();

        $updatedCount = $this->penaltyManager->updateStatuses();

        $io->success(sprintf('Calcul terminé. %d pénalité(s) générée(s) ou mise(s) à jour, et %d statut(s) actualisé(s) sur %d contrat(s).', $totalPenalties, $updatedCount, $totalContracts));

        return Command::SUCCESS;
    }
}