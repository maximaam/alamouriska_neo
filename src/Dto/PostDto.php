<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Post;
use App\Entity\UserComment;
use App\Entity\UserLike;

final readonly class PostDto
{
    /**
     * @param array<int, array> $posts
     *
     * @return array<string, int]string>
     */
    public function flatPosts(array $posts): array
    {
        foreach ($posts as &$post) {
            $post['entityName'] = 'post';
            $post['type'] = $post['type']->name;
            $post['createdAt'] = $post['createdAt']->format('Y-m-d H:i:s');
            $post['updatedAt'] = $post['updatedAt']->format('Y-m-d H:i:s');
            $post['commentIds'] = $post['commentIds'] ? explode(',', $post['commentIds']) : [];
            $post['likeIds'] = $post['likeIds'] ? explode(',', $post['likeIds']) : [];
        }

        return $posts;
    }

    /**
     * @param Post[] $posts
     *
     * @return array<string, int]string>
     */
    public function flatPostsOld(array $posts): array
    {
        $result = [];

        foreach ($posts as $post) {
            $user = $post->getUser();
            $result[] = [
                'post' => [
                    'id' => $post->getId(),
                    'type' => $post->getType()->name,
                    'title' => $post->getTitle(),
                    'title_arabic' => $post->getTitleArabic(),
                    'description' => $post->getDescription(),
                    'title_slug' => $post->getTitleSlug(),
                    'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
                ],
                'user' => [
                    'id' => $user->getId(),
                    'pseudo' => $user->getPseudo(),
                    'avatar' => $user->getAvatarName(),
                ],
                'comments_ids' => array_map(
                    static fn (UserComment $comment) => $comment->getId(),
                    $post->getUserComments()->toArray()
                ),
                'likes_ids' => array_map(
                    static fn (UserLike $like) => $like->getId(),
                    $post->getUserLikes()->toArray()
                ),
            ];
        }

        return $result;
    }
}
