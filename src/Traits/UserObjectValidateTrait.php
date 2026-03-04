<?php

namespace App\Traits;

use App\Entity\Admin\User;
use Symfony\Component\Serializer\Annotation\Groups;

trait  UserObjectValidateTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"agency", "contract", "mandate", "payment", "inventory", "renew", "renewM", "terminate", "repayment",
     *  "mutate", "paymentC", "fund", "paymentB", "subscription", "paymentR", "spent", "funding", "paymentF", "terminateM"})
     */
    private $validateAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $validateBy;

    public function getValidateAt(): ?\DateTimeInterface
    {
        return $this->validateAt;
    }

    public function setValidateAt(?\DateTimeInterface $validateAt): self
    {
        $this->validateAt = $validateAt;

        return $this;
    }

    public function getValidateBy(): ?User
    {
        return $this->validateBy;
    }

    public function setValidateBy(?User $validateBy): self
    {
        $this->validateBy = $validateBy;
        
        return $this;
    }

    /**
     * @Groups({"agency", "contract", "mandate", "payment", "inventory", "renew", "renewM", "terminate", "repayment",
     *  "mutate", "paymentC", "fund", "paymentB", "subscription", "paymentR", "spent", "funding", "paymentF", "terminateM"})
     */
    public function getValidate(): ?string
    {
        return $this->validateBy ? $this->getValidateBy()->getLibelle() : null;
    }
}
