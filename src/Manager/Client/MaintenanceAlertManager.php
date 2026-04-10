<?php

namespace App\Manager\Client;

use App\Entity\Client\MaintenanceAlert;
use App\Entity\Client\Payment;
use App\Entity\Client\Contract;
use App\Entity\Client\Maintenance;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\MaintenanceAlertRepository;

class MaintenanceAlertManager
{
    private $em;
    private $maintenanceAlertRepository;
    private $vehicleRepository;
    private $contractRepository;
    private $uploadDir;
    private $projectDir;

    public function __construct(
        EntityManagerInterface $em,
        MaintenanceAlertRepository $maintenanceAlertRepository,
        \App\Repository\Client\VehicleRepository $vehicleRepository,
        \App\Repository\Client\ContractRepository $contractRepository,
        \Symfony\Component\HttpKernel\KernelInterface $kernel
        )
    {
        $this->em = $em;
        $this->maintenanceAlertRepository = $maintenanceAlertRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->contractRepository = $contractRepository;
        $this->projectDir = $kernel->getProjectDir();
        $this->uploadDir = $this->projectDir . '/public/uploads/maintenance/alerts/';
    }

    public function create(object $data, \Symfony\Component\HttpFoundation\Request $request): MaintenanceAlert
    {
        $alert = new MaintenanceAlert();
        $alert->setUuid(\Ramsey\Uuid\Uuid::uuid4());
        $alert->setStatus('Ouvert');

        // Generate Human Reference ALT-YYYY-XXX
        $year = date('Y');
        $count = $this->maintenanceAlertRepository->countByYear($year);
        $alert->setReference(sprintf('ALT-%s-%03d', $year, $count + 1));

        $this->mapDataToEntity($data, $alert);
        $this->handleFiles($alert, $request);

        $this->em->persist($alert);
        $this->em->flush();

        return $alert;
    }

    public function update(string $uuid, object $data, \Symfony\Component\HttpFoundation\Request $request): MaintenanceAlert
    {
        $alert = $this->maintenanceAlertRepository->findOneByUuid($uuid);
        if (!$alert) {
            throw new \Exception("Alerte introuvable.");
        }

        $this->mapDataToEntity($data, $alert);
        $this->handleFiles($alert, $request);

        $this->em->flush();

        return $alert;
    }

    public function changeStatus(string $uuid, string $status): MaintenanceAlert
    {
        $alert = $this->maintenanceAlertRepository->findOneByUuid($uuid);
        if (!$alert) {
            throw new \Exception("Alerte introuvable.");
        }

        $alert->setStatus($status);
        $this->em->flush();

        return $alert;
    }

    public function invoice(string $uuid, string $payerType): MaintenanceAlert
    {
        $alert = $this->maintenanceAlertRepository->findOneByUuid($uuid);
        if (!$alert) {
            throw new \Exception("Alerte introuvable.");
        }

        if ($alert->getIsInvoiced()) {
            throw new \Exception("Cet incident a déjà été facturé.");
        }

        if (!$alert->getRepairCost()) {
            throw new \Exception("Le coût des réparations n'est pas renseigné.");
        }

        $payment = new Payment();
        $payment->setAmount($alert->getRepairCost());
        $payment->setDate(new \DateTimeImmutable());

        $contract = $alert->getContract();
        $client = $alert->getClient();

        // Fallback to vehicle's active contract if not set on the alert
        if (!$contract) {
            $contract = $this->em->getRepository(Contract::class)->findOneBy(
            ['vehicle' => $alert->getVehicle()],
            ['id' => 'DESC']
            );
        }

        if (!$contract) {
            throw new \Exception("Impossible de facturer : aucun contrat trouvé pour ce véhicule.");
        }

        if (!$client) {
            $client = $contract->getClient();
        }

        if (!$client) {
            throw new \Exception("Impossible de facturer : aucun client trouvé.");
        }

        $payment->setContract($contract);
        $payment->setClient($client);

        $refId = $alert->getReference() ?: strtoupper(substr($alert->getUuid(), 0, 8));
        $payment->setReference('INV-' . $refId);
        $payment->setMethod('Espèces');

        if ($payerType === 'CLIENT') {
            $payment->setType('RÉPARATION_CLIENT');
            $payment->setStatus('En attente'); // Client needs to pay
            $payment->setObservation("Facturation d'incident : " . $alert->getType());
        }
        else {
            $payment->setType('FRAIS_AGENCE');
            $payment->setStatus('VALIDÉ'); // Agency already "paid" by covering it
            $payment->setObservation("Prise en charge interne incident : " . $alert->getType());

            // Create a Maintenance entry to log in vehicle history
            $maint = new Maintenance();
            $maint->setVehicle($alert->getVehicle());
            $maint->setContract($contract);
            $maint->setClient($client);
            $maint->setType($alert->getType());
            $maint->setDescription("Prise en charge interne suite à sinistre : " . $alert->getDescription());
            $maint->setCost($alert->getRepairCost());
            $maint->setDateIntervention(new \DateTimeImmutable());
            $maint->setStatus('Terminé');
            $maint->setReference('MAI-' . $refId);
            $this->em->persist($maint);
        }

        $this->em->persist($payment);

        // Logical Separation: We track invoicing separately from operational status
        $alert->setIsInvoiced(true);
        $alert->setInvoicedAt(new \DateTimeImmutable());
        $alert->setBillingPayer($payerType);

        $this->em->flush();

        return $alert;
    }

    public function delete(MaintenanceAlert $maintenanceAlert): MaintenanceAlert
    {
        $this->em->remove($maintenanceAlert);
        $this->em->flush();

        return $maintenanceAlert;
    }

    private function mapDataToEntity(object $data, MaintenanceAlert $alert): void
    {
        if (isset($data->type))
            $alert->setType($data->type);
        if (isset($data->severity))
            $alert->setSeverity($data->severity);
        if (isset($data->description))
            $alert->setDescription($data->description);
        if (isset($data->observation))
            $alert->setObservation($data->observation);
        if (isset($data->driverInfo))
            $alert->setDriverInfo($data->driverInfo);
        if (isset($data->repairCost))
            $alert->setRepairCost((float)$data->repairCost);

        if (!empty($data->date)) {
            $alert->setDate(new \DateTimeImmutable($data->date));
        }

        if (isset($data->vehicle)) {
            $vehicle = $this->vehicleRepository->findOneByUuid($data->vehicle);
            if ($vehicle) {
                $alert->setVehicle($vehicle);
                $alert->setClient($vehicle->getClient());
            }
        }

        if (isset($data->contract)) {
            $contract = $this->contractRepository->findOneByUuid($data->contract);
            if ($contract) {
                $alert->setContract($contract);
            }
        }

        if (isset($data->status))
            $alert->setStatus($data->status);
    }

    private function handleFiles(MaintenanceAlert $alert, \Symfony\Component\HttpFoundation\Request $request): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        // 1. Procès-Verbal / Constat (PDF)
        $policeReport = $request->files->get('policeReportFile');
        if ($policeReport && $policeReport->isValid()) {
            $filename = 'alert_' . uniqid() . '_' . $policeReport->getClientOriginalName();
            $policeReport->move($this->uploadDir, $filename);
            $alert->setPoliceReportUrl('/uploads/maintenance/alerts/' . $filename);
        }

        // 2. Photos du sinistre (Multiple)
        $photos = $request->files->get('photosFiles');
        if ($photos && is_array($photos)) {
            $existingPhotos = $alert->getPhotosUrls() ?? [];
            foreach ($photos as $photo) {
                if ($photo && $photo->isValid()) {
                    $filename = 'photo_' . uniqid() . '_' . $photo->getClientOriginalName();
                    $photo->move($this->uploadDir, $filename);
                    $existingPhotos[] = '/uploads/maintenance/alerts/' . $filename;
                }
            }
            $alert->setPhotosUrls($existingPhotos);
        }
    }
}