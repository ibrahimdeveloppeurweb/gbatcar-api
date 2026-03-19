<?php

namespace App\Entity\Client;

use App\Repository\Client\VehicleComplianceRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=VehicleComplianceRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class VehicleCompliance
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"vehicle", "compliance"})
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Vehicle::class, inversedBy="compliance", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"compliance"})
     */
    private $vehicle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $type;

    // --- ASSURANCE ---

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $assuranceProvider;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $assurancePolicyNumber;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $assuranceExpiryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $assuranceDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $assuranceUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $assuranceObservation;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $assuranceRenewalCost;

    // --- VISITE TECHNIQUE ---

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $technicalInspectionCenter;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $technicalInspectionExpiryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $technicalInspectionDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $technicalInspectionUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $technicalInspectionObservation;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $technicalInspectionRenewalCost;

    // --- VIGNETTE / TAXE ---

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $roadTaxExpiryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $roadTaxDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $roadTaxUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $roadTaxObservation;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $roadTaxRenewalCost;

    // --- LICENCE DE TRANSPORT ---

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $transportLicenseType;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $transportLicenseExpiryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $transportLicenseDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $transportLicenseUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $transportLicenseObservation;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $transportLicenseRenewalCost;

    // --- EXTINCTEUR ---

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $fireExtinguisherExpiryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $fireExtinguisherDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $fireExtinguisherUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $fireExtinguisherObservation;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $fireExtinguisherRenewalCost;

    // --- CARTE GRISE ---

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $carteGriseExpiryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $carteGriseDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $carteGriseUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $carteGriseObservation;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $carteGriseRenewalCost;

    // --- CONTRAT LOCATION-VENTE ---

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $leaseContractType;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $leaseContractExpiryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $leaseContractDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $leaseContractUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $leaseContractObservation;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"vehicle", "compliance"})
     */
    private $leaseContractRenewalCost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSearchableTitle(): string
    {
        return 'Conformité - ' . ($this->vehicle ? $this->vehicle->getSearchableTitle() : 'N/A');
    }

    public function getSearchableDetail(): string
    {
        return 'Documents légaux et techniques';
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    // --- Assurance Getters & Setters ---

    public function getAssuranceProvider(): ?string
    {
        return $this->assuranceProvider;
    }

    public function setAssuranceProvider(?string $assuranceProvider): self
    {
        $this->assuranceProvider = $assuranceProvider;
        return $this;
    }

    public function getAssurancePolicyNumber(): ?string
    {
        return $this->assurancePolicyNumber;
    }

    public function setAssurancePolicyNumber(?string $assurancePolicyNumber): self
    {
        $this->assurancePolicyNumber = $assurancePolicyNumber;
        return $this;
    }

    public function getAssuranceExpiryDate(): ?\DateTimeImmutable
    {
        return $this->assuranceExpiryDate;
    }

    public function setAssuranceExpiryDate(?\DateTimeImmutable $assuranceExpiryDate): self
    {
        $this->assuranceExpiryDate = $assuranceExpiryDate;
        return $this;
    }

    public function getAssuranceDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->assuranceDeliveryDate;
    }

    public function setAssuranceDeliveryDate(?\DateTimeImmutable $assuranceDeliveryDate): self
    {
        $this->assuranceDeliveryDate = $assuranceDeliveryDate;
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

    public function getAssuranceObservation(): ?string
    {
        return $this->assuranceObservation;
    }

    public function setAssuranceObservation(?string $assuranceObservation): self
    {
        $this->assuranceObservation = $assuranceObservation;
        return $this;
    }

    // --- Technical Inspection Getters & Setters ---

    public function getTechnicalInspectionCenter(): ?string
    {
        return $this->technicalInspectionCenter;
    }

    public function setTechnicalInspectionCenter(?string $technicalInspectionCenter): self
    {
        $this->technicalInspectionCenter = $technicalInspectionCenter;
        return $this;
    }

    public function getTechnicalInspectionExpiryDate(): ?\DateTimeImmutable
    {
        return $this->technicalInspectionExpiryDate;
    }

    public function setTechnicalInspectionExpiryDate(?\DateTimeImmutable $technicalInspectionExpiryDate): self
    {
        $this->technicalInspectionExpiryDate = $technicalInspectionExpiryDate;
        return $this;
    }

    public function getTechnicalInspectionDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->technicalInspectionDeliveryDate;
    }

    public function setTechnicalInspectionDeliveryDate(?\DateTimeImmutable $technicalInspectionDeliveryDate): self
    {
        $this->technicalInspectionDeliveryDate = $technicalInspectionDeliveryDate;
        return $this;
    }

    public function getTechnicalInspectionUrl(): ?string
    {
        return $this->technicalInspectionUrl;
    }

    public function setTechnicalInspectionUrl(?string $technicalInspectionUrl): self
    {
        $this->technicalInspectionUrl = $technicalInspectionUrl;
        return $this;
    }

    public function getTechnicalInspectionObservation(): ?string
    {
        return $this->technicalInspectionObservation;
    }

    public function setTechnicalInspectionObservation(?string $technicalInspectionObservation): self
    {
        $this->technicalInspectionObservation = $technicalInspectionObservation;
        return $this;
    }

    // --- Road Tax Getters & Setters ---

    public function getRoadTaxExpiryDate(): ?\DateTimeImmutable
    {
        return $this->roadTaxExpiryDate;
    }

    public function setRoadTaxExpiryDate(?\DateTimeImmutable $roadTaxExpiryDate): self
    {
        $this->roadTaxExpiryDate = $roadTaxExpiryDate;
        return $this;
    }

    public function getRoadTaxDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->roadTaxDeliveryDate;
    }

    public function setRoadTaxDeliveryDate(?\DateTimeImmutable $roadTaxDeliveryDate): self
    {
        $this->roadTaxDeliveryDate = $roadTaxDeliveryDate;
        return $this;
    }

    public function getRoadTaxUrl(): ?string
    {
        return $this->roadTaxUrl;
    }

    public function setRoadTaxUrl(?string $roadTaxUrl): self
    {
        $this->roadTaxUrl = $roadTaxUrl;
        return $this;
    }

    public function getRoadTaxObservation(): ?string
    {
        return $this->roadTaxObservation;
    }

    public function setRoadTaxObservation(?string $roadTaxObservation): self
    {
        $this->roadTaxObservation = $roadTaxObservation;
        return $this;
    }

    // --- Transport License Getters & Setters ---

    public function getTransportLicenseType(): ?string
    {
        return $this->transportLicenseType;
    }

    public function setTransportLicenseType(?string $transportLicenseType): self
    {
        $this->transportLicenseType = $transportLicenseType;
        return $this;
    }

    public function getTransportLicenseExpiryDate(): ?\DateTimeImmutable
    {
        return $this->transportLicenseExpiryDate;
    }

    public function setTransportLicenseExpiryDate(?\DateTimeImmutable $transportLicenseExpiryDate): self
    {
        $this->transportLicenseExpiryDate = $transportLicenseExpiryDate;
        return $this;
    }

    public function getTransportLicenseDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->transportLicenseDeliveryDate;
    }

    public function setTransportLicenseDeliveryDate(?\DateTimeImmutable $transportLicenseDeliveryDate): self
    {
        $this->transportLicenseDeliveryDate = $transportLicenseDeliveryDate;
        return $this;
    }

    public function getTransportLicenseUrl(): ?string
    {
        return $this->transportLicenseUrl;
    }

    public function setTransportLicenseUrl(?string $transportLicenseUrl): self
    {
        $this->transportLicenseUrl = $transportLicenseUrl;
        return $this;
    }

    public function getTransportLicenseObservation(): ?string
    {
        return $this->transportLicenseObservation;
    }

    public function setTransportLicenseObservation(?string $transportLicenseObservation): self
    {
        $this->transportLicenseObservation = $transportLicenseObservation;
        return $this;
    }

    // --- Fire Extinguisher Getters & Setters ---

    public function getFireExtinguisherExpiryDate(): ?\DateTimeImmutable
    {
        return $this->fireExtinguisherExpiryDate;
    }

    public function setFireExtinguisherExpiryDate(?\DateTimeImmutable $fireExtinguisherExpiryDate): self
    {
        $this->fireExtinguisherExpiryDate = $fireExtinguisherExpiryDate;
        return $this;
    }

    public function getFireExtinguisherDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->fireExtinguisherDeliveryDate;
    }

    public function setFireExtinguisherDeliveryDate(?\DateTimeImmutable $fireExtinguisherDeliveryDate): self
    {
        $this->fireExtinguisherDeliveryDate = $fireExtinguisherDeliveryDate;
        return $this;
    }

    public function getFireExtinguisherUrl(): ?string
    {
        return $this->fireExtinguisherUrl;
    }

    public function setFireExtinguisherUrl(?string $fireExtinguisherUrl): self
    {
        $this->fireExtinguisherUrl = $fireExtinguisherUrl;
        return $this;
    }

    public function getFireExtinguisherObservation(): ?string
    {
        return $this->fireExtinguisherObservation;
    }

    public function setFireExtinguisherObservation(?string $fireExtinguisherObservation): self
    {
        $this->fireExtinguisherObservation = $fireExtinguisherObservation;
        return $this;
    }

    // --- Carte Grise Getters & Setters ---

    public function getCarteGriseExpiryDate(): ?\DateTimeImmutable
    {
        return $this->carteGriseExpiryDate;
    }

    public function setCarteGriseExpiryDate(?\DateTimeImmutable $carteGriseExpiryDate): self
    {
        $this->carteGriseExpiryDate = $carteGriseExpiryDate;
        return $this;
    }

    public function getCarteGriseDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->carteGriseDeliveryDate;
    }

    public function setCarteGriseDeliveryDate(?\DateTimeImmutable $carteGriseDeliveryDate): self
    {
        $this->carteGriseDeliveryDate = $carteGriseDeliveryDate;
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

    public function getCarteGriseObservation(): ?string
    {
        return $this->carteGriseObservation;
    }

    public function setCarteGriseObservation(?string $carteGriseObservation): self
    {
        $this->carteGriseObservation = $carteGriseObservation;
        return $this;
    }

    // --- Lease Contract Getters & Setters ---

    public function getLeaseContractType(): ?string
    {
        return $this->leaseContractType;
    }

    public function setLeaseContractType(?string $leaseContractType): self
    {
        $this->leaseContractType = $leaseContractType;
        return $this;
    }

    public function getLeaseContractExpiryDate(): ?\DateTimeImmutable
    {
        return $this->leaseContractExpiryDate;
    }

    public function setLeaseContractExpiryDate(?\DateTimeImmutable $leaseContractExpiryDate): self
    {
        $this->leaseContractExpiryDate = $leaseContractExpiryDate;
        return $this;
    }

    public function getLeaseContractDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->leaseContractDeliveryDate;
    }

    public function setLeaseContractDeliveryDate(?\DateTimeImmutable $leaseContractDeliveryDate): self
    {
        $this->leaseContractDeliveryDate = $leaseContractDeliveryDate;
        return $this;
    }

    public function getLeaseContractUrl(): ?string
    {
        return $this->leaseContractUrl;
    }

    public function setLeaseContractUrl(?string $leaseContractUrl): self
    {
        $this->leaseContractUrl = $leaseContractUrl;
        return $this;
    }

    public function getLeaseContractObservation(): ?string
    {
        return $this->leaseContractObservation;
    }

    public function setLeaseContractObservation(?string $leaseContractObservation): self
    {
        $this->leaseContractObservation = $leaseContractObservation;
        return $this;
    }

    public function getAssuranceRenewalCost(): ?float
    {
        return $this->assuranceRenewalCost;
    }

    public function setAssuranceRenewalCost(?float $assuranceRenewalCost): self
    {
        $this->assuranceRenewalCost = $assuranceRenewalCost;
        return $this;
    }

    public function getTechnicalInspectionRenewalCost(): ?float
    {
        return $this->technicalInspectionRenewalCost;
    }

    public function setTechnicalInspectionRenewalCost(?float $technicalInspectionRenewalCost): self
    {
        $this->technicalInspectionRenewalCost = $technicalInspectionRenewalCost;
        return $this;
    }

    public function getRoadTaxRenewalCost(): ?float
    {
        return $this->roadTaxRenewalCost;
    }

    public function setRoadTaxRenewalCost(?float $roadTaxRenewalCost): self
    {
        $this->roadTaxRenewalCost = $roadTaxRenewalCost;
        return $this;
    }

    public function getTransportLicenseRenewalCost(): ?float
    {
        return $this->transportLicenseRenewalCost;
    }

    public function setTransportLicenseRenewalCost(?float $transportLicenseRenewalCost): self
    {
        $this->transportLicenseRenewalCost = $transportLicenseRenewalCost;
        return $this;
    }

    public function getFireExtinguisherRenewalCost(): ?float
    {
        return $this->fireExtinguisherRenewalCost;
    }

    public function setFireExtinguisherRenewalCost(?float $fireExtinguisherRenewalCost): self
    {
        $this->fireExtinguisherRenewalCost = $fireExtinguisherRenewalCost;
        return $this;
    }

    public function getCarteGriseRenewalCost(): ?float
    {
        return $this->carteGriseRenewalCost;
    }

    public function setCarteGriseRenewalCost(?float $carteGriseRenewalCost): self
    {
        $this->carteGriseRenewalCost = $carteGriseRenewalCost;
        return $this;
    }

    public function getLeaseContractRenewalCost(): ?float
    {
        return $this->leaseContractRenewalCost;
    }

    public function setLeaseContractRenewalCost(?float $leaseContractRenewalCost): self
    {
        $this->leaseContractRenewalCost = $leaseContractRenewalCost;
        return $this;
    }
}