<?php

namespace App\Services;

use App\Entity\Admin\User;
use App\Entity\Extra\NotificationSetting;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Repository\Extra\NotificationSettingRepository;
use Psr\Log\LoggerInterface;

class FirebaseNotificationService
{
    private $messaging;
    private $logger;
    private $notificationSettingRepository;

    public function __construct(
        Messaging $messaging,
        LoggerInterface $logger,
        NotificationSettingRepository $notificationSettingRepository
        )
    {
        $this->messaging = $messaging;
        $this->logger = $logger;
        $this->notificationSettingRepository = $notificationSettingRepository;
    }

    /**
     * Vérifie si les notifications push sont activées globalement
     */
    private function isPushEnabled(): bool
    {
        $settings = $this->notificationSettingRepository->findAll();
        if (empty($settings)) {
            return false; // Par défaut, on n'envoie rien si pas de config
        }
        return (bool)$settings[0]->getEnablePushNotifications();
    }

    /**
     * Envoie une notification push à un utilisateur spécifique
     *
     * @param User $user L'utilisateur destinataire
     * @param string $title Titre de la notification
     * @param string $body Corps du message
     * @param array $data Données supplémentaires (ex: ['type' => 'payment'])
     * @return bool Succès de l'envoi
     */
    public function sendNotification(User $user, string $title, string $body, array $data = []): bool
    {
        if (!$this->isPushEnabled()) {
            $this->logger->info('Envoi de notification push ignoré : désactivé dans les paramètres globaux.');
            return false;
        }

        $token = $user->getFcmToken();

        if (!$token) {
            $this->logger->warning(sprintf('Impossible d\'envoyer une notification à l\'utilisateur %s : Token FCM manquant.', $user->getUsername()));
            return false;
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            $this->logger->info(sprintf('Notification push envoyée avec succès à %s', $user->getUsername()));
            return true;

        }
        catch (\Exception $e) {
            $this->logger->error(sprintf('Erreur lors de l\'envoi de la notification push à %s : %s', $user->getUsername(), $e->getMessage()));
            return false;
        }
    }

    /**
     * Envoie une notification à un sujet (topic)
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (!$this->isPushEnabled()) {
            $this->logger->info(sprintf('Envoi de notification push au topic %s ignoré : désactivé globalement.', $topic));
            return false;
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
            return true;
        }
        catch (\Exception $e) {
            $this->logger->error(sprintf('Erreur lors de l\'envoi de la notification au topic %s : %s', $topic, $e->getMessage()));
            return false;
        }
    }

    /**
     * Récupère la configuration des notifications
     */
    public function getSettings(): ?NotificationSetting
    {
        $settings = $this->notificationSettingRepository->findAll();
        return !empty($settings) ? $settings[0] : null;
    }

    /**
     * Remplace les variables dans un template de message
     */
    public function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
}