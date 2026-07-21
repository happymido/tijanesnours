<?php

namespace App\Pedagogy\Domain\Entity;

use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\Teacher;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'grades')]
#[ORM\Index(columns: ['student_id', 'exam_date'], name: 'idx_grades_student_date')]
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Teacher $teacher = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $score = '0.00';

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => '20.00'])]
    private string $maxScore = '20.00';

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1, options: ['default' => '1.0'])]
    private string $coefficient = '1.0';

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $examDate = null;

    #[ORM\Column(length: 30, options: ['default' => 'TRIMESTER_1'])]
    private string $term = 'TRIMESTER_1';

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

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;
        return $this;
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

    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): self
    {
        $this->score = $score;
        return $this;
    }

    public function getMaxScore(): string
    {
        return $this->maxScore;
    }

    public function setMaxScore(string $maxScore): self
    {
        $this->maxScore = $maxScore;
        return $this;
    }

    public function getCoefficient(): string
    {
        return $this->coefficient;
    }

    public function setCoefficient(string $coefficient): self
    {
        $this->coefficient = $coefficient;
        return $this;
    }

    public function getExamDate(): ?\DateTimeImmutable
    {
        return $this->examDate;
    }

    public function setExamDate(\DateTimeImmutable $examDate): self
    {
        $this->examDate = $examDate;
        return $this;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setTerm(string $term): self
    {
        $this->term = $term;
        return $this;
    }
}
