<?php

namespace App\Entity\Client;

use App\Repository\Client\ClientRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Client
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"client", "vehicle", "contract", "payment", "alert"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client", "vehicle", "contract", "payment", "alert"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client", "vehicle", "contract", "payment", "alert"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client", "vehicle", "contract", "payment", "alert"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $paymentStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $idNumber;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $hasValidID;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client", "vehicle", "contract"})
     */
    private $civilite;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"client"})
     */
    private $gender;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"client"})
     */
    private $birthDate;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"client"})
     */
    private $validationDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $birthPlace;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"client"})
     */
    private $nationality;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"client"})
     */
    private $maritalStatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"client"})
     */
    private $childrenCount;

    // Contact

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"client"})
     */
    private $pays;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $neighborhood;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"client"})
     */
    private $address;

    // Documents Identité

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"client"})
     */
    private $idIssueDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $licenseNumber;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $hasDriverLicense;

    // Profil socio-économique

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $educationLevel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $incomeBracket;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $housingStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $primaryBankAccount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $discoveryChannel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"client"})
     */
    private $drivingExperienceYears;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $previousCreditExperience;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $repaymentSource;

    // Media & Documents Numériques

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"client"})
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $idScanUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"client"})
     */
    private $licenseScanUrl;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"client"})
     */
    private $type;

    // Agrégats Financiers (Caches)

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $amountPaid;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $totalAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $cautionAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $nextPaymentAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $unpaidAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $walletBalance;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $fines;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"client"})
     */
    private $initialDeposit;

    // --- RELATIONS ---

    /**
     * @ORM\OneToMany(targetEntity=Vehicle::class, mappedBy="client")
     * @Groups({"client"})
     */
    private $vehicles;

    /**
     * @ORM\OneToMany(targetEntity=Contract::class, mappedBy="client")
     * @Groups({"client"})
     */
    private $contracts;

    /**
     * @ORM\OneToMany(targetEntity=Payment::class, mappedBy="client")
     */
    private $payments;

    // Construct & Getters & Setters
    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"client"})
     */
    function getSearchableTitle(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * @Groups({"client"})
     */
    function getSearchableDetail(): string
    {
        return 'Client/Locataire';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    public function setIdNumber(?string $idNumber): self
    {
        $this->idNumber = $idNumber;

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

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    /**
     * @Groups({"client"})
     */
    public function getActiveContract(): ?Contract
    {
        $contracts = $this->contracts->toArray();
        if (empty($contracts)) {
            return null;
        }

        // Sort by ID DESC to get the latest
        usort($contracts, function ($a, $b) {
            return $b->getId() <=> $a->getId();
        });

        // Take the latest one that is VALIDÉ
        foreach ($contracts as $contract) {
            if ($contract instanceof Contract && $contract->getStatus() === 'VALIDÉ') {
                return $contract;
            }
        }

        // Fallback: Latest one
        $latest = $contracts[0] ?? null;
        return $latest instanceof Contract ? $latest : null;
    }

    /**
     * @Groups({"client"})
     */
    public function getVehicle(): ?Vehicle
    {
        // 1. Direct relationship (if set)
        if (!$this->vehicles->isEmpty()) {
            return $this->vehicles->first();
        }

        // 2. Fallback: Take the vehicle from the most recent contract
        $contracts = $this->contracts->toArray();
        if (empty($contracts)) {
            return null;
        }

        // Simple sort by ID desc to get the latest
        usort($contracts, function ($a, $b) {
            return $b->getId() <=> $a->getId();
        });

        foreach ($contracts as $contract) {
            if ($contract->getVehicle()) {
                return $contract->getVehicle();
            }
        }

        return null;
    }

    public function addVehicle(Vehicle $vehicle): self
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles[] = $vehicle;
            $vehicle->setClient($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): self
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getClient() === $this) {
                $vehicle->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contract>
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): self
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts[] = $contract;
            $contract->setClient($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): self
    {
        if ($this->contracts->removeElement($contract)) {
            // set the owning side to null (unless already changed)
            if ($contract->getClient() === $this) {
                $contract->setClient(null);
            }
        }

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
            $payment->setClient($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getClient() === $this) {
                $payment->setClient(null);
            }
        }

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeImmutable $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): self
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?string $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getChildrenCount(): ?int
    {
        return $this->childrenCount;
    }

    public function setChildrenCount(?int $childrenCount): self
    {
        $this->childrenCount = $childrenCount;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getNeighborhood(): ?string
    {
        return $this->neighborhood;
    }

    public function setNeighborhood(?string $neighborhood): self
    {
        $this->neighborhood = $neighborhood;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getIdIssueDate(): ?\DateTimeImmutable
    {
        return $this->idIssueDate;
    }

    public function setIdIssueDate(?\DateTimeImmutable $idIssueDate): self
    {
        $this->idIssueDate = $idIssueDate;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;

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

    public function getEducationLevel(): ?string
    {
        return $this->educationLevel;
    }

    public function setEducationLevel(?string $educationLevel): self
    {
        $this->educationLevel = $educationLevel;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getIncomeBracket(): ?string
    {
        return $this->incomeBracket;
    }

    public function setIncomeBracket(?string $incomeBracket): self
    {
        $this->incomeBracket = $incomeBracket;

        return $this;
    }

    public function getHousingStatus(): ?string
    {
        return $this->housingStatus;
    }

    public function setHousingStatus(?string $housingStatus): self
    {
        $this->housingStatus = $housingStatus;

        return $this;
    }

    public function getPrimaryBankAccount(): ?string
    {
        return $this->primaryBankAccount;
    }

    public function setPrimaryBankAccount(?string $primaryBankAccount): self
    {
        $this->primaryBankAccount = $primaryBankAccount;

        return $this;
    }

    public function getDiscoveryChannel(): ?string
    {
        return $this->discoveryChannel;
    }

    public function setDiscoveryChannel(?string $discoveryChannel): self
    {
        $this->discoveryChannel = $discoveryChannel;

        return $this;
    }

    public function getDrivingExperienceYears(): ?int
    {
        return $this->drivingExperienceYears;
    }

    public function setDrivingExperienceYears(?int $drivingExperienceYears): self
    {
        $this->drivingExperienceYears = $drivingExperienceYears;

        return $this;
    }

    public function getPreviousCreditExperience(): ?string
    {
        return $this->previousCreditExperience;
    }

    public function setPreviousCreditExperience(?string $previousCreditExperience): self
    {
        $this->previousCreditExperience = $previousCreditExperience;

        return $this;
    }

    public function getRepaymentSource(): ?string
    {
        return $this->repaymentSource;
    }

    public function setRepaymentSource(?string $repaymentSource): self
    {
        $this->repaymentSource = $repaymentSource;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

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

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(?float $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

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

    public function getCautionAmount(): ?float
    {
        return $this->cautionAmount;
    }

    public function setCautionAmount(?float $cautionAmount): self
    {
        $this->cautionAmount = $cautionAmount;

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

    public function getWalletBalance(): ?float
    {
        return $this->walletBalance;
    }

    public function setWalletBalance(?float $walletBalance): self
    {
        $this->walletBalance = $walletBalance;

        return $this;
    }

    public function getFines(): ?float
    {
        return $this->fines;
    }

    public function setFines(?float $fines): self
    {
        $this->fines = $fines;

        return $this;
    }

    public function getIdScanUrl(): ?string
    {
        return $this->idScanUrl;
    }

    public function setIdScanUrl(?string $idScanUrl): self
    {
        $this->idScanUrl = $idScanUrl;

        return $this;
    }

    public function getLicenseScanUrl(): ?string
    {
        return $this->licenseScanUrl;
    }

    public function setLicenseScanUrl(?string $licenseScanUrl): self
    {
        $this->licenseScanUrl = $licenseScanUrl;

        return $this;
    }

    public function getValidationDate(): ?\DateTimeImmutable
    {
        return $this->validationDate;
    }

    public function setValidationDate(?\DateTimeImmutable $validationDate): self
    {
        $this->validationDate = $validationDate;

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
}
