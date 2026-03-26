<?php

namespace App\Entity\Client;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class ContractVehicleDemand
{
    /**
     * @ORM\ManyToMany(targetEntity=Vehicle::class)
     * @Groups({"contract", "demand", "client"})
     */
    private $assignedVehicles;

    public function __construct()
    {
        $this->assignedVehicles = new ArrayCollection();
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"contract", "demand"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="vehicleDemands")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"vehicle"})
     */
    private $contract;

    /**
     * @ORM\ManyToOne(targetEntity=Brand::class)
     * @Groups({"contract", "demand"})
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity=VehicleModel::class)
     * @Groups({"contract", "demand"})
     */
    private $vehicleModel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"contract", "demand"})
     */
    private $quantity;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getVehicleModel(): ?VehicleModel
    {
        return $this->vehicleModel;
    }

    public function setVehicleModel(?VehicleModel $vehicleModel): self
    {
        $this->vehicleModel = $vehicleModel;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Collection|Vehicle[]
     */
    public function getAssignedVehicles(): Collection
    {
        return $this->assignedVehicles;
    }

    public function addAssignedVehicle(Vehicle $vehicle): self
    {
        if (!$this->assignedVehicles->contains($vehicle)) {
            $this->assignedVehicles[] = $vehicle;
        }

        return $this;
    }

    public function removeAssignedVehicle(Vehicle $vehicle): self
    {
        $this->assignedVehicles->removeElement($vehicle);

        return $this;
    }
}