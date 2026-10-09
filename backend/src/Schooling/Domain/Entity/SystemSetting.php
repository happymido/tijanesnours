<?php

namespace App\Schooling\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'system_settings')]
class SystemSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'setting_key', length: 100, unique: true)]
    private string $settingKey;

    #[ORM\Column(name: 'setting_value', type: 'text')]
    private string $settingValue;

    #[ORM\Column(name: 'setting_label', length: 255, nullable: true)]
    private ?string $settingLabel = null;

    #[ORM\Column(name: 'setting_group', length: 50, options: ['default' => 'GENERAL'])]
    private string $settingGroup = 'GENERAL';

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSettingKey(): string
    {
        return $this->settingKey;
    }

    public function setSettingKey(string $settingKey): self
    {
        $this->settingKey = $settingKey;
        return $this;
    }

    public function getSettingValue(): string
    {
        return $this->settingValue;
    }

    public function setSettingValue(string $settingValue): self
    {
        $this->settingValue = $settingValue;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getSettingLabel(): ?string
    {
        return $this->settingLabel;
    }

    public function setSettingLabel(?string $settingLabel): self
    {
        $this->settingLabel = $settingLabel;
        return $this;
    }

    public function getSettingGroup(): string
    {
        return $this->settingGroup;
    }

    public function setSettingGroup(string $settingGroup): self
    {
        $this->settingGroup = $settingGroup;
        return $this;
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
