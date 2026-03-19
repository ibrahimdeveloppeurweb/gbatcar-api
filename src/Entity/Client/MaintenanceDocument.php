<?php

namespace App\Entity\Client;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\UserObjectNoCodeTrait;
use App\Repository\Client\MaintenanceDocumentRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MaintenanceDocumentRepository::class)
 */
class MaintenanceDocument
{
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"maintenance"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"maintenance"})
     */
    private $originalName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"maintenance"})
     */
    private $storedName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"maintenance"})
     */
    private $mimeType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"maintenance"})
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity=Maintenance::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $maintenance;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
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

    public function getMaintenance(): ?Maintenance
    {
        return $this->maintenance;
    }
    public function setMaintenance(?Maintenance $maintenance): self
    {
        $this->maintenance = $maintenance;
        return $this;
    }
}