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
    public function fromFlatEntities(array $posts): array
    {
        return array_map($this->map(...), $posts);
    }

    /**
     * @param array<string, mixed> $post
     *
     * @return array<string, mixed>
     */
    public function fromFlatEntity(array $post): array
    {
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
            'type' => isset($post['type']) ? $post['type']->name : null,
            'createdAt' => $post['createdAt']->format('Y-m-d H:i:s'),
            'updatedAt' => $post['updatedAt']->format('Y-m-d H:i:s'),
        ];
    }
}
