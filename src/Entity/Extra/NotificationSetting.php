<?php

namespace App\Entity\Extra;

use App\Repository\Extra\NotificationSettingRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use App\Annotation\Searchable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NotificationSettingRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class NotificationSetting
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
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"setting"})
     */
    private $autoSendSms = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"setting"})
     */
    private $autoSendEmail = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"setting"})
     */
    private $autoSendWhatsapp = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"setting"})
     */
    private $enablePushNotifications = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"setting"})
     * @Searchable()
     */
    private $smsTemplateWelcome;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"setting"})
     * @Searchable()
     */
    private $smsTemplateLatePayment;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"setting"})
     * @Searchable()
     */
    private $smsTemplateMaintenance;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAutoSendSms(): ?bool
    {
        return $this->autoSendSms;
    }

    public function setAutoSendSms(?bool $autoSendSms): self
    {
        $this->autoSendSms = $autoSendSms;
        return $this;
    }

    public function getAutoSendEmail(): ?bool
    {
        return $this->autoSendEmail;
    }

    public function setAutoSendEmail(?bool $autoSendEmail): self
    {
        $this->autoSendEmail = $autoSendEmail;
        return $this;
    }

    public function getAutoSendWhatsapp(): ?bool
    {
        return $this->autoSendWhatsapp;
    }

    public function setAutoSendWhatsapp(?bool $autoSendWhatsapp): self
    {
        $this->autoSendWhatsapp = $autoSendWhatsapp;
        return $this;
    }

    public function getEnablePushNotifications(): ?bool
    {
        return $this->enablePushNotifications;
    }

    public function setEnablePushNotifications(?bool $enablePushNotifications): self
    {
        $this->enablePushNotifications = $enablePushNotifications;
        return $this;
    }

    public function getSmsTemplateWelcome(): ?string
    {
        return $this->smsTemplateWelcome;
    }

    public function setSmsTemplateWelcome(?string $smsTemplateWelcome): self
    {
        $this->smsTemplateWelcome = $smsTemplateWelcome;
        return $this;
    }

    public function getSmsTemplateLatePayment(): ?string
    {
        return $this->smsTemplateLatePayment;
    }

    public function setSmsTemplateLatePayment(?string $smsTemplateLatePayment): self
    {
        $this->smsTemplateLatePayment = $smsTemplateLatePayment;
        return $this;
    }

    public function getSmsTemplateMaintenance(): ?string
    {
        return $this->smsTemplateMaintenance;
    }

    public function setSmsTemplateMaintenance(?string $smsTemplateMaintenance): self
    {
        $this->smsTemplateMaintenance = $smsTemplateMaintenance;
        return $this;
    }

    /**
     * @Groups({"setting"})
     */
    function getTitle(): string
    {
        return "Configuration des Notifications";
    }

    /**
     * @Groups({"setting"})
     */
    function getDetail(): string
    {
        return "Configuration globale des SMS, Emails et Modèles";
    }
}