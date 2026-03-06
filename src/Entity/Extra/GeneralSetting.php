<?php

namespace App\Entity\Extra;

use App\Repository\Extra\GeneralSettingRepository;
use App\Traits\SearchableTrait;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use App\Annotation\Searchable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=GeneralSettingRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class GeneralSetting
{
    use SearchableTrait;
    use SoftDeleteableEntity;
    use UserObjectNoCodeTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"setting"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"setting"})
     */
    private $fraisDossier;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"setting"})
     */
    private $penaliteRetardJournaliere;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"setting"})
     */
    private $delaiGracePenalite;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"setting"})
     */
    private $dureeContratDefautMois;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"setting"})
     */
    private $apportInitialPourcentage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFraisDossier(): ?int
    {
        return $this->fraisDossier;
    }

    public function setFraisDossier(?int $fraisDossier): self
    {
        $this->fraisDossier = $fraisDossier;

        return $this;
    }

    public function getPenaliteRetardJournaliere(): ?float
    {
        return $this->penaliteRetardJournaliere;
    }

    public function setPenaliteRetardJournaliere(?float $penaliteRetardJournaliere): self
    {
        $this->penaliteRetardJournaliere = $penaliteRetardJournaliere;

        return $this;
    }

    public function getDelaiGracePenalite(): ?int
    {
        return $this->delaiGracePenalite;
    }

    public function setDelaiGracePenalite(?int $delaiGracePenalite): self
    {
        $this->delaiGracePenalite = $delaiGracePenalite;

        return $this;
    }

    public function getDureeContratDefautMois(): ?int
    {
        return $this->dureeContratDefautMois;
    }

    public function setDureeContratDefautMois(?int $dureeContratDefautMois): self
    {
        $this->dureeContratDefautMois = $dureeContratDefautMois;

        return $this;
    }

    public function getApportInitialPourcentage(): ?float
    {
        return $this->apportInitialPourcentage;
    }

    public function setApportInitialPourcentage(?float $apportInitialPourcentage): self
    {
        $this->apportInitialPourcentage = $apportInitialPourcentage;

        return $this;
    }

    /**
     * @Groups({"setting"})
     */
    function getSearchableTitle(): string
    {
        return "Paramètres Généraux";
    }

    /**
     * @Groups({"setting"})
     */
    function getSearchableDetail(): string
    {
        return "Configuration globale des frais de dossier, pénalités et contrats";
    }
}