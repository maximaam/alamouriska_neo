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
    public function findLatests(int $maxResult = 10): array
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->orderBy(self::QB_ALIAS.'.createdAt', 'DESC')
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
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->select(self::QB_ALIAS, UserRepository::QB_ALIAS)
            ->leftJoin(self::QB_ALIAS.'.user', UserRepository::QB_ALIAS)
            ->andWhere(self::QB_ALIAS.'.question = true')
            ->orderBy(self::QB_ALIAS.'.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<mixed, mixed>
     */
    public function search(string $searchInput, int $maxResult = 10): array
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->andWhere(self::QB_ALIAS.'.title LIKE :search_input OR '.self::QB_ALIAS.'.titleArabic LIKE :search_input')
            ->setParameter('search_input', '%'.$searchInput.'%')
            ->orderBy(self::QB_ALIAS.'.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Query<array<int, Post>>
     */
    public function findPaginatedQuery(PostType $type): Query
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->where(self::QB_ALIAS.'.type = :type')
            ->setParameter('type', $type)
            ->orderBy(self::QB_ALIAS.'.createdAt', 'DESC')
            ->getQuery();
    }

    /**
     * @return Query<array<int, Post>>
     */
    public function findPaginatedByUserQuery(User $user): Query
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->where(self::QB_ALIAS.'.user = :user')
            ->setParameter('user', $user)
            ->orderBy(self::QB_ALIAS.'.createdAt', 'DESC')
            ->getQuery();
    }

    /**
     * @return Query<array<int, Post>>
     */
    public function findPaginatedQuestionsQuery(): Query
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->where(self::QB_ALIAS.'.question = TRUE')
            ->orderBy(self::QB_ALIAS.'.createdAt', 'DESC')
            ->getQuery();
    }
}
