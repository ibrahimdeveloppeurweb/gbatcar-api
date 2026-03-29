<?php

namespace App\Entity\Client;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\UserObjectNoCodeTrait;
use App\Repository\Client\PaymentDocumentRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PaymentDocumentRepository::class)
 */
class PaymentDocument
{
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"payment"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"payment"})
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"payment"})
     */
    private $originalName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"payment"})
     */
    private $storedName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"payment"})
     */
    private $mimeType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"payment"})
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity=Payment::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $payment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(?string $libelle): self { $this->libelle = $libelle; return $this; }

    public function getOriginalName(): ?string { return $this->originalName; }
    public function setOriginalName(string $name): self { $this->originalName = $name; return $this; }

    public function getStoredName(): ?string { return $this->storedName; }
    public function setStoredName(string $name): self { $this->storedName = $name; return $this; }

    public function getMimeType(): ?string { return $this->mimeType; }
    public function setMimeType(?string $mimeType): self { $this->mimeType = $mimeType; return $this; }

    public function getSize(): ?int { return $this->size; }
    public function setSize(?int $size): self { $this->size = $size; return $this; }

    public function getPayment(): ?Payment { return $this->payment; }
    public function setPayment(?Payment $payment): self { $this->payment = $payment; return $this; }
}
