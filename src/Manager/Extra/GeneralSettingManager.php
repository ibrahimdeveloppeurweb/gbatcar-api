<?php

namespace App\Manager\Extra;

use App\Entity\Extra\GeneralSetting;
use App\Entity\Extra\GeneralSettingHistory;
use App\Repository\Extra\GeneralSettingRepository;
use App\Repository\Extra\GeneralSettingHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class GeneralSettingManager
{
    private $em;
    private $repository;
    private $historyRepository;

    public function __construct(
        EntityManagerInterface $em,
        GeneralSettingRepository $repository,
        GeneralSettingHistoryRepository $historyRepository
        )
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->historyRepository = $historyRepository;
    }

    /**
     * Either create or update the global general settings
     */
    public function updateOrCreate(object $data): GeneralSetting
    {
        $settings = $this->repository->findAll();
        $setting = count($settings) > 0 ? $settings[0] : new GeneralSetting();

        // Capture previous values for history
        $previousValues = [
            'fraisDossier' => $setting->getFraisDossier(),
            'penaliteRetardJournaliere' => $setting->getPenaliteRetardJournaliere(),
            'delaiGracePenalite' => $setting->getDelaiGracePenalite(),
            'dureeContratDefautMois' => $setting->getDureeContratDefautMois(),
            'apportInitialPourcentage' => $setting->getApportInitialPourcentage(),
        ];

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

        // Record History
        $history = new GeneralSettingHistory();
        $history->setDescription($data->reason ?? 'Mise à jour des paramètres');
        $history->setPreviousValues($previousValues);
        $history->setNewValues([
            'fraisDossier' => $setting->getFraisDossier(),
            'penaliteRetardJournaliere' => $setting->getPenaliteRetardJournaliere(),
            'delaiGracePenalite' => $setting->getDelaiGracePenalite(),
            'dureeContratDefautMois' => $setting->getDureeContratDefautMois(),
            'apportInitialPourcentage' => $setting->getApportInitialPourcentage(),
        ]);

        $this->em->persist($history);
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

    public function getHistory(): array
    {
        return $this->historyRepository->findBy([], ['createdAt' => 'DESC']);
    }
}