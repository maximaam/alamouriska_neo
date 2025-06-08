<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PostType;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[UniqueEntity(fields: ['title'], message: 'post_title_exists')]
#[Vich\Uploadable]
#[Assert\Callback('validateTitle')]
class Post
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT, enumType: PostType::class)]
    #[Assert\NotBlank]
    private ?PostType $type = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?string $postImageName = null;

    #[Vich\UploadableField(mapping: 'posts', fileNameProperty: 'postImageName')]
    private ?File $postImageFile = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Slug(fields: ['title'])]
    private ?string $titleSlug = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?PostType
    {
        return $this->type;
    }

    public function setType(PostType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
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

    public function getTitleSlug(): ?string
    {
        return $this->titleSlug;
    }

    public function setTitleSlug(string $titleSlug): static
    {
        $this->titleSlug = $titleSlug;

        return $this;
    }

    public function validateTitle(ExecutionContextInterface $context): void
    {
        if (!\in_array($this->type, [PostType::proverb, PostType::joke], true) && '' === $this->title) {
            $context->buildViolation('Requis.')
                ->atPath('title')
                ->addViolation();
        }
    }
}
