<?php

namespace App\Billing\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Payment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payment $payment = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $issueDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $totalExclTax = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $taxAmount = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $totalInclTax = '0.00';

    #[ORM\Column(length: 35, nullable: true)]
    private ?string $structuredBankReference = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;
        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getIssueDate(): ?\DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function setIssueDate(\DateTimeImmutable $issueDate): self
    {
        $this->issueDate = $issueDate;
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

    public function getTotalExclTax(): string
    {
        return $this->totalExclTax;
    }

    public function setTotalExclTax(string $totalExclTax): self
    {
        $this->totalExclTax = $totalExclTax;
        return $this;
    }

    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): self
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getTotalInclTax(): string
    {
        return $this->totalInclTax;
    }

    public function setTotalInclTax(string $totalInclTax): self
    {
        $this->totalInclTax = $totalInclTax;
        return $this;
    }

    public function getStructuredBankReference(): ?string
    {
        return $this->structuredBankReference;
    }

    public function setStructuredBankReference(?string $structuredBankReference): self
    {
        $this->structuredBankReference = $structuredBankReference;
        return $this;
    }
}
