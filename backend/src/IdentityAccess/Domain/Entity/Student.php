<?php

namespace App\IdentityAccess\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'students')]
#[ORM\Index(columns: ['parent_id'], name: 'idx_students_parent')]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: ParentUser::class, inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ParentUser $parent = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $assignedGroup = 'Classe Débutant 2A (6-8 ans)';

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $dateOfBirth = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $placeOfBirth = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nationality = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $healthIssues = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $familyNotes = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $imageRightsGranted = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $gdprConsent = true;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $insurancePolicyNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $insuranceDocPath = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParent(): ?ParentUser
    {
        return $this->parent;
    }

    public function setParent(?ParentUser $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getAssignedGroup(): ?string
    {
        return $this->assignedGroup;
    }

    public function setAssignedGroup(?string $assignedGroup): self
    {
        $this->assignedGroup = $assignedGroup;
        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeImmutable $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getPlaceOfBirth(): ?string
    {
        return $this->placeOfBirth;
    }

    public function setPlaceOfBirth(?string $placeOfBirth): self
    {
        $this->placeOfBirth = $placeOfBirth;
        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;
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

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): self
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getHealthIssues(): ?string
    {
        return $this->healthIssues;
    }

    public function setHealthIssues(?string $healthIssues): self
    {
        $this->healthIssues = $healthIssues;
        return $this;
    }

    public function getFamilyNotes(): ?string
    {
        return $this->familyNotes;
    }

    public function setFamilyNotes(?string $familyNotes): self
    {
        $this->familyNotes = $familyNotes;
        return $this;
    }

    public function isImageRightsGranted(): bool
    {
        return $this->imageRightsGranted;
    }

    public function setImageRightsGranted(bool $imageRightsGranted): self
    {
        $this->imageRightsGranted = $imageRightsGranted;
        return $this;
    }

    public function isGdprConsent(): bool
    {
        return $this->gdprConsent;
    }

    public function setGdprConsent(bool $gdprConsent): self
    {
        $this->gdprConsent = $gdprConsent;
        return $this;
    }

    public function getInsurancePolicyNumber(): ?string
    {
        return $this->insurancePolicyNumber;
    }

    public function setInsurancePolicyNumber(?string $insurancePolicyNumber): self
    {
        $this->insurancePolicyNumber = $insurancePolicyNumber;
        return $this;
    }

    public function getInsuranceDocPath(): ?string
    {
        return $this->insuranceDocPath;
    }

    public function setInsuranceDocPath(?string $insuranceDocPath): self
    {
        $this->insuranceDocPath = $insuranceDocPath;
        return $this;
    }
}
