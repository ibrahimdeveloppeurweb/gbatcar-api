<?php

namespace App\Traits;

use App\Entity\Admin\User;
use Ramsey\Uuid\UuidInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait UserObjectNoCodeTrait
{
    /**
     * @var UuidInterface
     * @ORM\Column(type="uuid", length=255, unique=true)
     * @Groups({"user", "admin", "setting", "path", "role", "vehicle", "compliance", "client", "contract", "payment", "maintenance", "alert", "penalty", "subscription"})
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @Groups({"user", "admin", "setting", "vehicle", "compliance", "client", "contract", "payment", "maintenance", "alert", "penalty", "subscription"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @Groups({"user", "admin", "setting", "vehicle", "compliance", "client", "contract", "payment", "maintenance", "penalty", "subscription"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $createBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $updateBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $removeBy;

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUuid()
    {
        return $this->uuid ? $this->uuid->toString() : null;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreateBy(): ?User
    {
        return $this->createBy;
    }

    /**
     * @Groups({"user", "admin", "setting", "compliance", "client", "contract", "payment", "maintenance", "vehicle", "penalty"})
     */
    public function getCreate(): ?string
    {
        return $this->createBy ? $this->createBy->getLibelle() : null;
    }

    public function setCreateBy(?User $createBy): self
    {
        $this->createBy = $createBy;

        return $this;
    }

    public function getUpdateBy(): ?User
    {
        return $this->updateBy;
    }

    /**
     * @Groups({"user", "admin", "setting", "client"})
     */
    public function getUpdate(): ?string
    {
        return $this->updateBy ? $this->updateBy->getLibelle() : null;
    }

    public function setUpdateBy(?User $updateBy): self
    {
        $this->updateBy = $updateBy;

        return $this;
    }

    public function getRemoveBy(): ?User
    {
        return $this->removeBy;
    }

    public function setRemoveBy(?User $removeBy): self
    {
        $this->removeBy = $removeBy;

        return $this;
    }
}