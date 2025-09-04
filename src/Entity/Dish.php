<?php

namespace App\Entity;

use App\Repository\DishRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DishRepository::class)]
class Dish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dishes')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Menu $menu = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ingredients = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'dishes')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $chef = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private string $price;

    #[ORM\OneToMany(mappedBy: 'dish', targetEntity: FavoriteDish::class)]
    private Collection $favoriteDishes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->favoriteDishes = new ArrayCollection();
    }

    public function getChef(): ?User
    {
        return $this->chef;
    }

    public function setChef(?User $chef): static
    {
        $this->chef = $chef;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }
    public function setIngredients(?string $ingredients): static
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    /**
     * @return Collection<int, FavoriteDish>
     */
    public function getFavoriteDishes(): Collection
    {
        return $this->favoriteDishes;
    }

    public function addFavoriteDish(FavoriteDish $favoriteDish): static
    {
        if (!$this->favoriteDishes->contains($favoriteDish)) {
            $this->favoriteDishes->add($favoriteDish);
            $favoriteDish->setDish($this);
        }

        return $this;
    }

    public function removeFavoriteDish(FavoriteDish $favoriteDish): static
    {
        if ($this->favoriteDishes->removeElement($favoriteDish)) {
            if ($favoriteDish->getDish() === $this) {
                $favoriteDish->setDish(null);
            }
        }

        return $this;
    }

}
