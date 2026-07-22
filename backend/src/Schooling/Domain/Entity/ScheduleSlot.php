<?php

namespace App\Schooling\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'schedule_slots')]
class ScheduleSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $day = 'Samedi';

    #[ORM\Column(type: 'string', length: 10)]
    private string $startTime = '09:00';

    #[ORM\Column(type: 'string', length: 10)]
    private string $endTime = '12:00';

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $label = 'Matin';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): string
    {
        return $this->day;
    }

    public function setDay(string $day): self
    {
        $this->day = $day;
        return $this;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getName(): string
    {
        return sprintf('%s %s - %s (%s)', $this->day, $this->startTime, $this->endTime, $this->label ?? 'Standard');
    }
}
