<?php

namespace App\Entity\Admin;

use App\Entity\Admin\User;
use App\Entity\Extra\Role;
use App\Repository\Admin\AgencyRepository;
use App\Traits\EntityTrait;
use App\Traits\FolderTrait;
use App\Traits\PhotoTrait;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectTrait;
use App\Traits\UserObjectValidateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=AgencyRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Agency
{

    use PhotoTrait;
    use FolderTrait;
    use EntityTrait;
    use UserObjectTrait;
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectValidateTrait;

    const ETAT = [
        'ACTIF' => 'ACTIF',
        'INACTIF' => 'INACTIF',
        'SUSPENDU' => 'SUSPENDU'
    ];
    const STATUS = [
        'ACTIF' => 'ACTIF',
        'EXPIRE' => 'EXPIRE'
    ];
    const CREE = [
        'WEB' => 'WEB',
        'ADMIN' => 'ADMIN'
    ];
    const VENTE = [
        'OUI' => 'OUI',
        'NON' => 'NON'
    ];

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
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="agency")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Service::class, mappedBy="agency")
     */
    private $services;

    /**
     * @ORM\OneToMany(targetEntity=Role::class, mappedBy="agency")
     */
    private $roles;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setAgency($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAgency() === $this) {
                $user->setAgency(null);
            }
        }

        return $this;
    }

    public function getFolderPath(): string
    {
        $nom = "";
        return $nom;
    }

    public function getSearchableTitle(): string
    {

        $nom = "";
        return $nom;
    }

    public function getSearchableDetail(): string
    {
        $nom = "";

        return $nom;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setAgency($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getAgency() === $this) {
                $service->setAgency(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setAgency($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getAgency() === $this) {
                $role->setAgency(null);
            }
        }

        return $this;
    }

  
}
