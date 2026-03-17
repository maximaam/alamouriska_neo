<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserComment>
 */
class UserCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserComment::class);
    }

    /**
     * @param array<int, int> $entityIds
     *
     * @return int[] Array of liked post IDs
     */
    public function getUserInteractionIds(array $entityIds, string $entityName, User $user): array
    {
        return $this->createQueryBuilder('uc')
            ->select('IDENTITY(uc.'.$entityName.')')
            ->andWhere('uc.'.$entityName.' IN (:entityIds)')
            ->andWhere('uc.user = :user')
            ->setParameter('entityIds', $entityIds)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findNewest(int $maxResult = 5): array
    {
        return $this->createQueryBuilder('c') // c = Comment
            ->select('c', 'p', 'postUser', 'commentUser')
            ->leftJoin('c.post', 'p')
            ->leftJoin('p.user', 'postUser')       // author of the post
            ->leftJoin('c.user', 'commentUser')    // author of the comment
            ->where('c.post IS NOT NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    public function findByPostIds(array $postIds, ?int $userId): array
    {
        return $this->createQueryBuilder('uc')
            ->select('IDENTITY(uc.post) AS postId')
            ->addSelect('COUNT(uc.id) AS commentCount')
            ->addSelect('MAX(CASE WHEN uc.user = :userId THEN 1 ELSE 0 END) AS commentedByCurrentUser')
            ->where('uc.post IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->setParameter('userId', $userId ?? 0)
            ->groupBy('uc.post')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }
}
