<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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
    public function findNewest(int $maxResult = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findLatestsArray(int $maxResult = 10): array
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->select(self::QB_ALIAS, UserRepository::QB_ALIAS)
            ->leftJoin(self::QB_ALIAS.'.user', UserRepository::QB_ALIAS)
            ->orderBy(self::QB_ALIAS.'.id', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findQuestions(int $maxResult = 5): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'u')
            ->leftJoin('p.user', 'u')
            ->andWhere('p.question = true')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<mixed, mixed>
     */
    public function search(string $searchInput, int $maxResult = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.title) LIKE :search_input OR p.titleArabic LIKE :search_input')
            ->setParameter('search_input', '%'.strtolower($searchInput).'%')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Query<array<int, Post>>
     */
    public function findPaginatedQuery(PostType $type): Query
    {
        return $this->createQueryBuilder('p')
            ->where('p.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();
    }

    /**
     * @return Query<array<int, Post>>
     */
    public function findPaginatedByUserQuery(User $user): Query
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();
    }

    /**
     * @return Query<array<int, Post>>
     */
    public function findPaginatedQuestionsQuery(): Query
    {
        return $this->createQueryBuilder('p')
            ->where('p.question = TRUE')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();
    }

    /**
     * @return array<int, array{type: PostType, count: int}>
     */
    public function countWeeklyPosts(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.type AS type', 'COUNT(p.id) AS count')
            ->where('p.createdAt >= :last_week')
            ->setParameter('last_week', new \DateTimeImmutable('-1 week'))
            ->groupBy('p.type')
            ->getQuery()
            ->getArrayResult();
    }
}
