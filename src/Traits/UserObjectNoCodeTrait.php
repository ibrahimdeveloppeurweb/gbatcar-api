<?php

namespace App\Traits;

use App\Entity\Admin\User;
use Ramsey\Uuid\UuidInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait  UserObjectNoCodeTrait
{
    /**
     * @var UuidInterface
     * @ORM\Column(type="uuid", length=255, unique=true)
     * @Groups({"city","agency","house", "role", "contract", "inventory", "country", "admin", "user", "customer", "tenant", "owner", "invoice", "rental", "subdivision", 
     * "islet", "lot", "mandate", "service", "folderC", "penality", "rent", "contract", "provider", "package", "construction", "homeType", "home", "promotion", "setting" ,"etape","tunnel",
     * "sms", "mail", "template","attibuate","agent","prospect", "subscription","optionF","funding","repayment", "path","advance", "paymentC", "ticket","chat", "terminate", "terminateM", "treasury", "file-signe", "spent", "equipment",
     * "tva", "account", "log", "logNature", "planModel", "accountingSetting", "defaultAccount", "trustee", "default", "house_co", "home_co", "optionS", "operation", "invoiceP", "funds-apeal", "houseco-lot", "homeco-lot","loadT",
     * "reservation", "folderR"})
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @Groups({"house", "contract", "role", "inventory", "country", "admin", "user", "customer", "tenant", "owner", "invoice", "rental", "subdivision", "islet",
     *  "lot", "mandate", "service", "folderC", "penality", "rent", "contract", "provider", "construction", "homeType", "home", "setting","sms", "mail", "template","etape","tunnel",
     *  "subscription","optionF","funding","attibuate","agent","prospect", "repayment", "path","advance", "paymentC", "ticket","chat", "treasury", "file-signe", "spent", "equipment",
     *  "tva", "account", "log", "logNature", "planModel", "accountingSetting", "defaultAccount", "trustee", "default", "house_co", "home_co", "optionS", "operation", "invoiceP", "funds-apeal", "houseco-lot", "homeco-lot","loadT",
     * "reservation", "folderR"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @Groups({"house", "contract", "inventory", "country", "admin", "user", "customer", "tenant", "owner", "invoice", "rental", "subdivision", "islet", "lot",
     *  "mandate", "service", "folderC", "penality", "rent", "contract", "provider", "construction", "homeType", "home", "setting", "sms", "mail", "template","etape","tunnel",
     *  "subscription","optionF","funding","attibuate","agent","prospect", "repayment", "path","advance", "paymentC", "ticket","chat", "treasury", "file-signe", "spent", "equipment",
     *  "tva", "account", "log", "logNature", "planModel", "accountingSetting", "defaultAccount", "trustee", "default", "house_co", "home_co", "optionS", "operation", "invoiceP", "funds-apeal", "houseco-lot", "homeco-lot","loadT",
     * "reservation", "folderR"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $createBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $updateBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Admin\User")
     */
    private $removeBy;

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUuid()
    {
        return $this->uuid ? $this->uuid->toString() : null;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreateBy(): ?User
    {
        return $this->createBy;
    }

    /**
     * @Groups({"house", "contract", "inventory", "country", "admin", "user", "customer", "tenant", "owner", "invoice", "rental", "subdivision", "islet", "path", "etape","tunnel",
     * "lot", "mandate", "service","attibuate","agent","prospect",  "folderC", "penality", "rent", "contract", "provider", "construction", "homeType", "home","subscription","optionF","funding",
     * "repayment","advance", "treasury", "file-signe", "spent", "equipment", "reservation", "folderR"})
     */
    public function getCreate(): ?string
    {
        return $this->createBy ? $this->createBy->getLibelle() : null;
    }

    public function setCreateBy(?User $createBy): self
    {
        $this->createBy = $createBy;

        return $this;
    }

    public function getUpdateBy(): ?User
    {
        return $this->updateBy;
    }

    /**
     * @Groups({"house", "role", "contract", "inventory", "country", "admin", "user", "customer", "tenant", "owner", "invoice", "rental", "subdivision", "islet", "path", "etape","tunnel",
     * "lot", "mandate", "service", "folderC","attibuate","agent","prospect", "penality", "rent", "contract", "provider", "construction", "homeType", "home","subscription","optionF","funding",
     * "repayment","advance", "treasury", "file-signe", "spent", "equipment", "reservation", "folderR"})
     */
    public function getUpdate(): ?string
    {
        return $this->updateBy ? $this->updateBy->getLibelle() : null;
    }

    public function setUpdateBy(?User $updateBy): self
    {
        $this->updateBy = $updateBy;

        return $this;
    }

    public function getRemoveBy(): ?User
    {
        return $this->removeBy;
    }

    public function setRemoveBy(?User $removeBy): self
    {
        $this->removeBy = $removeBy;

        return $this;
    }
}
