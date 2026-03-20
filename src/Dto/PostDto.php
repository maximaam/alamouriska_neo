<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class PostDto
{
    /**
     * @param array<int, array<string, mixed>> $posts
     *
     * @return array<int, array<string, mixed>>
     */
    public function fromFlatEntities(array $posts, string $entityName = 'post'): array
    {
        return array_map(fn ($post) => $this->map($post, $entityName), $posts);
    }

    /**
     * @param array<string, mixed> $post
     *
     * @return array<string, mixed>
     */
    public function fromFlatEntity(array $post, string $entityName = 'post'): array
    {
        return $this->map($post, $entityName);
    }

    /**
     * @param array<string, mixed> $post
     *
     * @return array<string, mixed>
     */
    private function map(array $post, string $entityName): array
    {
        return [
            ...$post,
            'entityName' => $entityName,
            'type' => isset($post['type']) ? $post['type']->name : null,
            'createdAt' => $post['createdAt']->format('Y-m-d H:i:s'),
            'updatedAt' => $post['updatedAt']->format('Y-m-d H:i:s'),
        ];
    }
}
