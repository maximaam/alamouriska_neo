<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Wall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wall>
 */
class WallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wall::class);
    }

    private function baseFlatQueryBuilder(?int $currentUserId): QueryBuilder
    {
        return $this->createQueryBuilder('w')
            ->select('w.id, w.description, w.descriptionHtml, w.createdAt, w.updatedAt')
            ->addSelect('u.id as userId, u.pseudo, u.avatarName')
            ->addSelect('(SELECT COUNT(ul.id) FROM App\Entity\UserLike ul WHERE ul.wall = w.id) AS likeCount')
            ->addSelect('(SELECT COUNT(uc.id) FROM App\Entity\UserComment uc WHERE uc.wall = w.id) AS commentCount')
            ->addSelect('(
                SELECT COUNT(ul2.id)
                FROM App\Entity\UserLike ul2
                WHERE ul2.wall = w.id AND ul2.user = :currentUser
                ) AS likedByCurrentUser')
            ->addSelect('(
                SELECT COUNT(uc2.id)
                FROM App\Entity\UserComment uc2
                WHERE uc2.wall = w.id AND uc2.user = :currentUser
                ) AS commentedByCurrentUser')
            ->innerJoin('w.user', 'u')
            ->setParameter('currentUser', $currentUserId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(?int $currentUserId, int $maxResult = 10): array
    {
        return $this->baseFlatQueryBuilder($currentUserId)
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOne(int $id, ?int $currentUserId): ?array
    {
        return $this->baseFlatQueryBuilder($currentUserId)
            ->where('w.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }
}
