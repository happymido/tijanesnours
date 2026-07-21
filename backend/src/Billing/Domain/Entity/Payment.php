<?php

namespace App\Billing\Domain\Entity;

use App\IdentityAccess\Domain\Entity\User;
use App\Schooling\Domain\Entity\Enrollment;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
#[ORM\Index(columns: ['user_id', 'status'], name: 'idx_payments_user_status')]
#[ORM\Index(columns: ['stripe_payment_intent_id'], name: 'idx_payments_stripe')]
#[ORM\Index(columns: ['created_at'], name: 'idx_payments_created')]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PaymentSchedule::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PaymentSchedule $schedule = null;

    #[ORM\ManyToOne(targetEntity: Enrollment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Enrollment $enrollment = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'agent_user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $agentUser = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount = '0.00';

    #[ORM\Column(length: 3, options: ['default' => 'EUR'])]
    private string $currency = 'EUR';

    #[ORM\Column(length: 50, options: ['default' => 'PENDING'])]
    private string $status = 'PENDING';

    #[ORM\Column(length: 50)]
    private string $paymentMethod = 'STRIPE';

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $transactionReference = null;

    #[ORM\Column(length: 35, nullable: true)]
    private ?string $structuredReferenceUsed = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $receiptNumber = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validatedAt = null;

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

    public function getSchedule(): ?PaymentSchedule
    {
        return $this->schedule;
    }

    public function setSchedule(?PaymentSchedule $schedule): self
    {
        $this->schedule = $schedule;
        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAgentUser(): ?User
    {
        return $this->agentUser;
    }

    public function setAgentUser(?User $agentUser): self
    {
        $this->agentUser = $agentUser;
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

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
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

    public function getTransactionReference(): ?string
    {
        return $this->transactionReference;
    }

    public function setTransactionReference(?string $transactionReference): self
    {
        $this->transactionReference = $transactionReference;
        return $this;
    }

    public function getStructuredReferenceUsed(): ?string
    {
        return $this->structuredReferenceUsed;
    }

    public function setStructuredReferenceUsed(?string $structuredReferenceUsed): self
    {
        $this->structuredReferenceUsed = $structuredReferenceUsed;
        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): self
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
        return $this;
    }

    public function getReceiptNumber(): ?string
    {
        return $this->receiptNumber;
    }

    public function setReceiptNumber(?string $receiptNumber): self
    {
        $this->receiptNumber = $receiptNumber;
        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): self
    {
        $this->validatedAt = $validatedAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
