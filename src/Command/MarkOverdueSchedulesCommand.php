<?php

namespace App\Command;

use App\Manager\Client\PaymentScheduleManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Run this command daily (via cron or Symfony Scheduler) to automatically
 * mark overdue installments as "En retard".
 *
 * Usage:
 *   php bin/console app:mark-overdue-schedules
 *
 * Suggested crontab (runs at midnight daily):
 *   0 0 * * * php /path/to/gbatcar-api/bin/console app:mark-overdue-schedules
 */
class MarkOverdueSchedulesCommand extends Command
{
    private PaymentScheduleManager $scheduleManager;

    public function __construct(PaymentScheduleManager $scheduleManager)
    {
        parent::__construct();
        $this->scheduleManager = $scheduleManager;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:mark-overdue-schedules')
            ->setDescription('Marks all past-due payment schedule installments as "En retard".')
            ->setHelp('Run this command daily to automatically update the status of overdue installments in the payment schedule.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Mise à jour des Échéances en Retard');

        $count = $this->scheduleManager->markOverdueSchedules();

        if ($count === 0) {
            $io->success('Aucune échéance en retard détectée. Tout est à jour !');
        }
        else {
            $io->success(sprintf('%d échéance(s) marquée(s) "En retard" avec succès.', $count));
        }

        return Command::SUCCESS;
    }
}