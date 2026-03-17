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

    public function findNewest(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.title, p.titleArabic, p.description, p.titleSlug, p.createdAt, p.updatedAt, p.type, p.question, p.postImageName')
            ->addSelect('u.id AS userId, u.pseudo, u.avatarName')
            ->innerJoin('p.user', 'u')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
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

    private function baseFlat(?int $currentUserId): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.title, p.titleArabic, p.description, p.titleSlug, p.createdAt, p.updatedAt, p.type, p.question, p.postImageName')
            ->addSelect('u.id as userId, u.pseudo, u.avatarName')
            ->addSelect('COUNT(DISTINCT l.id) AS likeCount')
            ->addSelect('CASE WHEN COUNT(ul.id) > 0 THEN true ELSE false END AS likedByCurrentUser')
            ->addSelect('COUNT(DISTINCT c.id) AS commentCount')
            ->addSelect('CASE WHEN COUNT(uc.id) > 0 THEN true ELSE false END AS commentedByCurrentUser')
            // ->addSelect('CASE WHEN uc.id IS NULL THEN false ELSE true END AS commentedByCurrentUser')
            // ->addSelect('GROUP_CONCAT(DISTINCT c.id) AS commentIds')
            // ->addSelect('GROUP_CONCAT(DISTINCT l.id) AS likeIds')
            // ->addSelect("DATE_FORMAT(p.createdAt, '%Y-%m-%d %H:%i:%s') AS createdAt")
            // ->addSelect('CAST(p.type AS CHAR) AS type2')
            ->innerJoin('p.user', 'u')
            ->leftJoin('p.userComments', 'c')
            ->leftJoin('p.userLikes', 'l')
            ->leftJoin('p.userLikes', 'ul', 'WITH', 'ul.user = :currentUser')
            ->leftJoin('p.userComments', 'uc', 'WITH', 'uc.user = :currentUser')
            ->setParameter('currentUser', $currentUserId)
            ->groupBy('p.id, u.id');
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
