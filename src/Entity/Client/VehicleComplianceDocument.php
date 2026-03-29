<?php

namespace App\Entity\Client;

use App\Repository\Client\VehicleComplianceDocumentRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=VehicleComplianceDocumentRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class VehicleComplianceDocument
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"compliance", "vehicle"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"compliance", "vehicle"})
     */
    private $type;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"compliance", "vehicle"})
     */
    private $deliveryDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"compliance", "vehicle"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"compliance", "vehicle"})
     */
    private $endDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"compliance", "vehicle"})
     */
    private $renewalCost;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"compliance", "vehicle"})
     */
    private $affectation;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"compliance", "vehicle"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"compliance", "vehicle"})
     */
    private $fileUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"compliance", "vehicle"})
     */
    private $observation;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class, inversedBy="complianceDocuments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicle;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
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

    public function getDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeImmutable $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;
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

    public function getRenewalCost(): ?float
    {
        return $this->renewalCost;
    }

    public function setRenewalCost(?float $renewalCost): self
    {
        $this->renewalCost = $renewalCost;
        return $this;
    }

    public function getAffectation(): ?string
    {
        return $this->affectation;
    }

    public function setAffectation(?string $affectation): self
    {
        $this->affectation = $affectation;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(?string $fileUrl): self
    {
        $this->fileUrl = $fileUrl;
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

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;
        return $this;
    }

    public function getSearchableTitle(): string
    {
        return $this->type . ' - ' . ($this->startDate ? $this->startDate->format('d/m/Y') : '');
    }

    public function getSearchableDetail(): string
    {
        return $this->status . ' - ' . $this->renewalCost . ' FCFA';
    }
}