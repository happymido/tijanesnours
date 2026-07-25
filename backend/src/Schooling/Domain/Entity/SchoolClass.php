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

    #[ORM\Column(type: 'integer')]
    private int $maxCapacity = 15;

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

    #[ORM\ManyToOne(targetEntity: Classroom::class)]
    #[ORM\JoinColumn(name: 'classroom_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Classroom $classroom = null;

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
        return $this->classroom ? $this->classroom->getName() : 'Salle Principale';
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
        return $this->scheduleSlot ? $this->scheduleSlot->getName() : 'Non défini';
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

    public function getScheduleSlot(): ?ScheduleSlot
    {
        return $this->scheduleSlot;
    }

    public function setScheduleSlot(?ScheduleSlot $scheduleSlot): self
    {
        $this->scheduleSlot = $scheduleSlot;
        return $this;
    }

    public function getClassroom(): ?Classroom
    {
        return $this->classroom;
    }

    public function setClassroom(?Classroom $classroom): self
    {
        $this->classroom = $classroom;
        return $this;
    }
}
