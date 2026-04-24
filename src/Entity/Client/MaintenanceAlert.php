<?php

namespace App\Entity\Client;

use App\Repository\Client\MaintenanceAlertRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MaintenanceAlertRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class MaintenanceAlert
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"alert"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"alert"})
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"alert"})
     */
    private $type;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"alert"})
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"alert"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"alert"})
     */
    private $severity;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"alert"})
     */
    private $status;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"alert"})
     */
    private $repairCost;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"alert"})
     */
    private $driverInfo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"alert"})
     */
    private $policeReportUrl;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"alert"})
     */
    private $photosUrls = [];

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"alert"})
     */
    private $billingPayer;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     * @Groups({"alert"})
     */
    private $isInvoiced = false;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"alert"})
     */
    private $invoicedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"alert"})
     */
    private $observation;

    // --- RELATIONS ---

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class, inversedBy="alerts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"alert"})
     */
    private $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="alerts")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"alert"})
     */
    private $contract;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"alert"})
     */
    private $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @Groups({"alert"})
     */
    function getTitle(): string
    {
        return 'Alerte ' . $this->type;
    }

    /**
     * @Groups({"alert"})
     */
    function getDetail(): string
    {
        return 'Sévérité : ' . $this->severity;
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): self
    {
        $this->date = $date;

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

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(?string $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRepairCost(): ?float
    {
        return $this->repairCost;
    }

    public function setRepairCost(?float $repairCost): self
    {
        $this->repairCost = $repairCost;

        return $this;
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

    public function getContract(): ?Contract
    {
        if ($this->contract !== null) {
            try {
                $this->contract->getStatus();
            }
            catch (\Doctrine\ORM\EntityNotFoundException $e) {
                return null;
            }
        }
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getDriverInfo(): ?string
    {
        return $this->driverInfo;
    }

    public function setDriverInfo(?string $driverInfo): self
    {
        $this->driverInfo = $driverInfo;

        return $this;
    }

    public function getPoliceReportUrl(): ?string
    {
        return $this->policeReportUrl;
    }

    public function setPoliceReportUrl(?string $policeReportUrl): self
    {
        $this->policeReportUrl = $policeReportUrl;

        return $this;
    }

    public function getPhotosUrls(): ?array
    {
        return $this->photosUrls;
    }

    public function setPhotosUrls(?array $photosUrls): self
    {
        $this->photosUrls = $photosUrls;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;

        return $this;
    }

    public function getBillingPayer(): ?string
    {
        return $this->billingPayer;
    }

    public function setBillingPayer(?string $billingPayer): self
    {
        $this->billingPayer = $billingPayer;

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

    public function getIsInvoiced(): bool
    {
        return $this->isInvoiced;
    }

    public function setIsInvoiced(bool $isInvoiced): self
    {
        $this->isInvoiced = $isInvoiced;

        return $this;
    }

    public function getInvoicedAt(): ?\DateTimeImmutable
    {
        return $this->invoicedAt;
    }

    public function setInvoicedAt(?\DateTimeImmutable $invoicedAt): self
    {
        $this->invoicedAt = $invoicedAt;

        return $this;
    }
}