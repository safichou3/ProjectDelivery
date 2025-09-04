<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $tax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discountCode = null;

    #[ORM\Column(nullable: true)]
    private ?float $discountPercentage = null;

    #[ORM\Column(nullable: true)]
    private ?bool $discountActive = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(float $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getDiscountCode(): ?string
    {
        return $this->discountCode;
    }

    public function setDiscountCode(?string $discountCode): static
    {
        $this->discountCode = $discountCode;

        return $this;
    }

    public function getDiscountPercentage(): ?float
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(?float $discountPercentage): static
    {
        $this->discountPercentage = $discountPercentage;

        return $this;
    }

    public function isDiscountActive(): ?bool
    {
        return $this->discountActive;
    }

    public function setDiscountActive(?bool $discountActive): static
    {
        $this->discountActive = $discountActive;

        return $this;
    }
}
