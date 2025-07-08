<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserCommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: UserCommentRepository::class)]
class UserComment
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $comment;

    #[ORM\ManyToOne(inversedBy: 'userComments')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'userComments')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'userComments')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Wall $wall = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getWall(): ?Wall
    {
        return $this->wall;
    }

    public function setWall(?Wall $wall): static
    {
        $this->wall = $wall;

        return $this;
    }
}
