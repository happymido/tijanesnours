<?php

namespace App\Billing\Domain\Entity;

use App\Schooling\Domain\Entity\Enrollment;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_schedules')]
#[ORM\Index(columns: ['enrollment_id', 'status', 'due_date'], name: 'idx_schedules_enrollment_status')]
class PaymentSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Enrollment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Enrollment $enrollment = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount = '0.00';

    #[ORM\Column(length: 50, options: ['default' => 'PENDING'])]
    private string $status = 'PENDING';

    #[ORM\Column(length: 50)]
    private string $paymentMethod = 'STRIPE';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnrollment(): ?Enrollment
    {
        return $this->enrollment;
    }

    public function setEnrollment(?Enrollment $enrollment): self
    {
        $this->enrollment = $enrollment;
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

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
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

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }
}
