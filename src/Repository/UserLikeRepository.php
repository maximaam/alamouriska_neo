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
     * @param Post[]|Wall[]|Post|Wall $posts A single Post or an array of Posts
     *
     * @return int[] Array of liked post IDs
     */
    public function getUserInteractionIds(array|Post|Wall $entities, string $entityName, User $user): array
    {
        if (!\is_iterable($entities)) {
            $entities = [$entities];
        }

        return $this->createQueryBuilder('l')
            ->select('IDENTITY(l.'.$entityName.')') // returns just the post IDs
            ->andWhere('l.'.$entityName.' IN (:entities)')
            ->andWhere('l.user = :user')
            ->setParameter('entities', $entities)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult(); // returns array of post IDs
    }
}
