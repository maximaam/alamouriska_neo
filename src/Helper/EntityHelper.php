<?php

declare(strict_types=1);

namespace App\Helper;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\Wall;
use App\Utils\SocialMediaUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EntityHelper
{
    public const ENTITY_CLASS_MAP = [
        'post' => Post::class,
        'wall' => Wall::class,
    ];

    public function resolveEntity(EntityManagerInterface $em, string $entityName, int $id): Post|Wall|null
    {
        return $em->getRepository(self::ENTITY_CLASS_MAP[$entityName])->find($id);
    }

    public function generateEntityUrl(Wall|Post $entity, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator): string
    {
        if ($entity instanceof Post) {
            return $urlGenerator->generate('app_frontend_post', [
                'seoTypeSlug' => $translator->trans(\sprintf('post.%s.seo_route', $entity->getType()->name)),
                'id' => $entity->getId(),
                'titleSlug' => $entity->getTitleSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $urlGenerator->generate('app_frontend_wall', [
            'id' => $entity->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return string[] Unique list of email addresses to notify
     */
    public function collectCommentators(Wall|Post $entity, User $user): array
    {
        $commentators = array_unique(
            array_merge(
                [$entity->getUser()->getEmail()],
                array_map(
                    static fn (UserComment $c) => $c->getUser()->getEmail(),
                    $entity->getUserComments()->toArray(),
                ),
            )
        );

        return array_values(array_diff($commentators, [$user->getEmail()]));
    }

    public function createUserComment(Wall|Post $entity, User $user, string $comment): UserComment
    {
        $commentText = SocialMediaUtils::linkifyUrls($comment);
        $userComment = (new UserComment())
            ->setUser($user)
            ->setComment($commentText);

        if ($entity instanceof Post) {
            $userComment->setPost($entity);
        } else {
            $userComment->setWall($entity);
        }

        return $userComment;
    }
}
