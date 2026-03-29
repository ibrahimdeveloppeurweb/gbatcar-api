<?php

namespace App\Entity\Client;

use App\Repository\Client\PromiseToPayRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PromiseToPayRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class PromiseToPay
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    const STATUS = [
        'PENDING' => 'EN ATTENTE',
        'KEPT' => 'TENUE',
        'BROKEN' => 'ROMPUE',
        'CANCELLED' => 'ANNULÉE'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"promise", "contract"})
     */
    private $id;

    /**
     * @ORM\Column(type="date_immutable")
     * @Groups({"promise", "contract"})
     */
    private $expectedDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"promise", "contract"})
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"promise", "contract"})
     */
    private $status = self::STATUS['PENDING'];

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"promise", "contract"})
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="promises")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"promise:contract"})
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

    public function setExpectedDate(\DateTimeImmutable $expectedDate): self
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
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

    /**
     * @Groups({"promise"})
     */
    public function getSearchableTitle(): string
    {
        return 'Promesse du ' . $this->expectedDate->format('d/m/Y');
    }

    /**
     * @Groups({"promise"})
     */
    public function getSearchableDetail(): string
    {
        return $this->status . ($this->amount ? ' - ' . $this->amount . ' FCFA' : '');
    }
}
