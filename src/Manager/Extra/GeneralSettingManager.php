<?php

namespace App\Manager\Extra;

use App\Entity\Extra\GeneralSetting;
use App\Repository\Extra\GeneralSettingRepository;
use Doctrine\ORM\EntityManagerInterface;

class GeneralSettingManager
{
    private $em;
    private $repository;

    public function __construct(
        EntityManagerInterface $em,
        GeneralSettingRepository $repository
        )
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * Either create or update the global general settings
     */
    public function updateOrCreate(object $data): GeneralSetting
    {
        $settings = $this->repository->findAll();
        $setting = count($settings) > 0 ? $settings[0] : new GeneralSetting();

        if (isset($data->fraisDossier)) {
            $setting->setFraisDossier((int)$data->fraisDossier);
        }

        if (isset($data->penaliteRetardJournaliere)) {
            $setting->setPenaliteRetardJournaliere((float)$data->penaliteRetardJournaliere);
        }

        if (isset($data->delaiGracePenalite)) {
            $setting->setDelaiGracePenalite((int)$data->delaiGracePenalite);
        }

        if (isset($data->dureeContratDefautMois)) {
            $setting->setDureeContratDefautMois((int)$data->dureeContratDefautMois);
        }

        if (isset($data->apportInitialPourcentage)) {
            $setting->setApportInitialPourcentage((float)$data->apportInitialPourcentage);
        }

        $this->em->persist($setting);
        $this->em->flush();

        return $setting;
    }

    public function getSettings(): GeneralSetting
    {
        $settings = $this->repository->findAll();
        if (count($settings) > 0) {
            return $settings[0];
        }

        return new GeneralSetting();
    }
}