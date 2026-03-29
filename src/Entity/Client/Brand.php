<?php

namespace App\Entity\Client;

use App\Repository\Client\BrandRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=BrandRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Brand
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"brand", "vehicle_model", "vehicle"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"brand", "vehicle_model", "vehicle", "contract", "demand", "alert"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=VehicleModel::class, mappedBy="brand", cascade={"persist", "remove"})
     * @Groups({"brand"})
     */
    private $models;

    public function __construct()
    {
        $this->models = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, VehicleModel>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(VehicleModel $model): self
    {
        if (!$this->models->contains($model)) {
            $this->models[] = $model;
            $model->setBrand($this);
        }

        return $this;
    }

    public function removeModel(VehicleModel $model): self
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getBrand() === $this) {
                $model->setBrand(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"brand"})
     */
    public function getSearchableTitle(): string
    {
        return $this->name;
    }

    /**
     * @Groups({"brand"})
     */
    public function getSearchableDetail(): string
    {
        return 'Marque de véhicule';
    }
}