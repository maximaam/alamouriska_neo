<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchNewest(?User $currentUser, int $maxResult = 10): array
    {
        return $this->baseFlatQueryBuilder($currentUser?->getId())
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

            // ✅ counts via JOIN (no subqueries)
            ->addSelect('COUNT(DISTINCT ul.id) AS likeCount')
            ->addSelect('COUNT(DISTINCT uc.id) AS commentCount')

            // ✅ user flags via JOIN
            ->addSelect('CASE WHEN COUNT(DISTINCT ul2.id) > 0 THEN true ELSE false END AS likedByCurrentUser')
            ->addSelect('CASE WHEN COUNT(DISTINCT uc2.id) > 0 THEN true ELSE false END AS commentedByCurrentUser')

            ->innerJoin('p.user', 'u')

            ->leftJoin('p.userLikes', 'ul')
            ->leftJoin('p.userComments', 'uc')

            ->leftJoin('p.userLikes', 'ul2', 'WITH', 'ul2.user = :currentUser')
            ->leftJoin('p.userComments', 'uc2', 'WITH', 'uc2.user = :currentUser')

            ->setParameter('currentUser', $currentUserId)

            ->groupBy('p.id, u.id');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $searchInput, ?int $currentUserId, int $maxResult = 10): array
    {
        return $this->baseFlatQueryBuilder($currentUserId)
            ->andWhere('LOWER(p.title) LIKE :search_input OR p.titleArabic LIKE :search_input')
            ->setParameter('search_input', '%'.strtolower($searchInput).'%')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchByType(PostType $type, ?int $currentUserId): array
    {
        return $this->baseFlatQueryBuilder($currentUserId)
            ->andWhere('p.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchByUser(User $user): array
    {
        return $this->baseFlatQueryBuilder($user->getId())
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchQuestions(): array
    {
        return $this->baseFlatQueryBuilder(null)
            ->where('p.question = TRUE')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(5)
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
