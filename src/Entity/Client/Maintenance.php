<?php

namespace App\Entity\Client;

use App\Repository\Client\MaintenanceRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MaintenanceRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Maintenance
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"maintenance", "vehicle"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $dateIntervention;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $kilometrage;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $cost;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity=MaintenanceType::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $maintenanceType;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"maintenance", "vehicle"})
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"maintenance"})
     */
    private $daysInShop;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"maintenance"})
     */
    private $nextMaintenanceDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"maintenance"})
     */
    private $nextMaintenanceMileage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"maintenance"})
     */
    private $technician;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"maintenance"})
     */
    private $observation;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"maintenance"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"maintenance"})
     */
    private $endDate;

    // --- RELATIONS ---

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class, inversedBy="maintenances")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"maintenance"})
     */
    private $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"maintenance"})
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"maintenance"})
     */
    private $contract;

    /**
     * @ORM\OneToMany(targetEntity=MaintenanceDocument::class, mappedBy="maintenance", cascade={"persist", "remove"})
     * @Groups({"maintenance"})
     */
    private $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"maintenance"})
     */
    function getSearchableTitle(): string
    {
        return 'Intervention ' . $this->reference;
    }

    /**
     * @Groups({"maintenance"})
     */
    function getSearchableDetail(): string
    {
        return $this->type;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    public function getDateIntervention(): ?\DateTimeImmutable
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(?\DateTimeImmutable $dateIntervention): self
    {
        $this->dateIntervention = $dateIntervention;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;

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

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getDaysInShop(): ?int
    {
        return $this->daysInShop;
    }

    public function setDaysInShop(?int $daysInShop): self
    {
        $this->daysInShop = $daysInShop;

        return $this;
    }

    public function getNextMaintenanceDate(): ?\DateTimeImmutable
    {
        return $this->nextMaintenanceDate;
    }

    public function setNextMaintenanceDate(?\DateTimeImmutable $nextMaintenanceDate): self
    {
        $this->nextMaintenanceDate = $nextMaintenanceDate;

        return $this;
    }

    public function getNextMaintenanceMileage(): ?int
    {
        return $this->nextMaintenanceMileage;
    }

    public function setNextMaintenanceMileage(?int $nextMaintenanceMileage): self
    {
        $this->nextMaintenanceMileage = $nextMaintenanceMileage;

        return $this;
    }

    public function getMaintenanceType(): ?MaintenanceType
    {
        return $this->maintenanceType;
    }

    public function setMaintenanceType(?MaintenanceType $maintenanceType): self
    {
        $this->maintenanceType = $maintenanceType;

        return $this;
    }

    public function getTechnician(): ?string
    {
        return $this->technician;
    }

    public function setTechnician(?string $technician): self
    {
        $this->technician = $technician;

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getKilometrage(): ?float
    {
        return $this->kilometrage;
    }

    public function setKilometrage(?float $kilometrage): self
    {
        $this->kilometrage = $kilometrage;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }
}