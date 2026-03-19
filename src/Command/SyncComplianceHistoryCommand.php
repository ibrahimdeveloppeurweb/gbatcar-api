<?php

namespace App\Command;

use App\Entity\Client\Vehicle;
use App\Entity\Client\VehicleCompliance;
use App\Entity\Client\VehicleComplianceDocument;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncComplianceHistoryCommand extends Command
{
    protected static $defaultName = 'app:sync-compliance-history';
    protected static $defaultDescription = 'Sync existing vehicle compliance documents to the history table';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Syncing Compliance History');

        $compliances = $this->em->getRepository(VehicleCompliance::class)->findAll();
        $count = 0;

        foreach ($compliances as $compliance) {
            $vehicle = $compliance->getVehicle();
            if (!$vehicle)
                continue;

            $docMap = [
                'Assurance' => [
                    'url' => $compliance->getAssuranceUrl(),
                    'delivery' => $compliance->getAssuranceDeliveryDate(),
                    'expiry' => $compliance->getAssuranceExpiryDate(),
                    'obs' => $compliance->getAssuranceObservation()
                ],
                'Visite Technique' => [
                    'url' => $compliance->getTechnicalInspectionUrl(),
                    'delivery' => $compliance->getTechnicalInspectionDeliveryDate(),
                    'expiry' => $compliance->getTechnicalInspectionExpiryDate(),
                    'obs' => $compliance->getTechnicalInspectionObservation()
                ],
                'Vignette' => [
                    'url' => $compliance->getRoadTaxUrl(),
                    'delivery' => $compliance->getRoadTaxDeliveryDate(),
                    'expiry' => $compliance->getRoadTaxExpiryDate(),
                    'obs' => $compliance->getRoadTaxObservation()
                ],
                'Licence de transport' => [
                    'url' => $compliance->getTransportLicenseUrl(),
                    'delivery' => $compliance->getTransportLicenseDeliveryDate(),
                    'expiry' => $compliance->getTransportLicenseExpiryDate(),
                    'obs' => $compliance->getTransportLicenseObservation()
                ],
                'Extincteur' => [
                    'url' => $compliance->getFireExtinguisherUrl(),
                    'delivery' => $compliance->getFireExtinguisherDeliveryDate(),
                    'expiry' => $compliance->getFireExtinguisherExpiryDate(),
                    'obs' => $compliance->getFireExtinguisherObservation()
                ],
                'Carte Grise' => [
                    'url' => $compliance->getCarteGriseUrl(),
                    'delivery' => $compliance->getCarteGriseDeliveryDate(),
                    'expiry' => $compliance->getCarteGriseExpiryDate(),
                    'obs' => $compliance->getCarteGriseObservation()
                ],
                'Contrat Location-Vente' => [
                    'url' => $compliance->getLeaseContractUrl(),
                    'delivery' => $compliance->getLeaseContractDeliveryDate(),
                    'expiry' => $compliance->getLeaseContractExpiryDate(),
                    'obs' => $compliance->getLeaseContractObservation()
                ],
            ];

            foreach ($docMap as $type => $data) {
                if (!$data['url'])
                    continue;

                // Check if already exists in history
                $exists = $this->em->getRepository(VehicleComplianceDocument::class)->findOneBy([
                    'vehicle' => $vehicle,
                    'type' => $type,
                    'fileUrl' => $data['url']
                ]);

                if (!$exists) {
                    $doc = new VehicleComplianceDocument();
                    $doc->setVehicle($vehicle);
                    $doc->setType($type);
                    $doc->setFileUrl($data['url']);
                    $doc->setEndDate($data['expiry']);
                    $doc->setDeliveryDate($data['delivery']);
                    $doc->setObservation($data['obs']);
                    $doc->setStatus('Actif');
                    $doc->setStartDate($data['delivery'] ?? new \DateTimeImmutable('now'));
                    $doc->setAffectation('Répercuté au locataire');
                    $doc->setRenewalCost(0);

                    $this->em->persist($doc);
                    $count++;
                }
            }
        }

        $this->em->flush();
        $io->success(sprintf('History sync completed. %d new records created.', $count));

        return Command::SUCCESS;
    }
}