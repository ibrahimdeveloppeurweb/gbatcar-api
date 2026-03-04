<?php

namespace App\Traits;

use App\Entity\Admin\User;
use Ramsey\Uuid\UuidInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait  UserObjectTrait
{
    /**
     * @var UuidInterface
     * @ORM\Column(type="uuid", length=255, unique=true)
     * @Groups({"syndic","city","rdv","activity","agency", "house", "terminate", "equipment", "contract", "inventory", "country", "admin", "user", "customer", "mutate","repayment",
     *     "tenant", "owner", "invoice", "rental", "subdivision", "islet", "lot", "lot:c", "mandate", "service", "folderC", "penality", "product",
     *     "rent", "contract", "provider", "construction","payment","renew","renewMandate","promotion","home","homeType","invoiceF","paymentC",
     *     "treasury","notice","supply","day","fund","spent","quote","invoiceFu","production","paymentF","package","facture", "subscription", "funding",
     *     "terminateF", "OptionF", "OptionR", "invoiceR", "paymentR", "bill", "path","ticket","chat","category", "signed-file", "terminateM", "transaction", 
     *     "mailSms", "disburs", "worksite", "work-package" , "task", "building", "option-worksite", "task", "task-link", "recommandation", "short-contract", "extend-contract","prospect","agent","offre",
     *     "tva", "account", "log", "logNature", "planModel", "accountingSetting", "defaultAccount", "trustee", "default", "house_co", "home_co", "optionS", "operation", "invoiceP", "funds-apeal", "houseco-lot", "homeco-lot","loadT",
     *      "reservation", "folderR"})
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"agency","rdv","activity", "house", "terminate", "equipment", "contract", "inventory", "country", "admin", "user", "customer", "mutate","repayment",
     *     "tenant", "owner", "invoice", "rental", "subdivision", "islet", "lot", "mandate", "service", "folderC", "penality", "product",
     *     "rent", "contract", "provider", "construction", "payment", "renew", "renewMandate","promotion","home","homeType","invoiceF","paymentC",
     *     "treasury", "notice", "supply", "day", "fund", "spent", "quote", "invoiceFu", "production", "paymentF", "package", "facture", "subscription",
     *     "funding","terminateF", "OptionF", "OptionR", "invoiceR", "paymentR", "bill", "path", "signed-file", "terminateM", "transaction", "mailSms", "disburs", 
     *     "worksite", "work-package" , "task", "building", "option-worksite", "task", "task-link", "recommandation", "short-contract", "extend-contract","prospect","agent","offre",
     *     "tva", "account", "log", "logNature", "planModel", "accountingSetting", "defaultAccount", "trustee", "default", "house_co", "home_co", "optionS", "operation", "invoiceP", "funds-apeal", "houseco-lot", "homeco-lot","loadT",
     *      "reservation", "folderR"})
     */
    private $code;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"agency","rdv","activity", "house", "terminate", "equipment", "contract", "inventory", "country", "admin", "user", "customer", "mutate","repayment",
     *     "tenant", "owner", "invoice", "rental", "subdivision", "islet", "lot", "mandate", "service", "folderC", "penality", "product",
     *     "rent", "contract", "provider", "construction", "payment", "renew", "renewMandate", "promotion", "home","homeType", "invoiceF", "paymentC",
     *     "treasury", "notice", "supply", "day", "fund", "spent", "quote", "invoiceFu", "production", "paymentF", "package", "facture", "subscription",
     *     "funding","terminateF", "OptionF", "OptionR", "invoiceR", "paymentR", "bill", "path", "ticket", "category", "terminateM", "transaction", "mailSms", 
     *     "disburs", "worksite", "work-package" , "task" , "building", "option-worksite", "task", "task-link", "recommandation", "short-contract", "extend-contract","prospect","agent","offre",
     *     "tva", "account", "log", "logNature", "planModel", "accountingSetting", "defaultAccount", "trustee", "default", "house_co", "home_co", "optionS", "operation", "invoiceP", "funds-apeal", "houseco-lot", "homeco-lot","loadT",
     *      "reservation", "folderR"})
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"agency","rdv", "house", "terminate", "equipment", "contract", "inventory", "country", "admin", "user", "customer", "mutate","repayment",
     *     "tenant", "owner", "invoice", "rental", "subdivision", "islet", "lot", "mandate", "service", "folderC", "penality", "product",
     *     "rent", "contract", "provider", "construction", "payment", "renew", "renewMandate", "promotion", "home","homeType", "invoiceF", "paymentC",
     *     "treasury", "notice", "supply", "day", "fund", "spent", "quote", "invoiceFu", "production", "paymentF", "package", "facture", "subscription",
     *     "funding","terminateF", "OptionF", "OptionR", "invoiceR", "paymentR", "bill", "path", "terminateM", "transaction", "mailSms", "disburs", "worksite", 
     *     "work-package" , "task", "building", "option-worksite", "task", "task-link", "recommandation", "short-contract", "extend-contract","prospect","agent","offre",
     *     "tva", "account", "log", "logNature", "planModel", "accountingSetting", "defaultAccount", "trustee", "default", "house_co", "home_co", "optionS", "operation", "invoiceP", "funds-apeal", "houseco-lot", "homeco-lot","loadT",
     *      "reservation", "folderR"})
     * @Gedmo\Timestampable(on="update")
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
        return $this->uuid ? (is_string($this->uuid) ? $this->uuid : $this->uuid->toString()) : null;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode(?string $code):self
    {
        $this->code = $code;

        return $this;
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
     * @Groups({"agency","rdv","activity", "house", "terminate", "equipment", "contract", "inventory", "country", "admin", "user", "customer", "mutate","repayment",
     *     "tenant", "owner", "invoice", "rental", "subdivision", "islet", "lot", "mandate", "service", "folderC", "penality", "product",
     *     "rent", "contract", "provider", "construction","payment","renewMandate","promotion","home","homeType", "invoiceF", "paymentC",
     *     "treasury", "notice", "supply", "day", "fund", "spent", "quote", "invoiceFu", "production", "paymentF", "package", "facture", 
     *     "subscription", "funding", "terminateF", "OptionF", "OptionR", "invoiceR", "paymentR", "bill", "path", "signed-file", "terminateM", 
     *     "transaction", "mailSms", "disburs", "worksite", "building", "recommandation", "short-contract", "extend-contract","agent","offre", "comment", "reservation", "folderR"})
     */
    public function getCreate(): ?string
    {
        return $this->createBy ? $this->getCreateBy()->getLibelle() : null;
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
     * @Groups({"agency","rdv","activity", "house", "terminate", "equipment", "contract", "inventory", "country", "admin", "user", "customer", "mutate","repayment",
     *     "tenant", "owner", "invoice", "rental", "subdivision", "islet", "lot", "mandate", "service", "folderC", "penality", "product",
     *     "rent", "contract", "provider", "construction", "payment", "promotion", "home","homeType", "invoiceF", "paymentC", "treasury", "notice",
     *     "supply", "day", "fund", "spent", "quote", "invoiceFu", "production", "paymentF", "package", "facture", "subscription", "funding",
     *     "terminateF", "OptionF", "OptionR", "invoiceR", "paymentR", "bill", "path", "signed-file", "terminateM", "transaction", "mailSms", "disburs", "worksite", 
     *     "building", "recommandation", "short-contract", "extend-contract","agent","offre", "comment", "reservation", "folderR"})
     */
    public function getUpdate(): ?string
    {
        return $this->updateBy ? $this->getUpdateBy()->getLibelle() : null;
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
