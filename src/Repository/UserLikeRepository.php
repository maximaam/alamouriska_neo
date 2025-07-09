<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserLike;
use App\Entity\Wall;
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
     * @param Post[]|Wall[]|Post|Wall $entities Either a single Post/Wall or an array of them
     *
     * @return int[] Array of liked post IDs
     */
    public function getUserInteractionIds(array $entityIds, User $user): array
    {
        if (!\is_iterable($entityIds)) {
            $entityIds = [$entityIds];
        }

        return $this->createQueryBuilder('ul')
            ->select('ul.id') // returns just the post IDs
            ->andWhere('ul.id IN (:entityIds)')
            ->andWhere('ul.user = :user')
            ->setParameter('entityIds', $entityIds)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult(); // returns array of post IDs
    }
}
