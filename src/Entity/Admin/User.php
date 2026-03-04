<?php

namespace App\Entity\Admin;

use App\Entity\Admin\Admin;
use App\Entity\Admin\Agency;
use App\Entity\Admin\Shop;
use App\Entity\Admin\Shopper;
use App\Entity\Extra\RefreshToken;
use App\Entity\Extra\Role;
use App\Traits\EntityTrait;
use App\Traits\FolderTrait;
use App\Traits\PhotoTrait;
use App\Traits\SearchableTrait;
use App\Annotation\Searchable;
use App\Traits\UserObjectNoCodeTrait;
use App\Utils\Constants;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
    use PhotoTrait;
    use FolderTrait;
    use EntityTrait;
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;

    const TYPE = [
        'ADMIN' => 'ADMIN',
        'USER' => 'USER',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user","service","ticket", "treasury", "chat","house"})
     */
    private $civilite;

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
     * @Groups({"user","customer","contract"})
     */
    private $telephone;

     /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user","owner","customer","contract", "treasury","prospect","house"})
     */
    private $type = User::TYPE['USER'];

     /**
     * @ORM\Column(type="boolean")
     * @Groups({"user"})
     */
    private $isFirst = false;

    /**
     * @ORM\Column(type="string", length=255)
     * @Searchable()
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

       /**
     * @ORM\Column(type="boolean")
     */
    private $isLocked = false;

     /**
     * @ORM\Column(type="boolean")
     */
    private $isEnabled = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"user"})
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"user"})
     */
    private $isOnline = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="users")
     */
    private $droits;

  

    /**
     * @ORM\ManyToOne(targetEntity=Agency::class, inversedBy="users")
     */
    private $agency;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="users")
     */
    private $shop;

    /**
     * @ORM\OneToOne(targetEntity=Shopper::class, inversedBy="usered", cascade={"persist", "remove"})
     */
    private $shopper;

    /**
     * @ORM\OneToOne(targetEntity=Admin::class, inversedBy="users", cascade={"persist", "remove"})
     */
    private $admin;

    /**
     * @ORM\OneToMany(targetEntity=Service::class, mappedBy="responsable")
     */
    private $services;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="users")
     */
    private $service;

    public function __construct()
    {
        $this->droits = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsFirst(): ?bool
    {
        return $this->isFirst;
    }

    public function setIsFirst(bool $isFirst): self
    {
        $this->isFirst = $isFirst;

        return $this;
    }

        public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getIsOnline(): ?bool
    {
        return $this->isOnline;
    }   

    public function setIsOnline(?bool $isOnline): self
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }



    public function getLibelle(): ?string
    {
        $nom = "";
        return $nom;
    }

      /**
     * @Groups({"user"})
     */
    public function getSexe(): ?string
    {
        $sexe = "Masculin";
        if ($this->civilite && $this->admin !== null) {
            $sexe = $this->civilite === 'Mr' ? 'Masculin' : 'Féminin';
        } elseif ($this->civilite && $this->agency !== null) {
            $sexe = $this->civilite === 'Mr' ? 'Masculin' : 'Féminin';
        } 

        return $sexe;
    }

       /**
     * @Groups({"user","house"})
     */
    public function getContact(): ?string
    {
        $contact = "";
        if ($this->admin !== null) {
            $contact = $this->admin->getTelephone();
        } elseif ($this->agency !== null) {
            $contact = $this->telephone;
        } elseif ($this->shop !== null) {
            $contact = $this->shop->getContact();
        }
        // elseif ($this->owner !== null) {
        //     $contact = $this->owner->getTelephone();
        // } elseif ($this->customer !== null) {
        //     $contact = $this->customer->getTelephone();
        // }
        return $contact;
    }

    public function getRoles()
    {
        $roles = [];
        if ($this->admin !== null) {
            $roles[] = Constants::USER_ROLES['ADMIN'];
        } 
        elseif ($this->agency !== null) {
            $roles[] = Constants::USER_ROLES['AGENCY'];
        } 
        elseif ($this->shop !== null) {
            $roles[] = Constants::USER_ROLES['MARCHAND'];
        } 
   
        return $roles;
    }

    /**
     * @Groups({"user"})
     */
    public function getPermissions()
    {
        $droits = $this->getDroits() ?: [];
        $path = [];
        foreach ($droits as $droit) {
            $roles = $droit->getPaths();
            foreach ($roles as $role) {
                $path[] = $role->getPermission();
            }
        }
        return $path;
    }

    public function getSalt()
    {
        return null;
    }

    public function isLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return Collection<int, Role>
     */
    public function getDroits(): Collection
    {
        return $this->droits;
    }

    public function addDroit(Role $droit): self
    {
        if (!$this->droits->contains($droit)) {
            $this->droits[] = $droit;
        }

        return $this;
    }

    public function removeDroit(Role $droit): self
    {
        $this->droits->removeElement($droit);

        return $this;
    }

     /**
     * @Groups({"user"})
     */
    public function getSearchableTitle(): string
    {

       return $this->getLibelle();
    }

     /**
     * @Groups({"user"})
     */
    public function getSearchableDetail(): string
    {
        return 'E-mail: ' . $this->username;
    }

    public function getFolderPath(): string
    {
        $nom = "";
        return $nom;
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

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getShopper(): ?Shopper
    {
        return $this->shopper;
    }

    public function setShopper(?Shopper $shopper): self
    {
        $this->shopper = $shopper;

        return $this;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function generateRefreshToken()
    {
        $refreshToken = new RefreshToken();
        $now = new \DateTime();
        $expireAt = new \DateTime();
        $expireAt->modify('+3600 seconds');
        $refreshToken->setCreateBy($this);
        $refreshToken->setExpireAt($expireAt);
        $refreshToken->setCreatedAt($now);
        

        return $refreshToken;
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
            $service->setResponsable($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getResponsable() === $this) {
                $service->setResponsable(null);
            }
        }

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
