<?php

namespace App\Entity\Extra;

use Ramsey\Uuid\UuidInterface;
use App\Repository\Extra\RefreshTokenRepository;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass=RefreshTokenRepository::class)
 */
class RefreshToken
{
    use UserObjectNoCodeTrait;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="uuid", length=255)
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @var UuidInterface
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expireAt;

  

    public function getExpireAt(): ?\DateTimeInterface
    {
        return $this->expireAt;
    }

    public function setExpireAt(\DateTimeInterface $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }

     /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
