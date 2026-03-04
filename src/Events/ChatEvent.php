<?php

namespace App\Events;

use App\Entity\Client\Chat;
use App\Helpers\JsonHelper;
use App\Entity\Client\Ticket;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\HubInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChatEvent
{
    private $hub;
    private $user;
    private $token;
    public function __construct(HubInterface $hub, TokenStorageInterface $tokenStorage)
    {
        $this->hub = $hub;
        $this->token = $tokenStorage;
    }
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Chat) {
            $chat = $entity;
            $ticket = $chat->getTicket();
            $emitter = null;
            if ($ticket->getType() == Ticket::TYPE['CLIENT']) {
                $emitter = $ticket->getCustomer();
            } elseif ($ticket->getType() == Ticket::TYPE['LOCATAIRE']) {
                $emitter = $ticket->getTenant();
            } elseif ($ticket->getType() == Ticket::TYPE['PROPRIETAIRE']) {
                $emitter = $ticket->getOwner();
            }
            if ($emitter->getUser()->getUuid() == $chat->getUser()->getUuid() && $ticket->getUser()) {
                $update = new Update(
                    $chat->getTicket()->getTopicUri() . 'user/' . $ticket->getUser()->getUuid(),
                    JsonHelper::getSerializedEntity($chat, ['groups' => ['chat', 'folder', 'file']])
                );
            } else {
                $update = new Update(
                    $chat->getTicket()->getTopicUri() . 'user/' . $emitter->getUser()->getUuid(),
                    JsonHelper::getSerializedEntity($chat, ['groups' => ['chat', 'folder', 'file']])
                );
            }
            try {
                $this->hub->publish($update);
            } catch (\Throwable $th) {
            }
        }
    }
    public function getUser()
    {
        if ($this->token->getToken()) {
            $this->user = $this->token->getToken()->getUser();
        }
        return $this->user;
    }
}
