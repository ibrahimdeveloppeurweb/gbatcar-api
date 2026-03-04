<?php

namespace App\Entity\Extra;

use App\Entity\Admin\Agency;
use App\Entity\Admin\User;
use App\Repository\Extra\RoleRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use App\Annotation\Searchable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Role
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"role","user"})
     * @Searchable()
     */
    private $nom;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdmin = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"role","user"})
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Path::class, mappedBy="roles")
     * @Groups({"role"})
     */
    private $paths;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="droits")
     */
    private $users;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isFirst;

    /**
     * @ORM\ManyToOne(targetEntity=Agency::class, inversedBy="roles")
     */
    private $agency;

    public function __construct()
    {
        $this->paths = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

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

      /**
     * @Groups({"role","user"})
     */
    function getSearchableTitle(): string
    {
        return $this->nom;
    }

    /**
     * @Groups({"role","user"})
     */
    function getSearchableDetail(): string
    {
        return $this->nom ;
    }

    /**
     * @return Collection<int, Path>
     */
    public function getPaths(): Collection
    {
        return $this->paths;
    }

    public function addPath(Path $path): self
    {
        if (!$this->paths->contains($path)) {
            $this->paths[] = $path;
            $path->addRole($this);
        }

        return $this;
    }

    public function removePath(Path $path): self
    {
        if ($this->paths->removeElement($path)) {
            $path->removeRole($this);
        }

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
            $user->addDroit($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeDroit($this);
        }

        return $this;
    }

    public function getIsFirst(): ?bool
    {
        return $this->isFirst;
    }

    public function setIsFirst(?bool $isFirst): self
    {
        $this->isFirst = $isFirst;

        return $this;
    }

    public function getAgency(): ?Agency
    {
        return $this->agency;
    }

    public function setAgency(?Agency $agency): self
    {
        $this->agency = $agency;

        return $this;
    }
}
