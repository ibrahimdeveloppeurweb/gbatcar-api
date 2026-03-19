<?php

namespace App\Entity\Client;

use App\Repository\Client\PenaltyRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PenaltyRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Penalty
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"penalty", "compliance"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $reference;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $reason;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $date;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $dueDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $severity;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $observation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"penalty", "compliance"})
     */
    private $proofUrl;

    // --- RELATIONS ---

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="penalties")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"penalty"})
     */
    private $contract;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"penalty"})
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"penalty"})
     */
    private $vehicle;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"penalty"})
     */
    function getSearchableTitle(): string
    {
        return 'Pénalité ' . $this->reference;
    }

    /**
     * @Groups({"penalty"})
     */
    function getSearchableDetail(): string
    {
        return 'Montant : ' . $this->amount . ' FCFA';
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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

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

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

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

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getProofUrl(): ?string
    {
        return $this->proofUrl;
    }

    public function setProofUrl(?string $proofUrl): self
    {
        $this->proofUrl = $proofUrl;

        return $this;
    }
}