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
     * @return array<int, array<string, mixed>>
     */
    public function fetchNewest(?int $currentUserId, int $maxResult = 10): array
    {
        return $this->baseFlatQueryBuilder($currentUserId)
            ->setMaxResults($maxResult)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchNewestSidebar(int $maxResult = 10): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.title, p.titleSlug, p.type')
            ->addSelect('u.id as userId, u.pseudo, u.avatarName')
            ->innerJoin('p.user', 'u')
            ->setMaxResults($maxResult)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $titleSlug, ?int $currentUserId): ?array
    {
        return $this->baseFlatQueryBuilder($currentUserId)
            ->where('p.titleSlug = :title_slug')
            ->setParameter('title_slug', $titleSlug)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    private function baseFlatQueryBuilder(?int $currentUserId): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.title, p.titleArabic, p.description, p.titleSlug, p.createdAt, p.updatedAt, p.type, p.question, p.postImageName')
            ->addSelect('u.id as userId, u.pseudo, u.avatarName')
            ->addSelect('(SELECT COUNT(ul.id) FROM App\Entity\UserLike ul WHERE ul.post = p.id) AS likeCount')
            ->addSelect('(SELECT COUNT(uc.id) FROM App\Entity\UserComment uc WHERE uc.post = p.id) AS commentCount')
            ->addSelect('(
                SELECT COUNT(ul2.id)
                FROM App\Entity\UserLike ul2
                WHERE ul2.post = p.id AND ul2.user = :currentUser
                ) AS likedByCurrentUser')
            ->addSelect('(
                SELECT COUNT(uc2.id)
                FROM App\Entity\UserComment uc2
                WHERE uc2.post = p.id AND uc2.user = :currentUser
                ) AS commentedByCurrentUser')
            ->innerJoin('p.user', 'u')
            ->setParameter('currentUser', $currentUserId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $searchInput, int $maxResult = 10): array
    {
        return $this->baseFlatQueryBuilder(null)
            ->andWhere('LOWER(p.title) LIKE :search_input OR p.titleArabic LIKE :search_input')
            ->setParameter('search_input', '%'.strtolower($searchInput).'%')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    public function fetchByType(PostType $type): array
    {
        return $this->baseFlatQueryBuilder(null)
            ->andWhere('p.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
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
     * @return array<int, array<string, mixed>>
     */
    public function fetchPaginatedByUser(User $user): array
    {
        return $this->baseFlatQueryBuilder($user->getId())
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function fetchQuestions(): array
    {
        return $this->baseFlatQueryBuilder(null)
            ->where('p.question = TRUE')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
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
