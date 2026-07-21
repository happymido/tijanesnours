<?php

namespace App\Schooling\Domain\Entity;

use App\IdentityAccess\Domain\Entity\Student;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'enrollments')]
#[ORM\Index(columns: ['student_id', 'status'], name: 'idx_enrollments_student_status')]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\Column(length: 50, options: ['default' => 'PENDING'])]
    private string $status = 'PENDING';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $selectedDay = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $selectedTimeSlot = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $includesBooks = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $optionalAccessories = [];

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $totalAmount = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $discountAmount = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '50.00'])]
    private string $registrationFee = '50.00';

    #[ORM\Column(length: 35, unique: true)]
    private ?string $structuredReference = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getSelectedDay(): ?string
    {
        return $this->selectedDay;
    }

    public function setSelectedDay(?string $selectedDay): self
    {
        $this->selectedDay = $selectedDay;
        return $this;
    }

    public function getSelectedTimeSlot(): ?string
    {
        return $this->selectedTimeSlot;
    }

    public function setSelectedTimeSlot(?string $selectedTimeSlot): self
    {
        $this->selectedTimeSlot = $selectedTimeSlot;
        return $this;
    }

    public function isIncludesBooks(): bool
    {
        return $this->includesBooks;
    }

    public function setIncludesBooks(bool $includesBooks): self
    {
        $this->includesBooks = $includesBooks;
        return $this;
    }

    public function getOptionalAccessories(): ?array
    {
        return $this->optionalAccessories;
    }

    public function setOptionalAccessories(?array $optionalAccessories): self
    {
        $this->optionalAccessories = $optionalAccessories;
        return $this;
    }

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getDiscountAmount(): string
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(string $discountAmount): self
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    public function getRegistrationFee(): string
    {
        return $this->registrationFee;
    }

    public function setRegistrationFee(string $registrationFee): self
    {
        $this->registrationFee = $registrationFee;
        return $this;
    }

    public function getStructuredReference(): ?string
    {
        return $this->structuredReference;
    }

    public function setStructuredReference(string $structuredReference): self
    {
        $this->structuredReference = $structuredReference;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
