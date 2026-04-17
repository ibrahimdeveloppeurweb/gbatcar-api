<?php

namespace App\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\Admin\AuditLogRepository;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=AuditLogRepository::class)
 * @ORM\Table(name="audit_log")
 */
class AuditLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"audit:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"audit:read"})
     */
    private $userFullName;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"audit:read"})
     */
    private $userInitials;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"audit:read"})
     */
    private $module;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"audit:read"})
     */
    private $action;

    /**
     * @ORM\Column(type="text")
     * @Groups({"audit:read"})
     */
    private $details;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"audit:read"})
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserFullName(): ?string
    {
        return $this->userFullName;
    }

    public function setUserFullName(string $userFullName): self
    {
        $this->userFullName = $userFullName;
        return $this;
    }

    public function getUserInitials(): ?string
    {
        return $this->userInitials;
    }

    public function setUserInitials(string $userInitials): self
    {
        $this->userInitials = $userInitials;
        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}