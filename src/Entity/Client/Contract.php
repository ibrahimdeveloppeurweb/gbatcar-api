<?php

namespace App\Entity\Client;

use App\Repository\Client\ContractRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ContractRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Contract
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"contract", "payment", "maintenance"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract", "payment", "maintenance"})
     */
    private $reference;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $totalAmount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract"})
     */
    private $status;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $paidAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $caution;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $nextPaymentAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $unpaidAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $projectedMargin;

    // --- DATES ---

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"contract"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"contract"})
     */
    private $endDate;

    // --- STATUTS & PROGRESSION ---

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"contract"})
     */
    private $paymentStatus;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $progressPercentage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"contract"})
     */
    private $daysLate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"contract"})
     */
    private $riskLevel;

    // --- PARAMÈTRES DU CONTRAT ---

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract"})
     */
    private $usageType;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"contract"})
     */
    private $paymentFrequency;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $dailyRate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"contract"})
     */
    private $durationInMonths;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $initialDeposit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract"})
     */
    private $maintenanceAndInsurance;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"contract"})
     */
    private $gracePeriodDays;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $penaltyRate;

    // --- CHECKLIST DOSSIER ---

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"contract"})
     */
    private $hasValidID;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"contract"})
     */
    private $hasDriverLicense;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"contract"})
     */
    private $hasProofOfAddress;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"contract"})
     */
    private $hasCriminalRecord;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract"})
     */
    private $lieu;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"contract"})
     */
    private $observation;

    // --- RELATIONS ---

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"contract"})
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"contract"})
     */
    private $vehicle;

    /**
     * @ORM\OneToMany(targetEntity=Payment::class, mappedBy="contract")
     */
    private $payments;

    /**
     * @ORM\OneToMany(targetEntity=Penalty::class, mappedBy="contract")
     */
    private $penalties;

    /**
     * @ORM\OneToMany(targetEntity=MaintenanceAlert::class, mappedBy="contract")
     */
    private $alerts;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->penalties = new ArrayCollection();
        $this->alerts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"contract"})
     */
    function getSearchableTitle(): string
    {
        return 'Contrat ' . $this->reference;
    }

    /**
     * @Groups({"contract"})
     */
    function getSearchableDetail(): string
    {
        return 'Contrat ' . $this->status;
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

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

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

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setContract($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getContract() === $this) {
                $payment->setContract(null);
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
            $penalty->setContract($this);
        }

        return $this;
    }

    public function removePenalty(Penalty $penalty): self
    {
        if ($this->penalties->removeElement($penalty)) {
            // set the owning side to null (unless already changed)
            if ($penalty->getContract() === $this) {
                $penalty->setContract(null);
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
            $alert->setContract($this);
        }

        return $this;
    }

    public function removeAlert(MaintenanceAlert $alert): self
    {
        if ($this->alerts->removeElement($alert)) {
            // set the owning side to null (unless already changed)
            if ($alert->getContract() === $this) {
                $alert->setContract(null);
            }
        }

        return $this;
    }

    public function getPaidAmount(): ?float
    {
        return $this->paidAmount;
    }

    public function setPaidAmount(?float $paidAmount): self
    {
        $this->paidAmount = $paidAmount;

        return $this;
    }

    public function getCaution(): ?float
    {
        return $this->caution;
    }

    public function setCaution(?float $caution): self
    {
        $this->caution = $caution;

        return $this;
    }

    public function getNextPaymentAmount(): ?float
    {
        return $this->nextPaymentAmount;
    }

    public function setNextPaymentAmount(?float $nextPaymentAmount): self
    {
        $this->nextPaymentAmount = $nextPaymentAmount;

        return $this;
    }

    public function getUnpaidAmount(): ?float
    {
        return $this->unpaidAmount;
    }

    public function setUnpaidAmount(?float $unpaidAmount): self
    {
        $this->unpaidAmount = $unpaidAmount;

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

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getProgressPercentage(): ?float
    {
        return $this->progressPercentage;
    }

    public function setProgressPercentage(?float $progressPercentage): self
    {
        $this->progressPercentage = $progressPercentage;

        return $this;
    }

    public function getDaysLate(): ?int
    {
        return $this->daysLate;
    }

    public function setDaysLate(?int $daysLate): self
    {
        $this->daysLate = $daysLate;

        return $this;
    }

    public function getRiskLevel(): ?string
    {
        return $this->riskLevel;
    }

    public function setRiskLevel(?string $riskLevel): self
    {
        $this->riskLevel = $riskLevel;

        return $this;
    }

    public function getUsageType(): ?string
    {
        return $this->usageType;
    }

    public function setUsageType(?string $usageType): self
    {
        $this->usageType = $usageType;

        return $this;
    }

    public function getPaymentFrequency(): ?string
    {
        return $this->paymentFrequency;
    }

    public function setPaymentFrequency(?string $paymentFrequency): self
    {
        $this->paymentFrequency = $paymentFrequency;

        return $this;
    }

    public function getDailyRate(): ?float
    {
        return $this->dailyRate;
    }

    public function setDailyRate(?float $dailyRate): self
    {
        $this->dailyRate = $dailyRate;

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

    public function getInitialDeposit(): ?float
    {
        return $this->initialDeposit;
    }

    public function setInitialDeposit(?float $initialDeposit): self
    {
        $this->initialDeposit = $initialDeposit;

        return $this;
    }

    public function getMaintenanceAndInsurance(): ?string
    {
        return $this->maintenanceAndInsurance;
    }

    public function setMaintenanceAndInsurance(?string $maintenanceAndInsurance): self
    {
        $this->maintenanceAndInsurance = $maintenanceAndInsurance;

        return $this;
    }

    public function getGracePeriodDays(): ?int
    {
        return $this->gracePeriodDays;
    }

    public function setGracePeriodDays(?int $gracePeriodDays): self
    {
        $this->gracePeriodDays = $gracePeriodDays;

        return $this;
    }

    public function getPenaltyRate(): ?float
    {
        return $this->penaltyRate;
    }

    public function setPenaltyRate(?float $penaltyRate): self
    {
        $this->penaltyRate = $penaltyRate;

        return $this;
    }

    public function getHasValidID(): ?bool
    {
        return $this->hasValidID;
    }

    public function setHasValidID(?bool $hasValidID): self
    {
        $this->hasValidID = $hasValidID;

        return $this;
    }

    public function getHasDriverLicense(): ?bool
    {
        return $this->hasDriverLicense;
    }

    public function setHasDriverLicense(?bool $hasDriverLicense): self
    {
        $this->hasDriverLicense = $hasDriverLicense;

        return $this;
    }

    public function getHasProofOfAddress(): ?bool
    {
        return $this->hasProofOfAddress;
    }

    public function setHasProofOfAddress(?bool $hasProofOfAddress): self
    {
        $this->hasProofOfAddress = $hasProofOfAddress;

        return $this;
    }

    public function getHasCriminalRecord(): ?bool
    {
        return $this->hasCriminalRecord;
    }

    public function setHasCriminalRecord(?bool $hasCriminalRecord): self
    {
        $this->hasCriminalRecord = $hasCriminalRecord;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;

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

    public function getProjectedMargin(): ?float
    {
        return $this->projectedMargin;
    }

    public function setProjectedMargin(?float $projectedMargin): self
    {
        $this->projectedMargin = $projectedMargin;

        return $this;
    }
}