<?php

namespace App\Entity\Client;

use App\Repository\Client\PaymentScheduleRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PaymentScheduleRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class PaymentSchedule
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"payment_schedule", "contract"})
     */
    private $id;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"payment_schedule", "contract"})
     */
    private $expectedDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"payment_schedule", "contract"})
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"payment_schedule", "contract"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"payment_schedule", "contract"})
     */
    private $status;

    /**
     * Amount already paid towards this specific installment (for partial payments)
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"payment_schedule", "contract"})
     */
    private $paidAmount = 0;

    /**
     * When this installment was fully paid
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"payment_schedule", "contract"})
     */
    private $paidAt;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="paymentSchedules")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"payment_schedule"})
     */
    private $contract;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpectedDate(): ?\DateTimeImmutable
    {
        return $this->expectedDate;
    }

    public function setExpectedDate(?\DateTimeImmutable $expectedDate): self
    {
        $this->expectedDate = $expectedDate;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
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

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;
        return $this;
    }

    public function getPaidAmount(): ?float
    {
        return $this->paidAmount ?? 0;
    }

    public function setPaidAmount(?float $paidAmount): self
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): self
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    /**
     * @Groups({"payment_schedule"})
     */
    public function getSearchableTitle(): string
    {
        return 'Échéance ' . ($this->expectedDate ? $this->expectedDate->format('d/m/Y') : '');
    }

    /**
     * @Groups({"payment_schedule"})
     */
    public function getSearchableDetail(): string
    {
        return 'Statut: ' . $this->status . ' - Montant: ' . $this->amount;
    }
}