<?php

namespace App\Manager\Client;

use App\Entity\Client\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\ClientRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class ClientManager
{
    private $em;
    private $clientRepository;
    private $uploadDir;

    public function __construct(
        EntityManagerInterface $em,
        ClientRepository $clientRepository,
        KernelInterface $kernel
        )
    {
        $this->em = $em;
        $this->clientRepository = $clientRepository;
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads/clients/';
    }

    public function create(object $data, Request $request): Client
    {
        $client = new Client();
        $this->hydrate($client, $data);
        $this->handleFiles($client, $request);

        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    public function update(string $uuid, object $data, Request $request): Client
    {
        $client = $this->clientRepository->findOneBy(['uuid' => $uuid]);
        if (!$client) {
            throw new \Exception("Client introuvable.");
        }

        $this->hydrate($client, $data);
        $this->handleFiles($client, $request);
        $this->em->flush();

        return $client;
    }

    public function delete(Client $client): Client
    {
        $client->setDeletedAt(new \DateTime());
        $this->em->flush();

        return $client;
    }

    public function findByFilters(array $filters = []): array
    {
        return $this->clientRepository->findByFilters($filters);
    }

    public function getDashboardData(): array
    {
        return $this->clientRepository->getDashboardMetrics();
    }

    private function handleFiles(Client $client, Request $request): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        // Photo de profil
        $photo = $request->files->get('photoFile');
        if ($photo && $photo->isValid()) {
            $filename = uniqid() . '_photo_' . $photo->getClientOriginalName();
            $photo->move($this->uploadDir, $filename);
            $client->setPhoto('/uploads/clients/' . $filename);
        }

        // Scan CNI/Passeport
        $idScan = $request->files->get('idScanFile');
        if ($idScan && $idScan->isValid()) {
            $filename = uniqid() . '_id_' . $idScan->getClientOriginalName();
            $idScan->move($this->uploadDir, $filename);
            $client->setIdScanUrl('/uploads/clients/' . $filename);
        }

        // Scan Permis
        $licenseScan = $request->files->get('licenseScanFile');
        if ($licenseScan && $licenseScan->isValid()) {
            $filename = uniqid() . '_license_' . $licenseScan->getClientOriginalName();
            $licenseScan->move($this->uploadDir, $filename);
            $client->setLicenseScanUrl('/uploads/clients/' . $filename);
        }
    }

    private function hydrate(Client $client, object $data): void
    {
        // Informations Personnelles
        if (isset($data->firstName))
            $client->setFirstName($data->firstName);
        if (isset($data->lastName))
            $client->setLastName($data->lastName);
        if (isset($data->name))
            $client->setName($data->name);
        if (!isset($data->name) && isset($data->firstName) && isset($data->lastName)) {
            $client->setName($data->firstName . ' ' . $data->lastName);
        }

        if (isset($data->birthDate) && $data->birthDate)
            $client->setBirthDate(new \DateTimeImmutable($data->birthDate));
        if (isset($data->gender))
            $client->setGender($data->gender);
        if (isset($data->maritalStatus))
            $client->setMaritalStatus($data->maritalStatus);
        if (isset($data->childrenCount))
            $client->setChildrenCount((int)$data->childrenCount);
        if (isset($data->phone))
            $client->setPhone($data->phone);
        if (isset($data->email))
            $client->setEmail($data->email);
        if (isset($data->address))
            $client->setAddress($data->address);
        if (isset($data->city))
            $client->setCity($data->city);
        if (isset($data->neighborhood))
            $client->setNeighborhood($data->neighborhood);
        if (isset($data->nationality))
            $client->setNationality($data->nationality);
        if (isset($data->birthPlace))
            $client->setBirthPlace($data->birthPlace);

        // Profil socio-économique
        if (isset($data->educationLevel))
            $client->setEducationLevel($data->educationLevel);
        if (isset($data->profession))
            $client->setProfession($data->profession);
        if (isset($data->incomeBracket))
            $client->setIncomeBracket($data->incomeBracket);
        if (isset($data->housingStatus))
            $client->setHousingStatus($data->housingStatus);
        if (isset($data->primaryBankAccount))
            $client->setPrimaryBankAccount($data->primaryBankAccount);
        if (isset($data->discoveryChannel))
            $client->setDiscoveryChannel($data->discoveryChannel);
        if (isset($data->drivingExperienceYears))
            $client->setDrivingExperienceYears((int)$data->drivingExperienceYears);
        if (isset($data->previousCreditExperience))
            $client->setPreviousCreditExperience($data->previousCreditExperience);
        if (isset($data->repaymentSource))
            $client->setRepaymentSource($data->repaymentSource);

        // Documents Officiels
        if (isset($data->idNumber))
            $client->setIdNumber($data->idNumber);
        if (isset($data->idIssueDate) && $data->idIssueDate)
            $client->setIdIssueDate(new \DateTimeImmutable($data->idIssueDate));
        if (isset($data->licenseNumber))
            $client->setLicenseNumber($data->licenseNumber);

        if (isset($data->status))
            $client->setStatus($data->status);
        if (isset($data->type))
            $client->setType($data->type);
    }
}