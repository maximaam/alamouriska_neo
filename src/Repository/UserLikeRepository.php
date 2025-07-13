<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserLike>
 */
class UserLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLike::class);
    }

    /**
     * @param array<int, int> $entityIds
     *
     * @return int[] Array of liked post IDs
     */
    public function getUserInteractionIds(array $entityIds, string $entityName, User $user): array
    {
        return $this->createQueryBuilder('ul')
            ->select('IDENTITY(ul.'.$entityName.')') // returns just the post IDs
            ->andWhere('ul.'.$entityName.' IN (:entityIds)')
            ->andWhere('ul.user = :user')
            ->setParameter('entityIds', $entityIds)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult(); // returns array of post IDs
    }
}
