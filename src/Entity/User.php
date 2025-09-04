<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone_number = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?ChefProfile $chefProfile = null;

    #[ORM\OneToMany(mappedBy: 'chef', targetEntity: Menu::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $menus;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Reservation::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'chef', targetEntity: ChefSchedule::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $chefSchedules;


    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Review::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reviews;
    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;

    #[ORM\OneToMany(mappedBy: 'chef', targetEntity: Dish::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $dishes;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $phoneOtp = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $otpExpiresAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SupportMessage::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $supportMessages;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: FavoriteDish::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $favoriteDishes;


    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->chefSchedules = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->dishes = new ArrayCollection();
        $this->supportMessages = new ArrayCollection();
        $this->favoriteDishes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getChefProfile(): ?ChefProfile
    {
        return $this->chefProfile;
    }

    public function setChefProfile(ChefProfile $chefProfile): static
    {
        if ($chefProfile->getUser() !== $this) {
            $chefProfile->setUser($this);
        }

        $this->chefProfile = $chefProfile;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setChef($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            if ($menu->getChef() === $this) {
                $menu->setChef(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setClient($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChefSchedule>
     */
    public function getChefSchedules(): Collection
    {
        return $this->chefSchedules;
    }

    public function addChefSchedule(ChefSchedule $chefSchedule): static
    {
        if (!$this->chefSchedules->contains($chefSchedule)) {
            $this->chefSchedules->add($chefSchedule);
            $chefSchedule->setChef($this);
        }

        return $this;
    }

    public function removeChefSchedule(ChefSchedule $chefSchedule): static
    {
        if ($this->chefSchedules->removeElement($chefSchedule)) {
            if ($chefSchedule->getChef() === $this) {
                $chefSchedule->setChef(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setClient($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getClient() === $this) {
                $review->setClient(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role)];
    }

    public function eraseCredentials(): void
    {

    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $resetTokenExpiresAt): static
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;

        return $this;
    }

    /**
     * @return Collection<int, Dish>
     */
    public function getDishes(): Collection
    {
        return $this->dishes;
    }

    public function addDish(Dish $dish): static
    {
        if (!$this->dishes->contains($dish)) {
            $this->dishes->add($dish);
            $dish->setChef($this);
        }

        return $this;
    }

    public function removeDish(Dish $dish): static
    {
        if ($this->dishes->removeElement($dish)) {
            if ($dish->getChef() === $this) {
                $dish->setChef(null);
            }
        }

        return $this;
    }

    public function getPhoneOtp(): ?string
    {
        return $this->phoneOtp;
    }

    public function setPhoneOtp(?string $phoneOtp): self
    {
        $this->phoneOtp = $phoneOtp;

        return $this;
    }

    public function getOtpExpiresAt(): ?\DateTimeInterface
    {
        return $this->otpExpiresAt;
    }

    public function setOtpExpiresAt(?\DateTimeInterface $otpExpiresAt): self
    {
        $this->otpExpiresAt = $otpExpiresAt;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return Collection<int, SupportMessage>
     */
    public function getSupportMessages(): Collection
    {
        return $this->supportMessages;
    }

    public function addSupportMessage(SupportMessage $supportMessage): static
    {
        if (!$this->supportMessages->contains($supportMessage)) {
            $this->supportMessages->add($supportMessage);
            $supportMessage->setUser($this);
        }

        return $this;
    }

    public function removeSupportMessage(SupportMessage $supportMessage): static
    {
        if ($this->supportMessages->removeElement($supportMessage)) {
            if ($supportMessage->getUser() === $this) {
                $supportMessage->setUser(null);
            }
        }

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
            $favoriteDish->setUser($this);
        }

        return $this;
    }

    public function removeFavoriteDish(FavoriteDish $favoriteDish): static
    {
        if ($this->favoriteDishes->removeElement($favoriteDish)) {
            if ($favoriteDish->getUser() === $this) {
                $favoriteDish->setUser(null);
            }
        }

        return $this;
    }


}
