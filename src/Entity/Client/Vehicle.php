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
     * @Groups({"vehicle", "client", "contract"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client", "contract"})
     */
    private $immatriculation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client", "contract"})
     */
    private $marque;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "client", "contract"})
     */
    private $modele;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"vehicle"})
     */
    private $annee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $couleur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
     */
    private $finition;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
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
     * @Groups({"vehicle"})
     */
    private $kilometrage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"vehicle"})
     */
    private $prochainEntretien;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle"})
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
     * @Groups({"vehicle"})
     */
    private $photo;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"vehicle"})
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

    // --- RELATIONS ---

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="vehicles")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"vehicle"})
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity=Contract::class, mappedBy="vehicle")
     */
    private $contracts;

    /**
     * @ORM\OneToMany(targetEntity=Maintenance::class, mappedBy="vehicle")
     */
    private $maintenances;

    /**
     * @ORM\OneToMany(targetEntity=MaintenanceAlert::class, mappedBy="vehicle")
     */
    private $alerts;

    // Construct & Getters & Setters omitted for brevity during scaffolding
    public function __construct()
    {
        $this->contracts = new ArrayCollection();
        $this->maintenances = new ArrayCollection();
        $this->alerts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // Standard getter setters for the rest...

    /**
     * @Groups({"vehicle"})
     */
    function getSearchableTitle(): string
    {
        return trim($this->marque . ' ' . $this->modele . ' ' . $this->immatriculation);
    }

    /**
     * @Groups({"vehicle"})
     */
    function getSearchableDetail(): string
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

    public function getFinition(): ?string
    {
        return $this->finition;
    }

    public function setFinition(?string $finition): self
    {
        $this->finition = $finition;

        return $this;
    }

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
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
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
            // set the owning side to null (unless already changed)
            if ($contract->getVehicle() === $this) {
                $contract->setVehicle(null);
            }
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
            // set the owning side to null (unless already changed)
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
            // set the owning side to null (unless already changed)
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

    public function getDepositPercentage(): ?float { return $this->depositPercentage; }
    public function setDepositPercentage(?float $depositPercentage): self { $this->depositPercentage = $depositPercentage; return $this; }

    public function getDurationInMonths(): ?int { return $this->durationInMonths; }
    public function setDurationInMonths(?int $durationInMonths): self { $this->durationInMonths = $durationInMonths; return $this; }

    public function getPurchasePrice(): ?float { return $this->purchasePrice; }
    public function setPurchasePrice(?float $purchasePrice): self { $this->purchasePrice = $purchasePrice; return $this; }

    public function getCustomsFees(): ?float { return $this->customsFees; }
    public function setCustomsFees(?float $customsFees): self { $this->customsFees = $customsFees; return $this; }

    public function getTransitFees(): ?float { return $this->transitFees; }
    public function setTransitFees(?float $transitFees): self { $this->transitFees = $transitFees; return $this; }

    public function getPreparationCost(): ?float { return $this->preparationCost; }
    public function setPreparationCost(?float $preparationCost): self { $this->preparationCost = $preparationCost; return $this; }

    public function getGpsInstallationCost(): ?float { return $this->gpsInstallationCost; }
    public function setGpsInstallationCost(?float $gpsInstallationCost): self { $this->gpsInstallationCost = $gpsInstallationCost; return $this; }

    public function getOtherCosts(): ?float { return $this->otherCosts; }
    public function setOtherCosts(?float $otherCosts): self { $this->otherCosts = $otherCosts; return $this; }
}