<?php

namespace App\Pedagogy\Domain\Entity;

use App\IdentityAccess\Domain\Entity\Student;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'attendances')]
#[ORM\Index(columns: ['student_id', 'date'], name: 'idx_attendances_student_date')]
class Attendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 30, options: ['default' => 'PRESENT'])]
    private string $status = 'PRESENT';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $justification = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificationDocPath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
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

    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(?string $justification): self
    {
        $this->justification = $justification;
        return $this;
    }

    public function getJustificationDocPath(): ?string
    {
        return $this->justificationDocPath;
    }

    public function setJustificationDocPath(?string $justificationDocPath): self
    {
        $this->justificationDocPath = $justificationDocPath;
        return $this;
    }
}
