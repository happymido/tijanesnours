<?php

namespace App\Pedagogy\Domain\Entity;

use App\IdentityAccess\Domain\Entity\Student;
use App\IdentityAccess\Domain\Entity\Teacher;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'bulletins')]
class Bulletin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Teacher $teacher = null;

    #[ORM\Column(length: 50)]
    private string $term = 'Trimestre 1';

    #[ORM\Column(length: 20, options: ['default' => '2026-2027'])]
    private string $schoolYear = '2026-2027';

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2, options: ['default' => '0.00'])]
    private string $generalAverage = '0.00';

    #[ORM\Column(type: 'decimal', precision: 3, scale: 2, options: ['default' => '5.00'])]
    private string $averageStars = '5.00';

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ratings = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $teacherRemarks = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $teacherAppreciation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column]
    private \DateTimeImmutable $generatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

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

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setTerm(string $term): self
    {
        $this->term = $term;
        return $this;
    }

    public function getSchoolYear(): string
    {
        return $this->schoolYear;
    }

    public function setSchoolYear(string $schoolYear): self
    {
        $this->schoolYear = $schoolYear;
        return $this;
    }

    public function getGeneralAverage(): string
    {
        return $this->generalAverage;
    }

    public function setGeneralAverage(string $generalAverage): self
    {
        $this->generalAverage = $generalAverage;
        return $this;
    }

    public function getAverageStars(): string
    {
        return $this->averageStars;
    }

    public function setAverageStars(string $averageStars): self
    {
        $this->averageStars = $averageStars;
        return $this;
    }

    public function getRatings(): ?array
    {
        return $this->ratings;
    }

    public function setRatings(?array $ratings): self
    {
        $this->ratings = $ratings;
        return $this;
    }

    public function getTeacherRemarks(): ?string
    {
        return $this->teacherRemarks ?: $this->teacherAppreciation;
    }

    public function setTeacherRemarks(?string $teacherRemarks): self
    {
        $this->teacherRemarks = $teacherRemarks;
        $this->teacherAppreciation = $teacherRemarks;
        return $this;
    }

    public function getTeacherAppreciation(): ?string
    {
        return $this->teacherAppreciation ?: $this->teacherRemarks;
    }

    public function setTeacherAppreciation(?string $teacherAppreciation): self
    {
        $this->teacherAppreciation = $teacherAppreciation;
        $this->teacherRemarks = $teacherAppreciation;
        return $this;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $pdfPath): self
    {
        $this->pdfPath = $pdfPath;
        return $this;
    }

    public function getGeneratedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
