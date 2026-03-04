<?php

namespace App\Traits;

use App\Entity\Admin\User;
use Symfony\Component\Serializer\Annotation\Groups;

trait  PublicationTrait
{
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"prospect","offre"})
     */
    private $isPublie = 'NON';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"prospect"})
     */
    private $publieAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $publieBy;

    public function getPublieAt(): ?\DateTimeInterface
    {
        return $this->publieAt;
    }

    public function setPublieAt(?\DateTimeInterface $publieAt): self
    {
        $this->publieAt = $publieAt;

        return $this;
    }

    public function getIsPublie(): ?string
    {
        return $this->isPublie;
    }

    public function setIsPublie(?string $isPublie): self
    {
        $this->isPublie = $isPublie;
        
        return $this;
    }

    public function getPublieBy(): ?User
    {
        return $this->publieBy;
    }

    public function setPublieBy(?User $publieBy): self
    {
        $this->publieBy = $publieBy;
        
        return $this;
    }

    /**
     * @Groups({"prospect"})
     */
    public function getPublie(): ?string
    {
        return $this->publieBy ? $this->getPublieBy()->getLibelle() : null;
    }


}
