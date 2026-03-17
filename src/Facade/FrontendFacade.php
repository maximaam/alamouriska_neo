<?php

declare(strict_types=1);

namespace App\Facade;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\UserLike;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FrontendFacade
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function getNewestPosts(?User $currentUser): array
    {
        $posts = $this->em->getRepository(Post::class)->findNewest();
        $postIds = array_column($posts, 'id');

        if ([] === $postIds) {
            return [];
        }

        $likes = $this->em->getRepository(UserLike::class)
            ->findByPostIds($postIds, $currentUser?->getId());
        $comments = $this->em->getRepository(UserComment::class)
            ->findByPostIds($postIds, $currentUser?->getId());

        $likesByPost = array_column($likes, null, 'postId');
        $commentsByPost = array_column($comments, null, 'postId');

        foreach ($posts as &$post) {
            $id = $post['id'];

            $post['entityName'] = 'post';
            $post['type'] = $post['type']->name;
            $post['createdAt'] = $post['createdAt']->format('Y-m-d H:i:s');
            $post['updatedAt'] = $post['updatedAt']->format('Y-m-d H:i:s');

            $like = $likesByPost[$id] ?? ['likeCount' => 0, 'likedByCurrentUser' => 0];
            $comment = $commentsByPost[$id] ?? ['commentCount' => 0, 'commentedByCurrentUser' => 0];

            $post['likeCount'] = (int) $like['likeCount'];
            $post['likedByCurrentUser'] = (bool) $like['likedByCurrentUser'];

            $post['commentCount'] = (int) $comment['commentCount'];
            $post['commentedByCurrentUser'] = (bool) $comment['commentedByCurrentUser'];
        }

        return $posts;
    }
}
