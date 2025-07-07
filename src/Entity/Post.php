<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PostType;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[UniqueEntity(fields: ['title'], message: 'post_title_exists')]
#[Vich\Uploadable]
// #[Assert\Callback('validateTitle')]
class Post
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT, enumType: PostType::class, nullable: false)]
    #[Assert\NotBlank]
    private PostType $type;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $title;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $titleArabic = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $description;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(nullable: true)]
    private ?string $postImageName = null;

    #[Vich\UploadableField(mapping: 'posts', fileNameProperty: 'postImageName')]
    private ?File $postImageFile = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    #[Slug(fields: ['title'])]
    private string $titleSlug;

    /**
     * @var Collection<int, UserLike>
     */
    #[ORM\OneToMany(targetEntity: UserLike::class, mappedBy: 'post', orphanRemoval: true, fetch: 'EAGER')]
    private Collection $userLikes;

    /**
     * @var Collection<int, UserComment>
     */
    #[ORM\OneToMany(targetEntity: UserComment::class, mappedBy: 'post', orphanRemoval: true, fetch: 'EAGER')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $userComments;

    #[ORM\Column]
    private bool $question = false;

    public function __construct()
    {
        $this->userLikes = new ArrayCollection();
        $this->userComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): PostType
    {
        return $this->type;
    }

    public function setType(PostType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitleArabic(): ?string
    {
        return $this->titleArabic;
    }

    public function setTitleArabic(string $titleArabic): static
    {
        $this->titleArabic = $titleArabic;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPostImageName(): ?string
    {
        return $this->postImageName;
    }

    public function setPostImageName(?string $postImageName): static
    {
        $this->postImageName = $postImageName;

        return $this;
    }

    public function setPostImageFile(?File $postImageFile = null): void
    {
        $this->postImageFile = $postImageFile;

        if (null !== $postImageFile) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getPostImageFile(): ?File
    {
        return $this->postImageFile;
    }

    public function getTitleSlug(): string
    {
        return $this->titleSlug;
    }

    public function setTitleSlug(string $titleSlug): static
    {
        $this->titleSlug = $titleSlug;

        return $this;
    }

    /*
    public function validateTitle(ExecutionContextInterface $context): void
    {
        if (!\in_array($this->type, [PostType::joke], true) && '' === $this->titleLatin) {
            $context->buildViolation('Requis.')
                ->atPath('title')
                ->addViolation();
        }
    }
    */

    /**
     * @return Collection<int, UserLike>
     */
    public function getUserLikes(): Collection
    {
        return $this->userLikes;
    }

    public function addPostLike(UserLike $userLike): static
    {
        if (!$this->userLikes->contains($userLike)) {
            $this->userLikes->add($userLike);
            $userLike->setPost($this);
        }

        return $this;
    }

    public function removeLike(UserLike $userLike): static
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

    public function addPostComment(UserComment $userComment): static
    {
        if (!$this->userComments->contains($userComment)) {
            $this->userComments->add($userComment);
            $userComment->setPost($this);
        }

        return $this;
    }

    public function removeComment(UserComment $userComment): static
    {
        $this->userComments->removeElement($userComment);

        return $this;
    }

    public function isQuestion(): bool
    {
        return $this->question;
    }

    public function setQuestion(bool $question): static
    {
        $this->question = $question;

        return $this;
    }
}
