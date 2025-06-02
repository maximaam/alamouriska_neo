<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public const QB_ALIAS = 'p';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findLatests(int $maxResult = 10): array
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->select(self::QB_ALIAS, UserRepository::QB_ALIAS)
            ->leftJoin(self::QB_ALIAS.'.user', UserRepository::QB_ALIAS)
            ->orderBy(self::QB_ALIAS.'.id', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
