<?php

namespace App\Manager\Client;

use App\Entity\Client\Vehicle;
use App\Entity\Client\VehicleCompliance;
use App\Entity\Client\VehicleComplianceDocument;
use App\Entity\Client\Penalty;
use App\Repository\Client\VehicleComplianceRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class VehicleComplianceManager
{
    private $em;
    private $repository;
    private $uploadDir;

    public function __construct(
        EntityManagerInterface $em,
        VehicleComplianceRepository $repository,
        KernelInterface $kernel
        )
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads/compliance/';
    }

    public function create(object $data, Request $request): VehicleCompliance
    {
        $vehicleId = $data->vehicleId ?? $data->vehicleUuid ?? null;
        $vehicle = null;

        if ($vehicleId) {
            $vehicle = is_numeric($vehicleId)
                ? $this->em->getRepository(Vehicle::class)->find($vehicleId)
                : $this->em->getRepository(Vehicle::class)->findOneByUuid($vehicleId);
        }

        $compliance = null;
        if ($vehicle) {
            $compliance = $vehicle->getCompliance();
        }

        if (!$compliance) {
            $compliance = new VehicleCompliance();
            if ($vehicle) {
                $compliance->setVehicle($vehicle);
            }
        }

        $this->hydrate($compliance, $data);
        $this->handleFiles($compliance, $request);
        $this->em->persist($compliance);
        $this->em->flush();
        return $compliance;
    }

    public function update(string $uuid, object $data, Request $request): VehicleCompliance
    {
        $compliance = is_numeric($uuid)
            ? $this->repository->find($uuid)
            : $this->repository->findOneByUuid($uuid);

        if (!$compliance) {
            throw new \Exception('Conformité introuvable.');
        }

        $this->hydrate($compliance, $data);
        $this->handleFiles($compliance, $request);
        $this->em->persist($compliance);
        $this->em->flush();
        return $compliance;
    }

    public function delete(VehicleCompliance $compliance): void
    {
        $this->em->remove($compliance);
        $this->em->flush();
    }

    private function recordDocument(Vehicle $vehicle, string $type, ?string $fileUrl, ?\DateTimeImmutable $expiryDate, ?\DateTimeImmutable $deliveryDate = null, ?string $observation = null, string $status = 'Archivé', float $renewalCost = 0): void
    {
        // On permet désormais l'enregistrement sans fichier joint (fileUrl peut être nul)

        // If status is Actif, set all previous same-type docs to Archivé
        if ($status === 'Actif') {
            $prevDocs = $this->em->getRepository(VehicleComplianceDocument::class)->findBy([
                'vehicle' => $vehicle,
                'type' => $type,
                'status' => 'Actif'
            ]);
            foreach ($prevDocs as $prev) {
                $prev->setStatus('Archivé');
            }
        }

        $doc = new VehicleComplianceDocument();
        $doc->setVehicle($vehicle);
        $doc->setType($type);
        $doc->setFileUrl($fileUrl);
        $doc->setEndDate($expiryDate);
        $doc->setDeliveryDate($deliveryDate);
        $doc->setObservation($observation);
        $doc->setStatus($status);
        // Initial values for history records
        $doc->setStartDate($deliveryDate ?? new \DateTimeImmutable('now'));
        $doc->setAffectation('Répercuté au locataire');
        $doc->setRenewalCost($renewalCost);

        $this->em->persist($doc);
    }

    private function getRenewalCostGetterMap(): array
    {
        return [
            'setAssuranceUrl' => 'getAssuranceRenewalCost',
            'setTechnicalInspectionUrl' => 'getTechnicalInspectionRenewalCost',
            'setRoadTaxUrl' => 'getRoadTaxRenewalCost',
            'setTransportLicenseUrl' => 'getTransportLicenseRenewalCost',
            'setFireExtinguisherUrl' => 'getFireExtinguisherRenewalCost',
            'setCarteGriseUrl' => 'getCarteGriseRenewalCost',
            'setLeaseContractUrl' => 'getLeaseContractRenewalCost',
        ];
    }

    private function handleFiles(VehicleCompliance $compliance, Request $request): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $fileMap = [
            'assuranceFile' => 'setAssuranceUrl',
            'technicalInspectionFile' => 'setTechnicalInspectionUrl',
            'roadTaxFile' => 'setRoadTaxUrl',
            'transportLicenseFile' => 'setTransportLicenseUrl',
            'fireExtinguisherFile' => 'setFireExtinguisherUrl',
            'carteGriseFile' => 'setCarteGriseUrl',
            'leaseContractFile' => 'setLeaseContractUrl',
        ];

        $typeMap = [
            'setAssuranceUrl' => 'Assurance',
            'setTechnicalInspectionUrl' => 'Visite Technique',
            'setRoadTaxUrl' => 'Vignette',
            'setTransportLicenseUrl' => 'Licence de transport',
            'setFireExtinguisherUrl' => 'Extincteur',
            'setCarteGriseUrl' => 'Carte Grise',
            'setLeaseContractUrl' => 'Contrat Location-Vente',
        ];

        $expiryGetterMap = [
            'setAssuranceUrl' => 'getAssuranceExpiryDate',
            'setTechnicalInspectionUrl' => 'getTechnicalInspectionExpiryDate',
            'setRoadTaxUrl' => 'getRoadTaxExpiryDate',
            'setTransportLicenseUrl' => 'getTransportLicenseExpiryDate',
            'setFireExtinguisherUrl' => 'getFireExtinguisherExpiryDate',
            'setCarteGriseUrl' => 'getCarteGriseExpiryDate',
            'setLeaseContractUrl' => 'getLeaseContractExpiryDate',
        ];

        $deliveryGetterMap = [
            'setAssuranceUrl' => 'getAssuranceDeliveryDate',
            'setTechnicalInspectionUrl' => 'getTechnicalInspectionDeliveryDate',
            'setRoadTaxUrl' => 'getRoadTaxDeliveryDate',
            'setTransportLicenseUrl' => 'getTransportLicenseDeliveryDate',
            'setFireExtinguisherUrl' => 'getFireExtinguisherDeliveryDate',
            'setCarteGriseUrl' => 'getCarteGriseDeliveryDate',
            'setLeaseContractUrl' => 'getLeaseContractDeliveryDate',
        ];

        $observationGetterMap = [
            'setAssuranceUrl' => 'getAssuranceObservation',
            'setTechnicalInspectionUrl' => 'getTechnicalInspectionObservation',
            'setRoadTaxUrl' => 'getRoadTaxObservation',
            'setTransportLicenseUrl' => 'getTransportLicenseObservation',
            'setFireExtinguisherUrl' => 'getFireExtinguisherObservation',
            'setCarteGriseUrl' => 'getCarteGriseObservation',
            'setLeaseContractUrl' => 'getLeaseContractObservation',
        ];

        foreach ($fileMap as $field => $setter) {
            $file = $request->files->get($field);
            $expiryField = str_replace('File', 'ExpiryDate', $field);
            $hasData = $request->get($expiryField) || $request->request->get($expiryField);

            if (($file && $file->isValid()) || $hasData) {
                $fileUrl = null;
                if ($file && $file->isValid()) {
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $file->move($this->uploadDir, $filename);
                    $fileUrl = '/uploads/compliance/' . $filename;
                    $compliance->$setter($fileUrl);
                }

                if ($compliance->getVehicle()) {
                    $this->recordDocument(
                        $compliance->getVehicle(),
                        $typeMap[$setter],
                        $fileUrl,
                        $compliance->{ $expiryGetterMap[$setter]}(),
                        $compliance->{ $deliveryGetterMap[$setter]}(),
                        $compliance->{ $observationGetterMap[$setter]}(),
                        'Actif',
                        (float)$compliance->{ $this->getRenewalCostGetterMap()[$setter]}()
                    );
                }
            }
        }
    }

    private function hydrate(VehicleCompliance $compliance, object $data): void
    {
        if (isset($data->vehicleId) || isset($data->vehicleUuid)) {
            $vehicleId = $data->vehicleId ?? $data->vehicleUuid;
            $vehicle = is_numeric($vehicleId)
                ? $this->em->getRepository(Vehicle::class)->find($vehicleId)
                : $this->em->getRepository(Vehicle::class)->findOneByUuid($vehicleId);

            if ($vehicle) {
                $compliance->setVehicle($vehicle);
            }
        }

        $fields = [
            'assuranceProvider' => 'setAssuranceProvider',
            'assurancePolicyNumber' => 'setAssurancePolicyNumber',
            'assuranceExpiryDate' => 'setAssuranceExpiryDate',
            'assuranceDeliveryDate' => 'setAssuranceDeliveryDate',
            'assuranceObservation' => 'setAssuranceObservation',
            'technicalInspectionCenter' => 'setTechnicalInspectionCenter',
            'technicalInspectionExpiryDate' => 'setTechnicalInspectionExpiryDate',
            'technicalInspectionDeliveryDate' => 'setTechnicalInspectionDeliveryDate',
            'technicalInspectionObservation' => 'setTechnicalInspectionObservation',
            'roadTaxExpiryDate' => 'setRoadTaxExpiryDate',
            'roadTaxDeliveryDate' => 'setRoadTaxDeliveryDate',
            'roadTaxObservation' => 'setRoadTaxObservation',
            'transportLicenseType' => 'setTransportLicenseType',
            'transportLicenseExpiryDate' => 'setTransportLicenseExpiryDate',
            'transportLicenseDeliveryDate' => 'setTransportLicenseDeliveryDate',
            'transportLicenseObservation' => 'setTransportLicenseObservation',
            'fireExtinguisherExpiryDate' => 'setFireExtinguisherExpiryDate',
            'fireExtinguisherDeliveryDate' => 'setFireExtinguisherDeliveryDate',
            'fireExtinguisherObservation' => 'setFireExtinguisherObservation',
            'carteGriseExpiryDate' => 'setCarteGriseExpiryDate',
            'carteGriseDeliveryDate' => 'setCarteGriseDeliveryDate',
            'carteGriseObservation' => 'setCarteGriseObservation',
            'leaseContractType' => 'setLeaseContractType',
            'leaseContractExpiryDate' => 'setLeaseContractExpiryDate',
            'leaseContractDeliveryDate' => 'setLeaseContractDeliveryDate',
            'leaseContractObservation' => 'setLeaseContractObservation',
            'assuranceRenewalCost' => 'setAssuranceRenewalCost',
            'technicalInspectionRenewalCost' => 'setTechnicalInspectionRenewalCost',
            'roadTaxRenewalCost' => 'setRoadTaxRenewalCost',
            'transportLicenseRenewalCost' => 'setTransportLicenseRenewalCost',
            'fireExtinguisherRenewalCost' => 'setFireExtinguisherRenewalCost',
            'carteGriseRenewalCost' => 'setCarteGriseRenewalCost',
            'leaseContractRenewalCost' => 'setLeaseContractRenewalCost',
            'type' => 'setType',
        ];

        foreach ($fields as $key => $setter) {
            if (isset($data->$key)) {
                $value = $data->$key;
                if (strpos($key, 'Date') !== false && $value) {
                    $value = new \DateTimeImmutable($value);
                }
                $compliance->$setter($value);
            }
        }
    }
    public function getFilteredCollections(VehicleCompliance $compliance, object $filters): array
    {
        $vehicle = $compliance->getVehicle();
        if (!$vehicle)
            return ['history' => [], 'penalties' => []];

        // Filter History
        $historyQb = $this->em->getRepository(VehicleComplianceDocument::class)->createQueryBuilder('d')
            ->where('d.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicle);

        if (!empty($filters->searchTerm) && trim($filters->searchTerm) !== '') {
            $historyQb->andWhere('d.type LIKE :term OR d.observation LIKE :term OR d.status LIKE :term')
                ->setParameter('term', '%' . trim($filters->searchTerm) . '%');
        }

        if (!empty($filters->statusFilter) && trim($filters->statusFilter) !== '') {
            $historyQb->andWhere('d.status = :status')
                ->setParameter('status', trim($filters->statusFilter));
        }

        if (!empty($filters->startDate)) {
            $historyQb->andWhere('d.createdAt >= :startDate')
                ->setParameter('startDate', new \DateTimeImmutable($filters->startDate));
        }

        if (!empty($filters->endDate)) {
            $historyQb->andWhere('d.createdAt <= :endDate')
                ->setParameter('endDate', new \DateTimeImmutable($filters->endDate . ' 23:59:59'));
        }

        $history = $historyQb->orderBy('d.createdAt', 'DESC')->getQuery()->getResult();

        // Filter Penalties
        $penaltyQb = $this->em->getRepository(Penalty::class)->createQueryBuilder('p')
            ->where('p.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicle);

        if (!empty($filters->searchTerm) && trim($filters->searchTerm) !== '') {
            $penaltyQb->andWhere('p.reference LIKE :term OR p.reason LIKE :term OR p.status LIKE :term')
                ->setParameter('term', '%' . trim($filters->searchTerm) . '%');
        }

        if (!empty($filters->statusFilter) && trim($filters->statusFilter) !== '') {
            $penaltyQb->andWhere('p.status = :status')
                ->setParameter('status', trim($filters->statusFilter));
        }

        if (!empty($filters->startDate)) {
            $penaltyQb->andWhere('p.createdAt >= :pstartDate')
                ->setParameter('pstartDate', new \DateTimeImmutable($filters->startDate));
        }

        if (!empty($filters->endDate)) {
            $penaltyQb->andWhere('p.createdAt <= :pendDate')
                ->setParameter('pendDate', new \DateTimeImmutable($filters->endDate . ' 23:59:59'));
        }

        $penalties = $penaltyQb->orderBy('p.createdAt', 'DESC')->getQuery()->getResult();

        return [
            'history' => $history,
            'penalties' => $penalties
        ];
    }
}