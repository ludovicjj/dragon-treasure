<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\UserRepository;
use App\State\UserProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['Default', 'create']],
            processor: UserProcessor::class
        ),
        new Put(
            security: 'is_granted("ROLE_USER_EDIT")',
        ),
        new Patch(
            security: 'is_granted("ROLE_USER_EDIT")',
        ),
        new Delete()
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    security: 'is_granted("ROLE_USER")'
)]
#[ApiResource(
    uriTemplate: '/treasures/{treasure_id}/owner.{_format}',
    operations: [new Get()],
    uriVariables: [
        'treasure_id' => new Link(
            fromProperty: 'owner',
            fromClass: DragonTreasure::class
        )
    ],
    normalizationContext: ['groups' => ['user:read']],
    security: 'is_granted("ROLE_USER")'
)]
#[UniqueEntity(fields: ['email'], message: 'this email is already used')]
#[UniqueEntity(fields: ['username'], message: 'this username is already used')]
#[ApiFilter(PropertyFilter::class, arguments: ['whitelist' => [
    'email',
    'username',
    'dragonTreasures',
    'dragonTreasures' => ['name'],
]])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['user:read', 'user:write', 'treasure:item:get', 'treasure:write'])]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: DragonTreasure::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Valid]
    private Collection $dragonTreasures;

    #[Groups(['user:write'])]
    #[SerializedName('password')]
    #[Assert\NotBlank(groups: ['create'])]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'ownedBy', targetEntity: ApiToken::class)]
    private Collection $apiTokens;

    /* Scopes given during API authentication */
    private ?array $accessTokenScopes = null;

    public function __construct()
    {
        $this->dragonTreasures = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        if ($this->accessTokenScopes === null) {
            $roles = $this->roles;
            // for users logged in via form login
            // this role give all possible scopes that a token could have
            // check role_hierarchy into security.yaml
            $roles[] = 'ROLE_FULL_USER';
        } else {
            $roles = $this->accessTokenScopes;
        }

        // guarantee every user at least has ROLE_FULL_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, DragonTreasure>
     */
    public function getDragonTreasures(): Collection
    {
        return $this->dragonTreasures;
    }

    public function addDragonTreasure(DragonTreasure $dragonTreasure): self
    {
        if (!$this->dragonTreasures->contains($dragonTreasure)) {
            $this->dragonTreasures->add($dragonTreasure);
            $dragonTreasure->setOwner($this);
        }

        return $this;
    }

    public function removeDragonTreasure(DragonTreasure $dragonTreasure): self
    {
        if ($this->dragonTreasures->removeElement($dragonTreasure)) {
            // set the owning side to null (unless already changed)
            if ($dragonTreasure->getOwner() === $this) {
                $dragonTreasure->setOwner(null);
            }
        }

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setOwnedBy($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getOwnedBy() === $this) {
                $apiToken->setOwnedBy(null);
            }
        }

        return $this;
    }

    public function getValidTokenStrings(): array
    {
        return $this->getApiTokens()
            ->filter(fn (ApiToken $token) => $token->isValid())
            ->map(fn (ApiToken $token) => $token->getToken())
            ->toArray();
    }

    public function markAsTokenAuthenticated(array $scopes)
    {
        $this->accessTokenScopes = $scopes;
    }
}
