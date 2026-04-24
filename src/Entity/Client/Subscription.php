<?php

namespace App\Entity\Client;

use App\Repository\Client\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\UserObjectNoCodeTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SubscriptionRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Subscription
{
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"subscription"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"subscription"})
     */
    private $clientType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"subscription"})
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"subscription"})
     */
    private $companyName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"subscription"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"subscription"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"subscription"})
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"subscription"})
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"subscription"})
     */
    private $monthlyIncome;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"subscription"})
     */
    private $taxAccountNb;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"subscription"})
     */
    private $vehicleType;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"subscription"})
     */
    private $vehicleCount;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"subscription"})
     */
    private $contractType;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"subscription"})
     */
    private $paymentMethod;

    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $permis;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $cni;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $casier;
    /** @ORM\Column(type="text", nullable=true) @Groups({"subscription"}) */
    private $photos;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $certif;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $bulletin;

    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $rc;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $dfe;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $cniGerant;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $casierGerant;
    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $statut;

    /** @ORM\Column(type="string", length=255, nullable=true) @Groups({"subscription"}) */
    private $releve;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"subscription"})
     */
    private $status = 'EN_ATTENTE';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientType(): ?string
    {
        return $this->clientType;
    }
    public function setClientType(string $clientType): self
    {
        $this->clientType = $clientType;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }
    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }
    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }
    public function setLocation(string $location): self
    {
        $this->location = $location;
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

    public function getMonthlyIncome(): ?string
    {
        return $this->monthlyIncome;
    }
    public function setMonthlyIncome(?string $monthlyIncome): self
    {
        $this->monthlyIncome = $monthlyIncome;
        return $this;
    }

    public function getTaxAccountNb(): ?string
    {
        return $this->taxAccountNb;
    }
    public function setTaxAccountNb(?string $taxAccountNb): self
    {
        $this->taxAccountNb = $taxAccountNb;
        return $this;
    }

    public function getVehicleType(): ?string
    {
        return $this->vehicleType;
    }
    public function setVehicleType(string $vehicleType): self
    {
        $this->vehicleType = $vehicleType;
        return $this;
    }

    public function getVehicleCount(): ?int
    {
        return $this->vehicleCount;
    }
    public function setVehicleCount(int $vehicleCount): self
    {
        $this->vehicleCount = $vehicleCount;
        return $this;
    }

    public function getContractType(): ?string
    {
        return $this->contractType;
    }
    public function setContractType(string $contractType): self
    {
        $this->contractType = $contractType;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }
    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getPermis(): ?string
    {
        return $this->permis;
    }
    public function setPermis(?string $permis): self
    {
        $this->permis = $permis;
        return $this;
    }

    public function getCni(): ?string
    {
        return $this->cni;
    }
    public function setCni(?string $cni): self
    {
        $this->cni = $cni;
        return $this;
    }

    public function getCasier(): ?string
    {
        return $this->casier;
    }
    public function setCasier(?string $casier): self
    {
        $this->casier = $casier;
        return $this;
    }

    public function getPhotos(): ?string
    {
        return $this->photos;
    }
    public function setPhotos(?string $photos): self
    {
        $this->photos = $photos;
        return $this;
    }

    public function getCertif(): ?string
    {
        return $this->certif;
    }
    public function setCertif(?string $certif): self
    {
        $this->certif = $certif;
        return $this;
    }

    public function getBulletin(): ?string
    {
        return $this->bulletin;
    }
    public function setBulletin(?string $bulletin): self
    {
        $this->bulletin = $bulletin;
        return $this;
    }

    public function getRc(): ?string
    {
        return $this->rc;
    }
    public function setRc(?string $rc): self
    {
        $this->rc = $rc;
        return $this;
    }

    public function getDfe(): ?string
    {
        return $this->dfe;
    }
    public function setDfe(?string $dfe): self
    {
        $this->dfe = $dfe;
        return $this;
    }

    public function getCniGerant(): ?string
    {
        return $this->cniGerant;
    }
    public function setCniGerant(?string $cniGerant): self
    {
        $this->cniGerant = $cniGerant;
        return $this;
    }

    public function getCasierGerant(): ?string
    {
        return $this->casierGerant;
    }
    public function setCasierGerant(?string $casierGerant): self
    {
        $this->casierGerant = $casierGerant;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }
    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getReleve(): ?string
    {
        return $this->releve;
    }
    public function setReleve(?string $releve): self
    {
        $this->releve = $releve;
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

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"subscription"})
     */
    private $rejectionReason;

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }
    public function setRejectionReason(?string $rejectionReason): self
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }
}