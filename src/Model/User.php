<?php

namespace App\Model;

/**
 * Modèle de données retourné au client après une authentification réussie.
 * Ce modèle est construit par SecurityManager::getAccessToken()
 * et représente les informations de session de l'utilisateur GbatCar.
 */
class User
{
    /** @var string|null Prénom de l'utilisateur */
    private $firstname;

    /** @var string|null Nom de famille de l'utilisateur */
    private $nom;

    /** @var string|null Email de l'utilisateur */
    private $email;

    /** @var string|null Civilité (M., Mme.) */
    private $civilite;

    /** @var string|null URL de la photo de profil */
    private $photo;

    /** @var string|null JWT Token d'accès */
    private $token;

    /** @var string|null Token de rafraîchissement */
    private $refreshToken;

    /** @var string|null UUID de l'utilisateur */
    private $uuid;

    /** @var string Rôle Symfony de l'utilisateur (ROLE_ADMIN, ROLE_MANAGER, ROLE_CLIENT) */
    private $role;

    /** @var string|null Numéro de téléphone */
    private $telephone;

    /** @var string|null Nom d'utilisateur (username = email) */
    private $username;

    /** @var bool Première connexion de l'utilisateur */
    private $isFirstUser = false;

    /** @var \DateTimeInterface|null Date de la dernière connexion */
    private $lastLogin;

    /** @var array Liste des permissions (paths/droits) */
    private $permissions = [];

    // -----------------------------------------------------------------
    // Getters & Setters
    // -----------------------------------------------------------------

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setName(?string $nom): self
    {
        $this->nom = $nom;
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

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): self
    {
        $this->civilite = $civilite;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getIsFirstUser(): bool
    {
        return $this->isFirstUser;
    }

    public function setIsFirstUser(bool $isFirstUser): self
    {
        $this->isFirstUser = $isFirstUser;
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

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(?array $permissions): self
    {
        $this->permissions = $permissions ?? [];
        return $this;
    }

    /**
     * Retourne toutes les données à envoyer au client en réponse à la connexion.
     * Ce tableau est utilisé dans SecurityManager::getAccessToken().
     */
    public function getData(): array
    {
        return [
            'uuid' => $this->uuid,
            'nom' => $this->nom,
            'firstname' => $this->firstname,
            'civilite' => $this->civilite,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'username' => $this->username,
            'photo' => $this->photo,
            'role' => $this->role,
            'token' => $this->token,
            'isFirstUser' => $this->isFirstUser,
            'lastLogin' => $this->lastLogin,
            'permissions' => $this->permissions,
        ];
    }

    /**
     * Alias pour la compatibilité avec l'appel setContact() dans SecurityManager
     */
    public function setContact(?string $contact): self
    {
        $this->telephone = $contact;
        return $this;
    }

    /**
     * Alias pour setUuid (utilisé dans SecurityManager avec setCountryId)
     * Conservé pour compatibilité, peut être supprimé après refactoring complet.
     */
    public function setCountryId(?string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * Alias pour la compatibilité avec l'appel setIsFirst() dans SecurityManager
     */
    public function setIsFirst(bool $isFirst): self
    {
        $this->isFirstUser = $isFirst;
        return $this;
    }
}