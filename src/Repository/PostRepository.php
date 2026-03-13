<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public const string QB_ALIAS = 'p';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findNewestOld(int $maxResult = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult();
    }

    public function findAllNewestFlat(int $maxResult = 10): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.title, p.titleArabic, p.description, p.titleSlug, p.createdAt, p.updatedAt, p.type, p.question, p.postImageName')
            ->addSelect('u.id as userId, u.pseudo, u.avatarName')
            ->addSelect('GROUP_CONCAT(DISTINCT c.id) AS commentIds')
            ->addSelect('GROUP_CONCAT(DISTINCT l.id) AS likeIds')
            ->innerJoin('p.user', 'u')
            ->leftJoin('p.userComments', 'c')
            ->leftJoin('p.userLikes', 'l')
            ->groupBy('p.id, u.id')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findAllNewest(int $maxResult = 10): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->leftJoin('p.userComments', 'c')
            ->leftJoin('p.userLikes', 'l')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getResult();
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

    private function baseQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->leftJoin('p.userComments', 'uc')
            ->leftJoin('p.userLikes', 'ul');
    }
}
