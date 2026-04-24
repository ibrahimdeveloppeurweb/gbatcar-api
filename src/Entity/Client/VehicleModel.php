<?php

namespace App\Entity\Client;

use App\Repository\Client\VehicleModelRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=VehicleModelRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class VehicleModel
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"vehicle_model", "brand", "vehicle", "penalty"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"vehicle_model", "brand", "vehicle", "contract", "demand", "alert", "penalty"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Brand::class, inversedBy="models")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"vehicle_model", "vehicle", "alert"})
     */
    private $brand;

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

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @Groups({"vehicle_model"})
     */
    public function getTitle(): string
    {
        return $this->name;
    }

    /**
     * @Groups({"penalty"})
     */
    public function getLibelle(): ?string
    {
        return $this->name;
    }

    /**
     * @Groups({"vehicle_model"})
     */
    public function getDetail(): string
    {
        return $this->brand ? 'Modèle de ' . $this->brand->getName() : 'Modèle de véhicule';
    }
}