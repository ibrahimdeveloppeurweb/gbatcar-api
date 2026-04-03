<?php

namespace App\Entity\Client;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\UserObjectNoCodeTrait;
use App\Repository\Client\ContractDocumentRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ContractDocumentRepository::class)
 */
class ContractDocument
{
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"contract", "client"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"contract", "payment", "client"})
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"contract", "payment", "client"})
     */
    private $originalName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"contract", "payment", "client"})
     */
    private $storedName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"contract", "payment", "client"})
     */
    private $mimeType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"contract", "payment", "client"})
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $contract;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }
    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getStoredName(): ?string
    {
        return $this->storedName;
    }
    public function setStoredName(string $storedName): self
    {
        $this->storedName = $storedName;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }
    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }
    public function setSize(?int $size): self
    {
        $this->size = $size;
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
}