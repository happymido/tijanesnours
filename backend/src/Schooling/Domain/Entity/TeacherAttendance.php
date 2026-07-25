<?php

namespace App\Schooling\Domain\Entity;

use App\IdentityAccess\Domain\Entity\Teacher;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'teacher_attendances')]
class TeacherAttendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne(targetEntity: SchoolClass::class)]
    #[ORM\JoinColumn(name: 'school_class_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?SchoolClass $schoolClass = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $sessionDate;

    #[ORM\Column(type: 'string', length: 30)]
    private string $status = 'PRESENT';

    #[ORM\Column(type: 'integer')]
    private int $presentStudentsCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $totalStudentsCount = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->sessionDate = new \DateTime();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSchoolClass(): ?SchoolClass
    {
        return $this->schoolClass;
    }

    public function setSchoolClass(?SchoolClass $schoolClass): self
    {
        $this->schoolClass = $schoolClass;
        return $this;
    }

    public function getSessionDate(): \DateTimeInterface
    {
        return $this->sessionDate;
    }

    public function setSessionDate(\DateTimeInterface $sessionDate): self
    {
        $this->sessionDate = $sessionDate;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPresentStudentsCount(): int
    {
        return $this->presentStudentsCount;
    }

    public function setPresentStudentsCount(int $presentStudentsCount): self
    {
        $this->presentStudentsCount = $presentStudentsCount;
        return $this;
    }

    public function getTotalStudentsCount(): int
    {
        return $this->totalStudentsCount;
    }

    public function setTotalStudentsCount(int $totalStudentsCount): self
    {
        $this->totalStudentsCount = $totalStudentsCount;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
