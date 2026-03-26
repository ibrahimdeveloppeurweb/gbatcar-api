<?php

namespace App\Entity\Client;

use App\Repository\Client\PaymentRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PaymentRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Payment
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    const ETAT = [
        'VALIDÉ' => 'VALIDÉ',
        'INVALIDE' => 'INVALIDE',
        'REJETE' => 'REJETE'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"payment", "contract"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"payment", "contract"})
     */
    private $reference;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"payment", "contract"})
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"payment", "contract"})
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"payment", "contract"})
     */
    private $period;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"payment", "contract"})
     */
    private $method;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"payment", "contract"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"payment"})
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"payment"})
     */
    private $observation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"payment", "contract"})
     */
    private $recordedBy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"payment"})
     */
    private $receiptUrl;

    // --- RELATIONS ---

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"payment:contract"})
     */
    private $contract;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"payment"})
     */
    private $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"payment"})
     */
    function getSearchableTitle(): string
    {
        return 'Paiement ' . $this->reference;
    }

    /**
     * @Groups({"payment"})
     */
    function getSearchableDetail(): string
    {
        return 'Paiement ' . $this->method;
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

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    public function getRecordedBy(): ?string
    {
        return $this->recordedBy;
    }

    public function setRecordedBy(?string $recordedBy): self
    {
        $this->recordedBy = $recordedBy;

        return $this;
    }

    public function getReceiptUrl(): ?string
    {
        return $this->receiptUrl;
    }

    public function setReceiptUrl(?string $receiptUrl): self
    {
        $this->receiptUrl = $receiptUrl;

        return $this;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    public function setPeriod(?string $period): self
    {
        $this->period = $period;

        return $this;
    }
}