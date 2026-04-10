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
     * @Groups({"contract", "payment", "maintenance", "client", "penalty"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract", "payment", "maintenance", "vehicle", "client", "penalty"})
     */
    private $reference;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract", "vehicle", "client"})
     */
    private $totalAmount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract", "vehicle", "client"})
     */
    private $status;


    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract", "client"})
     */
    private $caution;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract", "client"})
     */
    private $nextPaymentAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract", "client"})
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
     * @Groups({"contract", "vehicle"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"contract", "vehicle"})
     */
    private $endDate;

    // --- STATUTS & PROGRESSION ---

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"contract", "vehicle", "client"})
     */
    private $paymentStatus;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract", "vehicle"})
     */
    private $progressPercentage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"contract", "vehicle"})
     */
    private $daysLate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"contract", "vehicle"})
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
     * @Groups({"contract", "payment"})
     */
    private $paymentFrequency;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $dailyRate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $fraisDossier;

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

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"contract"})
     */
    private $prixDeVente;

    // --- RELATIONS ---

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"contract:client"})
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"contract", "payment", "client"})
     */
    private $vehicle;

    /**
     * @ORM\OneToMany(targetEntity=ContractVehicleDemand::class, mappedBy="contract", cascade={"persist", "remove"})
     * @Groups({"contract", "payment", "client", "penalty"})
     */
    private $vehicleDemands;

    /**
     * @ORM\OneToMany(targetEntity=Payment::class, mappedBy="contract")
     * @Groups({"contract:payments", "client"})
     * @ORM\OrderBy({"date" = "DESC", "id" = "DESC"})
     */
    private $payments;

    /**
     * @ORM\OneToMany(targetEntity=PaymentSchedule::class, mappedBy="contract", cascade={"persist", "remove"})
     * @Groups({"contract:schedules"})
     * @ORM\OrderBy({"expectedDate" = "ASC"})
     */
    private $paymentSchedules;


    /**
     * @ORM\OneToMany(targetEntity=Penalty::class, mappedBy="contract")
     * @Groups({"contract"})
     * @ORM\OrderBy({"date" = "DESC", "id" = "DESC"})
     */
    private $penalties;

    /**
     * @ORM\OneToMany(targetEntity=MaintenanceAlert::class, mappedBy="contract")
     * @Groups({"contract"})
     */
    private $alerts;

    /**
     * @ORM\OneToMany(targetEntity=ContractDocument::class, mappedBy="contract", cascade={"persist", "remove"})
     * @Groups({"contract", "client"})
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity=PromiseToPay::class, mappedBy="contract", cascade={"persist", "remove"})
     * @Groups({"contract:promises"})
     * @ORM\OrderBy({"expectedDate" = "DESC"})
     */
    private $promises;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->penalties = new ArrayCollection();
        $this->alerts = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->vehicleDemands = new ArrayCollection();
        $this->paymentSchedules = new ArrayCollection();
        $this->promises = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ContractVehicleDemand>
     */
    public function getVehicleDemands(): Collection
    {
        return $this->vehicleDemands;
    }

    public function addVehicleDemand(ContractVehicleDemand $vehicleDemand): self
    {
        if (!$this->vehicleDemands->contains($vehicleDemand)) {
            $this->vehicleDemands[] = $vehicleDemand;
            $vehicleDemand->setContract($this);
        }

        return $this;
    }

    public function removeVehicleDemand(ContractVehicleDemand $vehicleDemand): self
    {
        $this->vehicleDemands->removeElement($vehicleDemand);
        return $this;
    }

    /**
     * @return Collection<int, PaymentSchedule>
     */
    public function getPaymentSchedules(): Collection
    {
        return $this->paymentSchedules;
    }

    public function addPaymentSchedule(PaymentSchedule $paymentSchedule): self
    {
        if (!$this->paymentSchedules->contains($paymentSchedule)) {
            $this->paymentSchedules[] = $paymentSchedule;
            $paymentSchedule->setContract($this);
        }

        return $this;
    }

    public function removePaymentSchedule(PaymentSchedule $paymentSchedule): self
    {
        if ($this->paymentSchedules->removeElement($paymentSchedule)) {
            // set the owning side to null (unless already changed)
            if ($paymentSchedule->getContract() === $this) {
                $paymentSchedule->setContract(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PromiseToPay>
     */
    public function getPromises(): Collection
    {
        return $this->promises;
    }

    public function addPromise(PromiseToPay $promise): self
    {
        if (!$this->promises->contains($promise)) {
            $this->promises[] = $promise;
            $promise->setContract($this);
        }

        return $this;
    }

    public function removePromise(PromiseToPay $promise): self
    {
        if ($this->promises->removeElement($promise)) {
            if ($promise->getContract() === $this) {
                $promise->setContract(null);
            }
        }

        return $this;
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




    public function getCaution(): ?float
    {
        return $this->caution;
    }

    public function setCaution(?float $caution): self
    {
        $this->caution = $caution;

        return $this;
    }

    public function getFraisDossier(): ?float
    {
        return $this->fraisDossier;
    }

    public function setFraisDossier(?float $fraisDossier): self
    {
        $this->fraisDossier = $fraisDossier;

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

    public function getPrixDeVente(): ?float
    {
        return $this->prixDeVente;
    }

    public function setPrixDeVente(?float $prixDeVente): self
    {
        $this->prixDeVente = $prixDeVente;

        return $this;
    }

    /**
     * @return Collection<int, ContractDocument>
     * @Groups({"contract", "payment"})
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(ContractDocument $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setContract($this);
        }

        return $this;
    }

    public function removeDocument(ContractDocument $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getContract() === $this) {
                $document->setContract(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"contract", "vehicle", "client"})
     */
    public function getPaidAmount(): ?float
    {
        $totalPaid = 0;
        $fees = $this->getFraisDossier() ?: 0;

        foreach ($this->payments as $payment) {
            $status = strtoupper($payment->getStatus() ?? '');
            if (in_array($status, ['VALIDÉ', 'VALIDATED', 'VALIDé'])) {
                // Ignore maintenance/incident refacturations and penalties
                if (!in_array($payment->getType(), ['RÉPARATION_CLIENT', 'FRAIS_AGENCE', 'PÉNALITÉ'])) {
                    $totalPaid += $payment->getAmount();
                }
            }
        }

        return max(0, $totalPaid - $fees);
    }

    /**
     * @Groups({"contract", "vehicle", "client"})
     */
    public function getVehicleCount(): int
    {
        $count = 0;
        if ($this->vehicle) {
            return 1;
        }

        foreach ($this->vehicleDemands as $demand) {
            $count += ($demand->getQuantity() ?: 0);
        }

        return $count ?: 1;
    }

    /**
     * @Groups({"contract", "payment", "client", "payment:contract", "penalty"})
     */
    public function getVehicleSummary(): string
    {
        $vehicles = [];

        // 1. Single specific vehicle
        if ($this->vehicle) {
            $vehicles[] = $this->vehicle->getLibelle();
        }

        // 2. Fleet vehicles from demands
        foreach ($this->vehicleDemands as $demand) {
            foreach ($demand->getAssignedVehicles() as $v) {
                $lib = $v->getLibelle();
                if (!in_array($lib, $vehicles)) {
                    $vehicles[] = $lib;
                }
            }
        }

        if (empty($vehicles)) {
            return "Aucun véhicule assigné";
        }

        return implode(', ', $vehicles);
    }

    /**
     * @Groups({"contract"})
     */
    public function getHasSchedules(): bool
    {
        return $this->paymentSchedules->count() > 0;
    }

    /**
     * @Groups({"contract"})
     */
    public function getRiskAnalysis(): array
    {
        if ($this->getStatus() === 'SUSPENDU' || $this->getStatus() === 'SUSPENDED') {
            return [
                'level' => 'SUSPENDU',
                'code' => 'SUSPENDED',
                'reason' => 'Contrat en pause (Panne/Accident)',
                'dpd' => 0,
                'unpaidArrears' => 0,
                'class' => 'text-secondary'
            ];
        }

        if (!$this->getHasSchedules()) {
            return [
                'level' => 'NON DÉFINI',
                'code' => 'NONE',
                'reason' => 'Échéancier non généré',
                'dpd' => 0,
                'unpaidArrears' => 0,
                'class' => 'text-muted'
            ];
        }

        $today = new \DateTimeImmutable('today');
        $oldestUnpaid = null;

        foreach ($this->paymentSchedules as $schedule) {
            if ($schedule->getStatus() !== 'Payé' && $schedule->getExpectedDate() < $today) {
                if ($oldestUnpaid === null || $schedule->getExpectedDate() < $oldestUnpaid->getExpectedDate()) {
                    $oldestUnpaid = $schedule;
                }
            }
        }

        $unpaidArrears = $this->getUnpaidArrears();

        if ($oldestUnpaid) {
            // Use diff for more accurate DPD
            $diff = $today->diff($oldestUnpaid->getExpectedDate());
            $dpd = (int)$diff->format('%a');

            // Threshold: 10 days for critical
            $isCritical = ($dpd >= 10) || ($this->getPaymentStatus() === 'Impayé définitif');
            $level = $isCritical ? 'CRITIQUE' : 'ÉLEVÉ';
            $class = $isCritical ? 'text-danger' : 'text-warning';

            return [
                'level' => $level,
                'code' => $level,
                'reason' => $dpd > 0 ? "En retard de $dpd jours" : "Échéance impayée aujourd'hui",
                'dpd' => $dpd,
                'unpaidArrears' => $unpaidArrears,
                'class' => $class
            ];
        }

        // Up to date: Check progress
        $total = $this->getTotalAmount() ?: 1;
        $paid = $this->getPaidAmount() ?: 0;
        $progress = ($paid / $total) * 100;

        if ($progress < 25) {
            return [
                'level' => 'MOYEN',
                'code' => 'MOYEN',
                'reason' => 'Période de probation (< 25%)',
                'dpd' => 0,
                'unpaidArrears' => $unpaidArrears,
                'class' => 'text-info'
            ];
        }

        return [
            'level' => 'BAS',
            'code' => 'BAS',
            'reason' => 'Client à jour et fiable',
            'dpd' => 0,
            'unpaidArrears' => $unpaidArrears,
            'class' => 'text-success'
        ];
    }

    /**
     * @Groups({"contract"})
     */
    public function getUnpaidArrears(): float
    {
        $total = 0;
        $today = new \DateTimeImmutable('today');

        // 1. Overdue installments
        foreach ($this->paymentSchedules as $schedule) {
            if ($schedule->getStatus() !== 'Payé' && $schedule->getExpectedDate() < $today) {
                $total += ($schedule->getAmount() - ($schedule->getPaidAmount() ?: 0));
            }
        }

        // 2. Unpaid penalties
        foreach ($this->penalties as $penalty) {
            $status = strtoupper($penalty->getStatus() ?? '');
            if (!in_array($status, ['PAYÉ', 'PAYE', 'SOLDÉ', 'SOLDE', 'ANNULÉ', 'ANNULE'])) {
                $total += ($penalty->getAmount() - ($penalty->getPaidAmount() ?: 0));
            }
        }

        return $total;
    }}