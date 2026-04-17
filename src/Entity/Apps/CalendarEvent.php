<?php

namespace App\Entity\Apps;

use App\Repository\Apps\CalendarEventRepository;
use App\Traits\UserObjectNoCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CalendarEventRepository::class)
 */
class CalendarEvent
{
    use UserObjectNoCodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"calendar"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"calendar"})
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"calendar"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"calendar"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"calendar"})
     */
    private $endDate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"calendar"})
     */
    private $allDay = false;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"calendar"})
     */
    private $backgroundColor;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"calendar"})
     */
    private $borderColor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getAllDay(): ?bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(?string $borderColor): self
    {
        $this->borderColor = $borderColor;

        return $this;
    }
}