<?php

namespace App\IdentityAccess\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'parents')]
class ParentUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 50)]
    private ?string $phone = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $secondaryPhone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 11, nullable: true)]
    private ?string $bic = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $sepaMandateRef = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $sepaMandateSignatureDate = null;

    #[ORM\Column(length: 50, options: ['default' => 'STRIPE'])]
    private string $preferredPaymentMethod = 'STRIPE';

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Student::class, cascade: ['persist'])]
    private Collection $students;

    public function __construct()
    {
        $this->students = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getSecondaryPhone(): ?string
    {
        return $this->secondaryPhone;
    }

    public function setSecondaryPhone(?string $secondaryPhone): self
    {
        $this->secondaryPhone = $secondaryPhone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;
        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): self
    {
        $this->bic = $bic;
        return $this;
    }

    public function getSepaMandateRef(): ?string
    {
        return $this->sepaMandateRef;
    }

    public function setSepaMandateRef(?string $sepaMandateRef): self
    {
        $this->sepaMandateRef = $sepaMandateRef;
        return $this;
    }

    public function getSepaMandateSignatureDate(): ?\DateTimeImmutable
    {
        return $this->sepaMandateSignatureDate;
    }

    public function setSepaMandateSignatureDate(?\DateTimeImmutable $sepaMandateSignatureDate): self
    {
        $this->sepaMandateSignatureDate = $sepaMandateSignatureDate;
        return $this;
    }

    public function getPreferredPaymentMethod(): string
    {
        return $this->preferredPaymentMethod;
    }

    public function setPreferredPaymentMethod(string $preferredPaymentMethod): self
    {
        $this->preferredPaymentMethod = $preferredPaymentMethod;
        return $this;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->setParent($this);
        }
        return $this;
    }
}
