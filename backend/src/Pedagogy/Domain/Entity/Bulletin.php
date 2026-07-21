<?php

namespace App\Pedagogy\Domain\Entity;

use App\IdentityAccess\Domain\Entity\Student;
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

    #[ORM\Column(length: 30)]
    private string $term = 'TRIMESTER_1';

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2)]
    private string $generalAverage = '0.00';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $teacherAppreciation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column]
    private \DateTimeImmutable $generatedAt;

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
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

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setTerm(string $term): self
    {
        $this->term = $term;
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

    public function getTeacherAppreciation(): ?string
    {
        return $this->teacherAppreciation;
    }

    public function setTeacherAppreciation(?string $teacherAppreciation): self
    {
        $this->teacherAppreciation = $teacherAppreciation;
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
}
