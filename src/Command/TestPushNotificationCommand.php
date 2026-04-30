<?php

namespace App\Command;

use App\Repository\Admin\UserRepository;
use App\Services\FirebaseNotificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestPushNotificationCommand extends Command
{
    protected static $defaultName = 'app:test-push';
    private $userRepository;
    private $notificationService;

    public function __construct(UserRepository $userRepository, FirebaseNotificationService $notificationService)
    {
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoie une notification de test à un utilisateur')
            ->addArgument('userId', InputArgument::REQUIRED, 'ID de l\'utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('userId');

        $user = $this->userRepository->find((int)$userId);

        if (!$user) {
            $io->error(sprintf('Utilisateur avec l\'ID %s introuvable.', $userId));
            return Command::FAILURE;
        }

        $io->info(sprintf('Envoi d\'une notification test à %s...', $user->getUsername()));

        $success = $this->notificationService->sendNotification(
            $user,
            'Test GBATCAR 🚗',
            'Ceci est une notification de test envoyée depuis Symfony !',
            ['type' => 'alert']
        );

        if ($success) {
            $io->success('Notification envoyée avec succès !');
        } else {
            $io->error('L\'envoi de la notification a échoué. Vérifiez vos identifiants Firebase et le token FCM de l\'utilisateur.');
        }

        return Command::SUCCESS;
    }
}