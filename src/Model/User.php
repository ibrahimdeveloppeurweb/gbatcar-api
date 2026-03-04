<?php

namespace App\Model;

use App\Entity\Extra\Signature;

class User
{
    /**
     * @var string|null
     */
    private $nom;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $civilite;

    /**
     * @var string|null
     */
    private $sexe;

    /**
     * @var string|null
     */
    private $photo;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string|null
     */
    private $uuid;

    /**
     * @var string
     */
    private $role;

    /**
     * @var string|null
     */
    private $agencyKey;

    /**
     * @var string|null
     */
    private $prospectKey;

    /**
     * @var string|null
     */
    private $agencyName;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var string|null
     */
    private $device;

    /**
     * @var string|null
     */
    private $telephone;

    /**
     * @var float
     */
    private $prcFraisOrange;

    /**
     * @var float
     */
    private $prcFraisMtn;

    /**
     * @var float
     */
    private $prcFraisMoov;

    /**
     * @var float
     */
    private $prcFraisWave;

    /**
     * @var float
     */
    private $prcFraisDebitcard;

    /**
     * @var boolean
     */
    private $isFirstUser = false;

    /**
     * @var boolean
     */
    private $isSubscribe = false;

    /**
     * @var dateTime|null
     */
    private $lastLogin = false;

    /**
     * @var null|[]
     */
    private $autorisation = [];

    /**
     * @var null|[]
     */
    private $permissions = [];

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var string|null
     */
    private $signature;

    public function __construct()
    {
        $this->nom;
        $this->sexe;
        $this->civilite;
        $this->photo;
        $this->token;
        $this->uuid;
        $this->role;
        $this->country;
        $this->telephone;
        $this->prcFraisOrange;
        $this->prcFraisMtn;
        $this->prcFraisMoov;
        $this->prcFraisWave;
        $this->prcFraisDebitcard;
        $this->email;
        $this->device;
        $this->agencyKey;
        $this->prospectKey;
        $this->agencyName;
        $this->lastLogin;
        $this->isFirstUser;
        $this->isSubscribe;
        $this->autorisation;
        $this->permissions;
        $this->path;
        $this->signature;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
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

    public function getSexe()
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): self
    {
        $this->sexe = $sexe;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function setDevice(?string $device): self
    {
        $this->device = $device;
        return $this;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getPrcFraisOrange()
    {
        return $this->prcFraisOrange;
    }

    public function setPrcFraisOrange(?float $prcFraisOrange): self
    {
        $this->prcFraisOrange = $prcFraisOrange;
        return $this;
    }

    public function getPrcFraisMtn()
    {
        return $this->prcFraisMtn;
    }

    public function setPrcFraisMtn(?float $prcFraisMtn): self
    {
        $this->prcFraisMtn = $prcFraisMtn;
        return $this;
    }

    public function getPrcFraisMoov()
    {
        return $this->prcFraisMoov;
    }

    public function setPrcFraisMoov(?float $prcFraisMoov): self
    {
        $this->prcFraisMoov = $prcFraisMoov;
        return $this;
    }

    public function getPrcFraisWave()
    {
        return $this->prcFraisWave;
    }

    public function setPrcFraisWave(?float $prcFraisWave): self
    {
        $this->prcFraisWave = $prcFraisWave;
        return $this;
    }

    public function getPrcFraisDebitcard()
    {
        return $this->prcFraisDebitcard;
    }

    public function setPrcFraisDebitcard(?float $prcFraisDebitcard): self
    {
        $this->prcFraisDebitcard = $prcFraisDebitcard;
        return $this;
    }

    public function getAgencyKey()
    {
        return $this->agencyKey;
    }

    public function setAgencyKey(?string $agencyKey): self
    {
        $this->agencyKey = $agencyKey;
        return $this;
    }

    public function getProspectKey()
    {
        return $this->prospectKey;
    }

    public function setProspectKey(?string $prospectKey): self
    {
        $this->prospectKey = $prospectKey;
        return $this;
    }

    public function getAgencyName()
    {
        return $this->agencyName;
    }

    public function setAgencyName(?string $agencyName): self
    {
        $this->agencyName = $agencyName;
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

    public function getIsFirstUser()
    {
        return $this->isFirstUser;
    }

    public function setIsFirstUser(bool $isFirstUser): self
    {
        $this->isFirstUser = $isFirstUser;
        return $this;
    }

    public function getIsSubscribe()
    {
        return $this->isSubscribe;
    }

    public function setIsSubscribe(bool $isSubscribe): self
    {
        $this->isSubscribe = $isSubscribe;
        return $this;
    }

    public function getAutorisation()
    {
        return $this->autorisation;
    }

    public function setAutorisation(?array $autorisation): self
    {
        $this->autorisation = $autorisation;
        return $this;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions(?array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;
        return $this;
    }

    public function getData()
    {
        return [
            'nom' => $this->nom,
            'civilite' => $this->civilite,
            'sexe' => $this->sexe,
            'photo' => $this->photo,
            'token' => $this->token,
            'uuid' => $this->uuid,
            'agencyKey' => $this->agencyKey,
            'prospectKey' => $this->prospectKey,
            'agencyName' => $this->agencyName,
            'country' => $this->country,
            'device' => $this->device,
            'telephone' => $this->telephone,
            'prcFraisOrange' => $this->prcFraisOrange,
            'prcFraisMtn' => $this->prcFraisMtn,
            'prcFraisMoov' => $this->prcFraisMoov,
            'prcFraisWave' => $this->prcFraisWave,
            'prcFraisDebitcard' => $this->prcFraisDebitcard,
            'email' => $this->email,
            'lastLogin' => $this->lastLogin,
            'isFirstUser' => $this->isFirstUser,
            'isSubscribe' => $this->isSubscribe,
            'role' => $this->role,
            'autorisation' => $this->autorisation,
            'permissions' => $this->permissions,
            'path' => $this->path,
            'signature' => $this->signature
        ];
    }
}
