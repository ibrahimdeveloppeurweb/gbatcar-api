<?php

namespace App\Entity\Extra;

use App\Repository\Extra\GeneralSettingHistoryRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=GeneralSettingHistoryRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class GeneralSettingHistory
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"setting_history"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"setting_history"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"setting_history"})
     */
    private $description;

    /**
     * @ORM\Column(type="json")
     * @Groups({"setting_history"})
     */
    private $previousValues = [];

    /**
     * @ORM\Column(type="json")
     * @Groups({"setting_history"})
     */
    private $newValues = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"setting_history"})
     */
    private $updatedBy;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->uuid = \Ramsey\Uuid\Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPreviousValues(): ?array
    {
        return $this->previousValues;
    }

    public function setPreviousValues(array $previousValues): self
    {
        $this->previousValues = $previousValues;

        return $this;
    }

    public function getNewValues(): ?array
    {
        return $this->newValues;
    }

    public function setNewValues(array $newValues): self
    {
        $this->newValues = $newValues;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @Groups({"setting_history"})
     */
    function getTitle(): string
    {
        return "Historique Paramètres : " . $this->description;
    }

    /**
     * @Groups({"setting_history"})
     */
    function getDetail(): string
    {
        return "Modification effectuée le " . ($this->createdAt ? $this->createdAt->format('d/m/Y H:i') : '');
    }
}