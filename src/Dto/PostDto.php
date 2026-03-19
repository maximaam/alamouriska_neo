<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class PostDto
{
    private string $entityName;

    /**
     * @param array<int, array<string, mixed>> $posts
     *
     * @return array<int, array<string, mixed>>
     */
    public function fromFlatEntities(array $posts, string $entityName = 'post'): array
    {
        $this->entityName = $entityName;

        return array_map($this->map(...), $posts);
    }

    /**
     * @param array<string, mixed> $post
     *
     * @return array<string, mixed>
     */
    public function fromFlatEntity(array $post, string $entityName = 'post'): array
    {
        $this->entityName = $entityName;

        return $this->map($post);
    }

    /**
     * @param array<string, mixed> $post
     *
     * @return array<string, mixed>
     */
    private function map(array $post): array
    {
        return [
            ...$post,
            'entityName' => $this->entityName,
            'type' => isset($post['type']) ? $post['type']->name : null,
            'createdAt' => $post['createdAt']->format('Y-m-d H:i:s'),
            'updatedAt' => $post['updatedAt']->format('Y-m-d H:i:s'),
        ];
    }
}
