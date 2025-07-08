<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WallRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WallRepository::class)]
class Wall
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(
        min: 10,
        max: 3000,
        minMessage: 'wall_description_short',
        maxMessage: 'wall_description_long',
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descriptionHtml = null;

    #[ORM\ManyToOne(inversedBy: 'walls')]
    #[ORM\JoinColumn(nullable: false)]
    private User $User;

    /**
     * @var Collection<int, UserLike>
     */
    #[ORM\OneToMany(targetEntity: UserLike::class, mappedBy: 'wall', orphanRemoval: true, fetch: 'EAGER')]
    private Collection $userLikes;

    /**
     * @var Collection<int, UserComment>
     */
    #[ORM\OneToMany(targetEntity: UserComment::class, mappedBy: 'wall', orphanRemoval: true, fetch: 'EAGER')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $userComments;

    public function __construct()
    {
        $this->userLikes = new ArrayCollection();
        $this->userComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescriptionHtml(): ?string
    {
        return $this->descriptionHtml;
    }

    public function setDescriptionHtml(string $descriptionHtml): static
    {
        $this->descriptionHtml = $descriptionHtml;

        return $this;
    }

    public function getUser(): User
    {
        return $this->User;
    }

    public function setUser(User $User): static
    {
        $this->User = $User;

        return $this;
    }

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
            $userLike->setWall($this);
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
            $userComment->setWall($this);
        }

        return $this;
    }

    public function removeComment(UserComment $userComment): static
    {
        $this->userComments->removeElement($userComment);

        return $this;
    }
}
