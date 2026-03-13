<?php

namespace App\Manager\Client;

use App\Entity\Client\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\ClientRepository;

class ClientManager
{
    private $em;
    private $clientRepository;

    public function __construct(
        EntityManagerInterface $em,
        ClientRepository $clientRepository
    ) {
        $this->em = $em;
        $this->clientRepository = $clientRepository;
    }

    public function create(object $data): Client
    {
        // To be implemented
        return new Client();
    }

    public function update(string $uuid, object $data): Client
    {
        // To be implemented
        return new Client();
    }

    public function delete(Client $client): Client
    {
        // To be implemented
        return $client;
    }
}
