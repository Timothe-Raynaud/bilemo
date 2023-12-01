<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Groups as SymfonyGroups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "user",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 *
 * @Hateoas\Relation(
 *       "delete",
 *       href = @Hateoas\Route(
 *           "deleteUser",
 *           parameters = { "id" = "expr(object.getId())" },
 *       ),
 *       exclusion = @Hateoas\Exclusion(groups="getUsers", excludeIf = "expr(not is_granted('IS_AUTHENTICATED_FULLY'))"),
 * )
 *
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    #[Assert\NotBlank(message: "Le username est obligatoire")]
    #[Assert\Length(min: 5, max: 255,
        minMessage: "Le username doit faire au moins {{ limit }} caractères",
        maxMessage: "Le username ne peut pas faire plus de {{ limit }} caractères"
    )]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.',)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    #[Assert\NotBlank(message: "Le password est obligatoire")]
    #[Assert\Length(min: 5, max: 255,
        minMessage: "Le password doit faire au moins {{ limit }} caractères",
        maxMessage: "Le password ne peut pas faire plus de {{ limit }} caractères"
    )]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    private ?string $cellphone = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    private ?string $zipcode = null;

    #[ORM\Column]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    #[Assert\NotBlank(message: "La valeur is_registered est obligatoire")]
    private ?bool $is_registered = null;

    #[ORM\Column]
    #[Groups(["getUsers"])]
    #[SymfonyGroups(["addUser"])]
    #[Assert\NotBlank(message: "Un role est obligatoire")]
    private array $role = [];

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le client est obligatoire")]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

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

    public function getCellphone(): ?string
    {
        return $this->cellphone;
    }

    public function setCellphone(?string $cellphone): static
    {
        $this->cellphone = $cellphone;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function isIsRegistered(): ?bool
    {
        return $this->is_registered;
    }

    public function setIsRegistered(bool $is_registered): static
    {
        $this->is_registered = $is_registered;

        return $this;
    }

    public function getRole(): array
    {
        return $this->role;
    }

    public function setRole(array $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
