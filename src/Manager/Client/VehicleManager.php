<?php

namespace App\Manager\Client;

use App\Entity\Client\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Client\VehicleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class VehicleManager
{
    private $em;
    private $vehicleRepository;
    private $uploadDir;

    public function __construct(
        EntityManagerInterface $em,
        VehicleRepository $vehicleRepository,
        KernelInterface $kernel
        )
    {
        $this->em = $em;
        $this->vehicleRepository = $vehicleRepository;
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads/vehicles/';
    }

    public function create(object $data, Request $request): Vehicle
    {
        $vehicle = new Vehicle();
        $this->hydrate($vehicle, $data);
        $this->handleFiles($vehicle, $request);
        $this->em->persist($vehicle);
        $this->em->flush();
        return $vehicle;
    }

    public function update(string $uuid, object $data, Request $request): Vehicle
    {
        $vehicle = is_numeric($uuid)
            ? $this->vehicleRepository->find($uuid)
            : $this->vehicleRepository->findOneByUuid($uuid);

        if (!$vehicle) {
            throw new \Exception('Véhicule introuvable.');
        }
        $this->hydrate($vehicle, $data);
        $this->handleFiles($vehicle, $request);
        $this->em->persist($vehicle);
        $this->em->flush();
        return $vehicle;
    }

    public function delete(Vehicle $vehicle): void
    {
        $this->em->remove($vehicle);
        $this->em->flush();
    }

    public function getDashboardData(): array
    {
        return $this->vehicleRepository->getDashboardMetrics();
    }

    private function handleFiles(Vehicle $vehicle, Request $request): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $fileMap = [
            'carteGrise' => 'setCarteGriseUrl',
            'assurance' => 'setAssuranceUrl',
            'visiteTechnique' => 'setVisiteTechniqueUrl',
        ];

        foreach ($fileMap as $field => $setter) {
            $file = $request->files->get($field);
            if ($file && $file->isValid()) {
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->move($this->uploadDir, $filename);
                $vehicle->$setter('/uploads/vehicles/' . $filename);
            }
        }
    }

    private function hydrate(Vehicle $vehicle, object $data): void
    {
        // Informations de base
        if (isset($data->marque)) {
            $vehicle->setMarque($data->marque);
            $brand = $this->em->getRepository(\App\Entity\Client\Brand::class)->findOneBy(['name' => $data->marque]);
            if ($brand)
                $vehicle->setBrand($brand);
            else
                $vehicle->setBrand(null);
        }
        if (isset($data->modele)) {
            $vehicle->setModele($data->modele);
            $modelClass = \App\Entity\Client\VehicleModel::class;
            if (isset($brand) && $brand) {
                $model = $this->em->getRepository($modelClass)->findOneBy(['name' => $data->modele, 'brand' => $brand]);
                if ($model)
                    $vehicle->setVehicleModel($model);
                else
                    $vehicle->setVehicleModel(null);
            }
            else {
                $vehicle->setVehicleModel(null);
            }
        }
        if (isset($data->annee))
            $vehicle->setAnnee((int)$data->annee);
        if (isset($data->couleur))
            $vehicle->setCouleur($data->couleur);
        if (isset($data->finition))
            $vehicle->setFinition($data->finition);
        if (isset($data->carburant))
            $vehicle->setCarburant($data->carburant);
        if (isset($data->transmission))
            $vehicle->setTransmission($data->transmission);
        if (isset($data->nombrePlaces))
            $vehicle->setNombrePlaces((int)$data->nombrePlaces);
        if (isset($data->categorie))
            $vehicle->setCategorie($data->categorie);

        // Identification technique
        if (isset($data->immatriculation))
            $vehicle->setImmatriculation($data->immatriculation);
        if (isset($data->numeroChassis))
            $vehicle->setNumeroChassis($data->numeroChassis);
        if (isset($data->kilometrage))
            $vehicle->setKilometrage((int)$data->kilometrage);
        if (isset($data->prochainEntretien))
            $vehicle->setProchainEntretien((int)$data->prochainEntretien);
        if (isset($data->gpsStatus))
            $vehicle->setGpsStatus($data->gpsStatus);
        if (isset($data->lastMaintenance)) {
            $date = new \DateTimeImmutable($data->lastMaintenance);
            $vehicle->setDateDerniereMaintenance($date);
        }
        if (isset($data->notesInternes))
            $vehicle->setNotesInternes($data->notesInternes);

        // Statut
        if (isset($data->statut))
            $vehicle->setStatut($data->statut);

        // Offre commerciale
        if (isset($data->includingInsurance))
            $vehicle->setIncludingInsurance((bool)$data->includingInsurance);
        if (isset($data->includingGPS))
            $vehicle->setIncludingGPS((bool)$data->includingGPS);
        if (isset($data->depositPercentage))
            $vehicle->setDepositPercentage((float)$data->depositPercentage);
        if (isset($data->durationInMonths))
            $vehicle->setDurationInMonths((int)$data->durationInMonths);

        // Rentabilité
        if (isset($data->prixDeVente))
            $vehicle->setPrixDeVente((float)$data->prixDeVente);
        if (isset($data->tcoEstime))
            $vehicle->setTcoEstime((float)$data->tcoEstime);
        if (isset($data->margeBrutePrevisionnelle))
            $vehicle->setMargeBrutePrevisionnelle((float)$data->margeBrutePrevisionnelle);
        if (isset($data->purchasePrice))
            $vehicle->setPurchasePrice((float)$data->purchasePrice);
        if (isset($data->customsFees))
            $vehicle->setCustomsFees((float)$data->customsFees);
        if (isset($data->transitFees))
            $vehicle->setTransitFees((float)$data->transitFees);
        if (isset($data->preparationCost))
            $vehicle->setPreparationCost((float)$data->preparationCost);
        if (isset($data->gpsInstallationCost))
            $vehicle->setGpsInstallationCost((float)$data->gpsInstallationCost);
        if (isset($data->otherCosts))
            $vehicle->setOtherCosts((float)$data->otherCosts);

        // Commercial
        if (isset($data->prixParJour))
            $vehicle->setPrixParJour((float)$data->prixParJour);
        if (isset($data->description))
            $vehicle->setDescription($data->description);
        if (isset($data->pipelineStatus))
            $vehicle->setPipelineStatus($data->pipelineStatus);

        // Galerie Images
        if (isset($data->photo))
            $vehicle->setPhoto($data->photo);
        if (isset($data->photos)) {
            $photos = is_string($data->photos) ? explode(',', $data->photos) : $data->photos;
            $vehicle->setPhotos($photos);
        }
    }
}