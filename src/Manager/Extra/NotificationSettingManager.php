<?php

namespace App\Manager\Extra;

use App\Entity\Extra\NotificationSetting;
use App\Exception\ExceptionApi;
use App\Repository\Extra\NotificationSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class NotificationSettingManager
{
    private $em;
    private $repository;
    private $security;

    public function __construct(
        EntityManagerInterface $em,
        NotificationSettingRepository $repository,
        Security $security
        )
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->security = $security;
    }

    /**
     * Either create or update the global notification settings
     */
    public function updateOrCreate(object $data): NotificationSetting
    {
        // On suppose qu'il n'y a qu'une seule configuration pour tout le système mono-agence
        $settings = $this->repository->findAll();
        $setting = count($settings) > 0 ? $settings[0] : new NotificationSetting();

        $setting->setAutoSendSms(isset($data->autoSendSms) ? $data->autoSendSms : false);
        $setting->setAutoSendEmail(isset($data->autoSendEmail) ? $data->autoSendEmail : false);
        $setting->setAutoSendWhatsapp(isset($data->autoSendWhatsapp) ? $data->autoSendWhatsapp : false);
        $setting->setEnablePushNotifications(isset($data->enablePushNotifications) ? $data->enablePushNotifications : false);

        $setting->setSmsTemplateWelcome(isset($data->smsTemplateWelcome) ? $data->smsTemplateWelcome : null);
        $setting->setSmsTemplateLatePayment(isset($data->smsTemplateLatePayment) ? $data->smsTemplateLatePayment : null);
        $setting->setSmsTemplateMaintenance(isset($data->smsTemplateMaintenance) ? $data->smsTemplateMaintenance : null);

        $this->em->persist($setting);
        $this->em->flush();

        return $setting;
    }

    public function getSettings(): NotificationSetting
    {
        $settings = $this->repository->findAll();
        if (count($settings) > 0) {
            return $settings[0];
        }

        // Return empty instance if null to avoid 404s on frontend fetch
        return new NotificationSetting();
    }
}