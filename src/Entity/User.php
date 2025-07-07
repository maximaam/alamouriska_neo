<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'email_exists')]
#[UniqueEntity(fields: ['pendingEmail'], message: 'email_exists')]
#[UniqueEntity(fields: ['pseudo'], message: 'pseudo_exists')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email]
    private string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [self::ROLE_USER];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 32, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Type(Types::STRING)]
    #[Assert\Regex('/^[A-Za-z0-9\-]+$/')]
    #[Assert\Length(
        min: 4,
        max: 32,
        minMessage: 'user_pseudo_short',
        maxMessage: 'user_pseudo_long',
        // groups: ['Profile', 'Registration']
    )]
    private ?string $pseudo = null;

    #[ORM\Column]
    private bool $enableCommunityContact = true;

    #[ORM\Column]
    private bool $enablePostNotification = true;

    #[ORM\Column(nullable: true)]
    private ?string $avatarName = null;

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'avatarName')]
    #[Assert\Image(
        allowLandscape: true,
        allowPortrait: true,
        maxSize: '1M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        minWidth: 128,
        minHeight: 128,
        maxWidth: 2048,
        maxHeight: 2048,
        minWidthMessage: 'avatar_min_width',
        minHeightMessage: 'avatar_min_height',
        maxWidthMessage: 'avatar_max_width',
        maxHeightMessage: 'avatar_max_height',
        maxSizeMessage: 'avatar_max_size',
        mimeTypesMessage: 'avatar_mime_types_allowed'
    )]
    private ?File $avatarFile = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $posts;

    #[ORM\Column(length: 180, nullable: true, unique: true)]
    #[Assert\Email]
    private ?string $pendingEmail = null;

    /**
     * @var Collection<int, UserLike>
     */
    #[ORM\OneToMany(targetEntity: UserLike::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userLikes;

    /**
     * @var Collection<int, UserComment>
     */
    #[ORM\OneToMany(targetEntity: UserComment::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userComments;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $googleId = null;

    /**
     * @var Collection<int, Wall>
     */
    #[ORM\OneToMany(targetEntity: Wall::class, mappedBy: 'User', orphanRemoval: true)]
    private Collection $walls;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->userLikes = new ArrayCollection();
        $this->userComments = new ArrayCollection();
        $this->walls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserIdentifier(): string
    {
        if ('' === $this->email) {
            throw new \InvalidArgumentException('Email cannot be empty');
        }

        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isEnableCommunityContact(): bool
    {
        return $this->enableCommunityContact;
    }

    public function setEnableCommunityContact(bool $enableCommunityContact): static
    {
        $this->enableCommunityContact = $enableCommunityContact;

        return $this;
    }

    public function isEnablePostNotification(): bool
    {
        return $this->enablePostNotification;
    }

    public function setEnablePostNotification(bool $enablePostNotification): static
    {
        $this->enablePostNotification = $enablePostNotification;

        return $this;
    }

    public function getAvatarName(): ?string
    {
        return $this->avatarName;
    }

    public function setAvatarName(?string $avatarName): static
    {
        $this->avatarName = $avatarName;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                // $post->setUser();
            }
        }

        return $this;
    }

    // updatedAt, requird by VichUppload to trigger doctrine
    // is automatically called TimestampableEntity
    public function setAvatarFile(?File $avatarFile = null): void
    {
        $this->avatarFile = $avatarFile;

        if (null !== $avatarFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    // Disables serialization of the uploaded file
    public function __serialize(): array
    {
        return [$this->id, $this->email, $this->password];
    }

    /**
     * @param array{int|null, string, string} $data
     */
    public function __unserialize(array $data): void
    {
        [$this->id, $this->email, $this->password] = $data;
    }

    public function getPendingEmail(): ?string
    {
        return $this->pendingEmail;
    }

    public function setPendingEmail(?string $pendingEmail): static
    {
        $this->pendingEmail = $pendingEmail;

        return $this;
    }

    /**
     * @return Collection<int, UserLike>
     */
    public function getUserLikes(): Collection
    {
        return $this->userLikes;
    }

    public function addUserLike(UserLike $userLike): static
    {
        if (!$this->userLikes->contains($userLike)) {
            $this->userLikes->add($userLike);
            $userLike->setUser($this);
        }

        return $this;
    }

    public function removeUserLike(UserLike $userLike): static
    {
        $this->userLikes->removeElement($userLike);

        return $this;
    }

    /**
     * @return Collection<int, UserComment>
     */
    public function getUserComments(): Collection
    {
        return $this->userComments;
    }

    public function addUserComment(UserComment $userComment): static
    {
        if (!$this->userComments->contains($userComment)) {
            $this->userComments->add($userComment);
            $userComment->setUser($this);
        }

        return $this;
    }

    public function removeUserComment(UserComment $userComment): static
    {
        $this->userComments->removeElement($userComment);

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * @return Collection<int, Wall>
     */
    public function getWalls(): Collection
    {
        return $this->walls;
    }

    public function addWall(Wall $wall): static
    {
        if (!$this->walls->contains($wall)) {
            $this->walls->add($wall);
            $wall->setUser($this);
        }

        return $this;
    }

    public function removeWall(Wall $wall): static
    {
        $this->walls->removeElement($wall);

        return $this;
    }
}
