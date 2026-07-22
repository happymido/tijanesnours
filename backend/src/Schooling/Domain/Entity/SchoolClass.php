<?php

namespace App\Schooling\Domain\Entity;

use App\IdentityAccess\Domain\Entity\Teacher;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'school_classes')]
class SchoolClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $roomNumber = 'Salle Maryam 1';

    #[ORM\Column(type: 'integer')]
    private int $maxCapacity = 15;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $schedule = 'Samedi 09:00 - 12:00';

    #[ORM\ManyToOne(targetEntity: CourseCategory::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?CourseCategory $category = null;

    #[ORM\ManyToOne(targetEntity: CourseLevel::class)]
    #[ORM\JoinColumn(name: 'level_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?CourseLevel $level = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne(targetEntity: ScheduleSlot::class)]
    #[ORM\JoinColumn(name: 'schedule_slot_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?ScheduleSlot $scheduleSlot = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRoomNumber(): ?string
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(?string $roomNumber): self
    {
        $this->roomNumber = $roomNumber;
        return $this;
    }

    public function getMaxCapacity(): int
    {
        return $this->maxCapacity;
    }

    public function setMaxCapacity(int $maxCapacity): self
    {
        $this->maxCapacity = $maxCapacity;
        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(?string $schedule): self
    {
        $this->schedule = $schedule;
        return $this;
    }

    public function getCategory(): ?CourseCategory
    {
        return $this->category;
    }

    public function setCategory(?CourseCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getLevel(): ?CourseLevel
    {
        return $this->level;
    }

    public function setLevel(?CourseLevel $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;
        return $this;
    }
}
