<?php

namespace App\Schooling\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_levels')]
class CourseLevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150)]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $targetAgeMin = 4;

    #[ORM\Column(type: 'integer')]
    private int $targetAgeMax = 16;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $translations = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getTargetAgeMin(): int
    {
        return $this->targetAgeMin;
    }

    public function setTargetAgeMin(int $min): self
    {
        $this->targetAgeMin = $min;
        return $this;
    }

    public function getTargetAgeMax(): int
    {
        return $this->targetAgeMax;
    }

    public function setTargetAgeMax(int $max): self
    {
        $this->targetAgeMax = $max;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTranslations(): array
    {
        return $this->translations ?? [];
    }

    public function setTranslations(?array $translations): self
    {
        $this->translations = $translations;
        return $this;
    }

    public function getNameForLocale(string $locale = 'fr'): string
    {
        if (!empty($this->translations[$locale]['name'])) {
            return $this->translations[$locale]['name'];
        }
        if (!empty($this->translations['fr']['name'])) {
            return $this->translations['fr']['name'];
        }
        return $this->name;
    }

    public function getDescriptionForLocale(string $locale = 'fr'): ?string
    {
        if (!empty($this->translations[$locale]['description'])) {
            return $this->translations[$locale]['description'];
        }
        if (!empty($this->translations['fr']['description'])) {
            return $this->translations['fr']['description'];
        }
        return $this->description;
    }
}
