<?php

namespace App\Mailing;

use App\Entity\Client\Subscription;
use App\Services\MailService;

class SubscriptionMailing
{
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Notification lors d'une demande de souscription
     * 
     * @param Subscription $subscription
     * @return void
     */
    public function subscriptionRequested(Subscription $subscription)
    {
        $adminEmail = $_ENV['MAILER_SENDER'] ?? 'contact@gbatcar.com';
        $userEmail = $subscription->getEmail();

        // Send to Subscriber
        if ($userEmail) {
            $this->mailService->email(
                $userEmail,
            ['subscription' => $subscription],
                'Votre demande de souscription - GBATCAR',
                3,
                'mail/client/subscription/_subscription_request.html.twig'
            );
        }

        // Send to Admin
        $this->mailService->email(
            $adminEmail,
        ['subscription' => $subscription],
            '[ADMIN] Nouvelle demande de souscription : ' . $subscription->getFullName(),
            2, // High priority for admin
            'mail/client/subscription/_admin_subscription_notification.html.twig'
        );
    }

    /**
     * Notification lors du rejet d'une souscription
     * 
     * @param Subscription $subscription
     * @param string|null $reason
     * @return void
     */
    public function subscriptionRejected(Subscription $subscription, ?string $reason = null)
    {
        $userEmail = $subscription->getEmail();

        if ($userEmail) {
            $this->mailService->email(
                $userEmail,
            [
                'subscription' => $subscription,
                'reason' => $reason
            ],
                'Mise à jour concernant votre demande de souscription - GBATCAR',
                3,
                'mail/client/subscription/_subscription_rejected.html.twig'
            );
        }
    }
}