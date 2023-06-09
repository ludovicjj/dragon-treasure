<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\DragonTreasureRepository;
use App\Validator\IsValidOwner;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DragonTreasureRepository::class)]
#[ApiResource(
    shortName: 'Treasure',
    description: 'A rare and valuable treasure.',
    operations: [
        new Get(
            normalizationContext: [
                'groups' => ['treasure:read', 'treasure:item:get']
            ]
        ),
        new GetCollection(),
        new Post(
            security: 'is_granted("ROLE_TREASURE_CREATE")'
        ),
        new Patch(
            security: 'is_granted("EDIT", object)'
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")'
        )
    ],
    formats: [
        'jsonld',
        'json',
        'html',
        'jsonhal',
        'csv' => ['text/csv']
    ],
    normalizationContext: [
        'groups' => ['treasure:read']
    ],
    denormalizationContext: [
        'groups' => ['treasure:write']
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 10,
    paginationMaximumItemsPerPage: 30,
    extraProperties: [
        'standard_put' => true
    ]
)]
#[ApiResource(
    uriTemplate: '/users/{user_id}/treasures.{_format}',
    shortName: 'Treasure',
    operations: [new GetCollection()],
    uriVariables: [
        'user_id' => new Link(
            fromProperty: 'dragonTreasures',
            fromClass: User::class
        )
    ],
    normalizationContext: [
        'groups' => ['treasure:read']
    ],
)]
#[ApiFilter(
    PropertyFilter::class,
    arguments: ['whitelist' => [
        'name',
        'description',
        'shortDescription',
        'coolFactor',
        'value',
        'plunderedAtAgo'
    ]]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'owner.username' => 'partial'
])]
class DragonTreasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['treasure:read', 'treasure:write', 'user:read', 'user:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: 'Describe your loot in 50 chars or less')]
    private ?string $name;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['treasure:read'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    /**
     * The estimated value of this treasure, in gold coins.
     * @var int
     */
    #[ORM\Column]
    #[Groups(['treasure:read', 'treasure:write', 'user:read', 'user:write'])]
    #[ApiFilter(RangeFilter::class)]
    #[Assert\GreaterThanOrEqual(0)]
    private int $value = 0;

    #[ORM\Column]
    #[Groups(['treasure:read', 'treasure:write'])]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(10)]
    private int $coolFactor = 0;

    #[ORM\Column]
    private \DateTimeImmutable $plunderedAt;

    #[ORM\Column]
    #[Groups(['admin:read', 'admin:write', 'owner:read', 'owner:write'])]
    #[ApiFilter(BooleanFilter::class)]
    private ?bool $isPublished;

    #[ORM\ManyToOne(inversedBy: 'dragonTreasures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['treasure:read', 'treasure:write'])]
    #[Assert\Valid]
    #[Assert\NotNull]
    #[IsValidOwner]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?User $owner = null;

    #[Groups(['treasure:read'])]
    private ?bool $isMine = null;

    // let args name with default value null
    // serializer need to be able to instantiate the object
    // Then validator will handle violation (property name can't be null)
    public function __construct(string $name = null)
    {
        $this->plunderedAt = new \DateTimeImmutable();
        $this->isPublished = false;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Groups(['treasure:read'])]
    public function getShortDescription(): ?string
    {
        return u($this->description)->truncate(40, '...');
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[SerializedName('description')]
    #[Groups(['treasure:write', 'user:write'])]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolFactor(): int
    {
        return $this->coolFactor;
    }

    public function setCoolFactor(int $coolFactor): self
    {
        $this->coolFactor = $coolFactor;

        return $this;
    }

    public function getPlunderedAt(): \DateTimeImmutable
    {
        return $this->plunderedAt;
    }

    public function setPlunderedAt(\DateTimeImmutable $plunderedAt): self
    {
        $this->plunderedAt = $plunderedAt;

        return $this;
    }

    /**
     * A human-readable representation of when this treasure was plundered.
     */
    #[Groups(['treasure:read'])]
    public function getPlunderedAtAgo(): string
    {
        return Carbon::instance($this->plunderedAt)->diffForHumans();
    }

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getIsMine(): ?bool
    {
        return $this->isMine;
    }

    public function setIsMine(?bool $isMine): void
    {
        $this->isMine = $isMine;
    }
}
