<?php

namespace App\Entity\Admin;

use App\Entity\Admin\User;
use App\Repository\Admin\ShopperRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopperRepository::class)
 */
class Shopper
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="shopper", cascade={"persist", "remove"})
     */
    private $usered;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getUsered(): ?User
    {
        return $this->usered;
    }

    public function setUsered(?User $usered): self
    {
        // unset the owning side of the relation if necessary
        if ($usered === null && $this->usered !== null) {
            $this->usered->setShopper(null);
        }

        // set the owning side of the relation if necessary
        if ($usered !== null && $usered->getShopper() !== $this) {
            $usered->setShopper($this);
        }

        $this->usered = $usered;

        return $this;
    }
}
