<?php

namespace App\Entity\Client;

use App\Repository\Client\VehicleRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=VehicleRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Vehicle
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"vehicle", "client", "contract", "compliance", "maintenance", "penalty"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client", "contract", "compliance", "maintenance", "payment", "alert", "penalty"})
     */
    private $immatriculation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client", "contract", "compliance", "maintenance", "payment", "alert", "penalty"})
     */
    private $marque;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client", "contract", "compliance", "maintenance", "payment", "alert", "penalty"})
     */
    private $modele;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"vehicle", "client", "contract", "compliance", "maintenance", "payment", "demand"})
     */
    private $annee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client", "contract", "compliance", "maintenance", "payment", "demand"})
     */
    private $couleur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $finition;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "contract"})
     */
    private $numeroChassis;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"vehicle"})
     */
    private $nombrePlaces;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $carburant;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $transmission;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $categorie;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"vehicle", "client"})
     */
    private $kilometrage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"vehicle", "client"})
     */
    private $prochainEntretien;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client"})
     */
    private $statut;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client"})
     */
    private $paymentStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $gpsStatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle"})
     */
    private $notesInternes;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "client"})
     */
    private $photo;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"vehicle", "client"})
     */
    private $photos = [];

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle"})
     */
    private $dateDerniereMaintenance;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"vehicle"})
     */
    private $maintenanceAlert;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $reimbursementProgress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $pipelineStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $preReservedBy;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle"})
     */
    private $description;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $prixParJour;

    // --- OFFRE COMMERCIALE ---

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $depositPercentage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"vehicle"})
     */
    private $durationInMonths;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"vehicle"})
     */
    private $includingInsurance = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"vehicle"})
     */
    private $includingGPS = false;

    // --- RENTABILITÉ ---

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $purchasePrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $customsFees;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $transitFees;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $preparationCost;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $gpsInstallationCost;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $otherCosts;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $prixDeVente;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $tcoEstime;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle"})
     */
    private $margeBrutePrevisionnelle;

    public function calculateTco(): self
    {
        $this->tcoEstime = (float)($this->purchasePrice ?? 0) +
            (float)($this->customsFees ?? 0) +
            (float)($this->transitFees ?? 0) +
            (float)($this->preparationCost ?? 0) +
            (float)($this->gpsInstallationCost ?? 0) +
            (float)($this->otherCosts ?? 0);
        return $this;
    }

    public function calculateMargeBrute(): self
    {
        $this->margeBrutePrevisionnelle = (float)($this->prixDeVente ?? 0) - (float)($this->tcoEstime ?? 0);
        return $this;
    }

    // --- DOCUMENTS NUMÉRIQUES ---

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $carteGriseUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $assuranceUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $visiteTechniqueUrl;

    /**
     * @ORM\ManyToOne(targetEntity=Brand::class)
     * @Groups({"vehicle", "contract"})
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity=VehicleModel::class)
     * @Groups({"vehicle", "contract"})
     */
    private $vehicleModel;

    /**
     * @ORM\ManyToMany(targetEntity=ContractVehicleDemand::class, mappedBy="assignedVehicles")
     * @Groups({"vehicle"})
     */
    private $vehicleDemands;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="vehicles")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"vehicle", "maintenance"})
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity=Contract::class, mappedBy="vehicle")
     * @Groups({"vehicle"})
     */
    private $contracts;

    /**
     * @ORM\OneToMany(targetEntity=Maintenance::class, mappedBy="vehicle")
     * @ORM\OrderBy({"id" = "DESC"})
     * @Groups({"vehicle"})
     */
    private $maintenances;

    /**
     * @ORM\OneToMany(targetEntity=MaintenanceAlert::class, mappedBy="vehicle")
     */
    private $alerts;

    /**
     * @ORM\OneToOne(targetEntity=VehicleCompliance::class, mappedBy="vehicle", cascade={"persist", "remove"})
     * @Groups({"vehicle", "client"})
     */
    private $compliance;

    /**
     * @ORM\OneToMany(targetEntity=VehicleComplianceDocument::class, mappedBy="vehicle", cascade={"persist", "remove"})
     * @Groups({"vehicle", "compliance"})
     */
    private $complianceDocuments;

    /**
     * @ORM\OneToMany(targetEntity=Penalty::class, mappedBy="vehicle")
     * @Groups({"vehicle", "compliance"})
     */
    private $penalties;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
        $this->maintenances = new ArrayCollection();
        $this->alerts = new ArrayCollection();
        $this->complianceDocuments = new ArrayCollection();
        $this->penalties = new ArrayCollection();
        $this->vehicleDemands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return trim($this->marque . ' ' . $this->modele . ' ' . $this->immatriculation);
    }

    public function getDetail(): string
    {
        return 'Véhicule ' . $this->statut;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(?string $immatriculation): self
    {
        $this->immatriculation = $immatriculation;
        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): self
    {
        $this->marque = $marque;
        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): self
    {
        $this->modele = $modele;
        return $this;
    }

    /**
     * @Groups({"vehicle", "contract", "demand"})
     */
    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(?int $annee): self
    {
        $this->annee = $annee;
        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): self
    {
        $this->couleur = $couleur;
        return $this;
    }

    /**
     * Reliability Score (0-100%) based on payment punctuality
     * @Groups({"vehicle"})
     */
    public function getReliabilityScore(): float
    {
        $totalPaid = 0;
        $onTime = 0;

        foreach ($this->contracts as $contract) {
            foreach ($contract->getPaymentSchedules() as $schedule) {
                if ($schedule->getPaidAt() !== null) {
                    $totalPaid++;
                    // Check if paid on or before expected date
                    if ($schedule->getPaidAt()->format('Y-m-d') <= $schedule->getExpectedDate()->format('Y-m-d')) {
                        $onTime++;
                    }
                }
            }
        }

        if ($totalPaid === 0) {
            return 100.0; // Default for new clients or no payments yet
        }

        return round(($onTime / $totalPaid) * 100, 1);
    }

    public function getFinition(): ?string
    {
        return $this->finition;
    }

    public function setFinition(?string $finition): self
    {
        $this->finition = $finition;
        return $this;
    }

    /**
     * @Groups({"vehicle", "contract", "demand"})
     */
    public function getNumeroChassis(): ?string
    {
        return $this->numeroChassis;
    }

    public function setNumeroChassis(?string $numeroChassis): self
    {
        $this->numeroChassis = $numeroChassis;
        return $this;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombrePlaces;
    }

    public function setNombrePlaces(?int $nombrePlaces): self
    {
        $this->nombrePlaces = $nombrePlaces;
        return $this;
    }

    public function getCarburant(): ?string
    {
        return $this->carburant;
    }

    public function setCarburant(?string $carburant): self
    {
        $this->carburant = $carburant;
        return $this;
    }

    public function getTransmission(): ?string
    {
        return $this->transmission;
    }

    public function setTransmission(?string $transmission): self
    {
        $this->transmission = $transmission;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getKilometrage(): ?int
    {
        return $this->kilometrage;
    }

    public function setKilometrage(?int $kilometrage): self
    {
        $this->kilometrage = $kilometrage;
        return $this;
    }

    public function getProchainEntretien(): ?int
    {
        return $this->prochainEntretien;
    }

    public function setProchainEntretien(?int $prochainEntretien): self
    {
        $this->prochainEntretien = $prochainEntretien;
        return $this;
    }

    public function getStatut(): ?string
    {
        // Dynamic status check if attached to an active contract (direct or fleet)
        $activeStatusesList = ['ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'ACTIF', 'VALIDATED', 'VALIDé'];
        $soldStatusesList = ['TERMINÉ', 'SOLDÉ'];

        // 1. Check for Active Contracts (Direct)
        if ($this->contracts !== null) {
            foreach ($this->contracts as $contract) {
                $status = mb_strtoupper($contract->getStatus() ?? '', 'UTF-8');
                if (in_array($status, $activeStatusesList) || in_array($contract->getStatus(), ['Validé', 'Actif', 'En cours'])) {
                    return 'En Location-Vente';
                }
            }
        }

        // 2. Check for Active Contracts (Fleet)
        if ($this->vehicleDemands !== null) {
            foreach ($this->vehicleDemands as $demand) {
                try {
                    $contract = $demand->getContract();
                    if ($contract) {
                        $status = mb_strtoupper($contract->getStatus() ?? '', 'UTF-8');
                        if (in_array($status, $activeStatusesList) || in_array($contract->getStatus(), ['Validé', 'Actif', 'En cours'])) {
                            return 'En Location-Vente';
                        }
                    }
                }
                catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    continue;
                }
            }
        }

        // 3. Check for Terminated/Sold Contracts (Direct)
        if ($this->contracts !== null) {
            foreach ($this->contracts as $contract) {
                if (in_array(mb_strtoupper($contract->getStatus() ?? '', 'UTF-8'), $soldStatusesList)) {
                    return 'Vendu';
                }
            }
        }

        // 4. Check for Terminated/Sold Contracts (Fleet)
        if ($this->vehicleDemands !== null) {
            foreach ($this->vehicleDemands as $demand) {
                try {
                    $contract = $demand->getContract();
                    if ($contract && in_array(mb_strtoupper($contract->getStatus() ?? '', 'UTF-8'), $soldStatusesList)) {
                        return 'Vendu';
                    }
                }
                catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    continue;
                }
            }
        }

        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        // Try to get from active contract first for real-time accuracy
        if ($this->contracts !== null) {
            foreach ($this->contracts as $contract) {
                try {
                    if (in_array(strtoupper($contract->getStatus() ?? ''), ['ACTIVE', 'EN COURS', 'VALIDÉ', 'ACTIF', 'VALIDATED'])) {
                        return $contract->getPaymentStatus() ?: $this->paymentStatus;
                    }
                }
                catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    continue;
                }
            }
        }

        // Final check: if sold, status should be Soldé
        if ($this->statut === 'Vendu') {
            return 'Soldé';
        }

        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getGpsStatus(): ?string
    {
        return $this->gpsStatus;
    }

    public function setGpsStatus(?string $gpsStatus): self
    {
        $this->gpsStatus = $gpsStatus;
        return $this;
    }

    public function getNotesInternes(): ?string
    {
        return $this->notesInternes;
    }

    public function setNotesInternes(?string $notesInternes): self
    {
        $this->notesInternes = $notesInternes;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): self
    {
        $this->photos = $photos;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return Collection<int, Contract>
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): self
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts[] = $contract;
            $contract->setVehicle($this);
        }
        return $this;
    }

    public function removeContract(Contract $contract): self
    {
        if ($this->contracts->removeElement($contract)) {
            if ($contract->getVehicle() === $this) {
                $contract->setVehicle(null);
            }
        }
        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;
        if ($brand) {
            $this->marque = $brand->getName(); // Auto-sync legacy string
        }
        return $this;
    }

    public function getVehicleModel(): ?VehicleModel
    {
        return $this->vehicleModel;
    }

    public function setVehicleModel(?VehicleModel $vehicleModel): self
    {
        $this->vehicleModel = $vehicleModel;
        if ($vehicleModel) {
            $this->modele = $vehicleModel->getName(); // Auto-sync legacy string
        }
        return $this;
    }

    /**
     * @return Collection<int, Maintenance>
     */
    public function getMaintenances(): Collection
    {
        return $this->maintenances;
    }

    public function addMaintenance(Maintenance $maintenance): self
    {
        if (!$this->maintenances->contains($maintenance)) {
            $this->maintenances[] = $maintenance;
            $maintenance->setVehicle($this);
        }
        return $this;
    }

    public function removeMaintenance(Maintenance $maintenance): self
    {
        if ($this->maintenances->removeElement($maintenance)) {
            if ($maintenance->getVehicle() === $this) {
                $maintenance->setVehicle(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, MaintenanceAlert>
     */
    public function getAlerts(): Collection
    {
        return $this->alerts;
    }

    public function addAlert(MaintenanceAlert $alert): self
    {
        if (!$this->alerts->contains($alert)) {
            $this->alerts[] = $alert;
            $alert->setVehicle($this);
        }
        return $this;
    }

    public function removeAlert(MaintenanceAlert $alert): self
    {
        if ($this->alerts->removeElement($alert)) {
            if ($alert->getVehicle() === $this) {
                $alert->setVehicle(null);
            }
        }
        return $this;
    }

    public function getDateDerniereMaintenance(): ?\DateTimeImmutable
    {
        return $this->dateDerniereMaintenance;
    }

    public function setDateDerniereMaintenance(?\DateTimeImmutable $dateDerniereMaintenance): self
    {
        $this->dateDerniereMaintenance = $dateDerniereMaintenance;
        return $this;
    }

    public function getMaintenanceAlert(): ?bool
    {
        return $this->maintenanceAlert;
    }

    public function setMaintenanceAlert(?bool $maintenanceAlert): self
    {
        $this->maintenanceAlert = $maintenanceAlert;
        return $this;
    }

    public function getCompliance(): ?VehicleCompliance
    {
        return $this->compliance;
    }

    public function setCompliance(?VehicleCompliance $compliance): self
    {
        if ($compliance === null && $this->compliance !== null) {
            $this->compliance->setVehicle(null);
        }
        if ($compliance !== null && $compliance->getVehicle() !== $this) {
            $compliance->setVehicle($this);
        }
        $this->compliance = $compliance;
        return $this;
    }

    public function getReimbursementProgress(): ?float
    {
        return $this->reimbursementProgress;
    }

    public function setReimbursementProgress(?float $reimbursementProgress): self
    {
        $this->reimbursementProgress = $reimbursementProgress;
        return $this;
    }

    public function getPipelineStatus(): ?string
    {
        return $this->pipelineStatus;
    }

    public function setPipelineStatus(?string $pipelineStatus): self
    {
        $this->pipelineStatus = $pipelineStatus;
        return $this;
    }

    public function getPreReservedBy(): ?string
    {
        return $this->preReservedBy;
    }

    public function setPreReservedBy(?string $preReservedBy): self
    {
        $this->preReservedBy = $preReservedBy;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrixParJour(): ?float
    {
        return $this->prixParJour;
    }

    public function setPrixParJour(?float $prixParJour): self
    {
        $this->prixParJour = $prixParJour;
        return $this;
    }

    public function getPrixDeVente(): ?float
    {
        return $this->prixDeVente;
    }

    public function setPrixDeVente(?float $prixDeVente): self
    {
        $this->prixDeVente = $prixDeVente;
        return $this;
    }

    public function getTcoEstime(): ?float
    {
        return $this->tcoEstime;
    }

    public function setTcoEstime(?float $tcoEstime): self
    {
        $this->tcoEstime = $tcoEstime;
        return $this;
    }

    public function getMargeBrutePrevisionnelle(): ?float
    {
        return $this->margeBrutePrevisionnelle;
    }

    public function setMargeBrutePrevisionnelle(?float $margeBrutePrevisionnelle): self
    {
        $this->margeBrutePrevisionnelle = $margeBrutePrevisionnelle;
        return $this;
    }

    public function getCarteGriseUrl(): ?string
    {
        return $this->carteGriseUrl;
    }

    public function setCarteGriseUrl(?string $carteGriseUrl): self
    {
        $this->carteGriseUrl = $carteGriseUrl;
        return $this;
    }

    public function getAssuranceUrl(): ?string
    {
        return $this->assuranceUrl;
    }

    public function setAssuranceUrl(?string $assuranceUrl): self
    {
        $this->assuranceUrl = $assuranceUrl;
        return $this;
    }

    public function getVisiteTechniqueUrl(): ?string
    {
        return $this->visiteTechniqueUrl;
    }

    public function setVisiteTechniqueUrl(?string $visiteTechniqueUrl): self
    {
        $this->visiteTechniqueUrl = $visiteTechniqueUrl;
        return $this;
    }

    public function isIncludingInsurance(): ?bool
    {
        return $this->includingInsurance;
    }

    public function setIncludingInsurance(?bool $includingInsurance): self
    {
        $this->includingInsurance = $includingInsurance;
        return $this;
    }

    public function isIncludingGPS(): ?bool
    {
        return $this->includingGPS;
    }

    public function setIncludingGPS(?bool $includingGPS): self
    {
        $this->includingGPS = $includingGPS;
        return $this;
    }

    public function getDepositPercentage(): ?float
    {
        return $this->depositPercentage;
    }

    public function setDepositPercentage(?float $depositPercentage): self
    {
        $this->depositPercentage = $depositPercentage;
        return $this;
    }

    public function getDurationInMonths(): ?int
    {
        return $this->durationInMonths;
    }

    public function setDurationInMonths(?int $durationInMonths): self
    {
        $this->durationInMonths = $durationInMonths;
        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(?float $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice;
        return $this;
    }

    public function getCustomsFees(): ?float
    {
        return $this->customsFees;
    }

    public function setCustomsFees(?float $customsFees): self
    {
        $this->customsFees = $customsFees;
        return $this;
    }

    public function getTransitFees(): ?float
    {
        return $this->transitFees;
    }

    public function setTransitFees(?float $transitFees): self
    {
        $this->transitFees = $transitFees;
        return $this;
    }

    public function getPreparationCost(): ?float
    {
        return $this->preparationCost;
    }

    public function setPreparationCost(?float $preparationCost): self
    {
        $this->preparationCost = $preparationCost;
        return $this;
    }

    public function getGpsInstallationCost(): ?float
    {
        return $this->gpsInstallationCost;
    }

    public function setGpsInstallationCost(?float $gpsInstallationCost): self
    {
        $this->gpsInstallationCost = $gpsInstallationCost;
        return $this;
    }

    public function getOtherCosts(): ?float
    {
        return $this->otherCosts;
    }

    public function setOtherCosts(?float $otherCosts): self
    {
        $this->otherCosts = $otherCosts;
        return $this;
    }

    /**
     * @return Collection<int, VehicleComplianceDocument>
     */
    public function getComplianceDocuments(): Collection
    {
        return $this->complianceDocuments;
    }

    public function addComplianceDocument(VehicleComplianceDocument $document): self
    {
        if (!$this->complianceDocuments->contains($document)) {
            $this->complianceDocuments[] = $document;
            $document->setVehicle($this);
        }
        return $this;
    }

    public function removeComplianceDocument(VehicleComplianceDocument $document): self
    {
        if ($this->complianceDocuments->removeElement($document)) {
            if ($document->getVehicle() === $this) {
                $document->setVehicle(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Penalty>
     */
    public function getPenalties(): Collection
    {
        return $this->penalties;
    }

    public function addPenalty(Penalty $penalty): self
    {
        if (!$this->penalties->contains($penalty)) {
            $this->penalties[] = $penalty;
            $penalty->setVehicle($this);
        }
        return $this;
    }

    public function removePenalty(Penalty $penalty): self
    {
        if ($this->penalties->removeElement($penalty)) {
            if ($penalty->getVehicle() === $this) {
                $penalty->setVehicle(null);
            }
        }
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \App\Entity\Client\ContractVehicleDemand>
     */
    public function getVehicleDemands(): \Doctrine\Common\Collections\Collection
    {
        return $this->vehicleDemands;
    }

    public function addVehicleDemand(\App\Entity\Client\ContractVehicleDemand $vehicleDemand): self
    {
        if (!$this->vehicleDemands->contains($vehicleDemand)) {
            $this->vehicleDemands[] = $vehicleDemand;
        }

        return $this;
    }

    public function removeVehicleDemand(\App\Entity\Client\ContractVehicleDemand $vehicleDemand): self
    {
        $this->vehicleDemands->removeElement($vehicleDemand);

        return $this;
    }

    /**
     * @return Contract|null
     * @Groups({"vehicle"})
     */
    public function getActiveContract(): ?Contract
    {
        $activeStatusesList = ['ACTIVE', 'EN COURS', 'EN_COURS', 'VALIDÉ', 'ACTIF', 'VALIDATED', 'VALIDé'];

        if ($this->contracts !== null) {
            foreach ($this->contracts as $contract) {
                try {
                    if (in_array(mb_strtoupper($contract->getStatus() ?? '', 'UTF-8'), $activeStatusesList) || in_array($contract->getStatus(), ['Validé', 'Actif', 'En cours'])) {
                        return $contract;
                    }
                }
                catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    continue;
                }
            }
        }

        if ($this->vehicleDemands !== null) {
            foreach ($this->vehicleDemands as $demand) {
                try {
                    $contract = $demand->getContract();
                    if ($contract && (in_array(mb_strtoupper($contract->getStatus() ?? '', 'UTF-8'), $activeStatusesList) || in_array($contract->getStatus(), ['Validé', 'Actif', 'En cours']))) {
                        return $contract;
                    }
                }
                catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * @Groups({"penalty", "contract", "vehicle"})
     */
    public function getLibelle(): string
    {
        if ($this->vehicleModel) {
            return $this->vehicleModel->getLibelle() . ' (' . $this->immatriculation . ')';
        }
        return trim(($this->marque ?? '') . ' ' . ($this->modele ?? '')) . ' (' . $this->immatriculation . ')';
    }

    /**
     * @Groups({"vehicle"})
     */
    public function getRecoveryReason(): ?string
    {
        // 1. Defaut de paiement
        $activeContract = $this->getActiveContract();
        if ($activeContract) {
            $lateCount = 0;
            foreach ($activeContract->getPaymentSchedules() as $ps) {
                if ($ps->getDeletedAt() === null && in_array($ps->getStatus(), ['En retard', 'Partiel']) && $ps->getExpectedDate() < new \DateTime()) {
                    $lateCount++;
                }
            }
            if ($lateCount >= 6) {
                return "Impayé critique ($lateCount échéances)";
            }
        }

        // 2. Entretien dépassé
        if ($this->prochainEntretien > 0 && $this->kilometrage >= $this->prochainEntretien) {
            return "Maintenance : Vidange dépassée";
        }

        // 3. Compliance (Documents expirés)
        if ($this->complianceDocuments) {
            foreach ($this->complianceDocuments as $doc) {
                if ($doc->getDeletedAt() === null && $doc->getEndDate() && $doc->getEndDate()->format('Y-m-d') < (new \DateTime())->format('Y-m-d')) {
                    return "Conformité : " . $doc->getType() . " expiré";
                }
            }
        }

        // 4. Maintenance immobilisée
        if ($this->maintenances) {
            foreach ($this->maintenances as $m) {
                if ($m->getDeletedAt() === null && in_array(strtoupper((string)$m->getStatus()), ['EN COURS', 'EN_COURS', 'IN PROGRESS', 'EN_PROGRESS']) && $m->getStartDate()) {
                    $diff = $m->getStartDate()->diff(new \DateTime());
                    if ($diff->days > 3) {
                        return "Maintenance prolongée (" . $diff->days . "j)";
                    }
                }
            }
        }

        return null;
    }
}