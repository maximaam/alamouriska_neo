<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\Wall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
     * @param Post[]|Wall[]|Post|Wall $entities Either a single Post/Wall or an array of them
     *
     * @return int[] Array of liked post IDs
     */
    public function getUserInteractionIds(array|Post|Wall $entities, string $entityName, User $user): array
    {
        if (!\is_iterable($entities)) {
            $entities = [$entities];
        }

        return $this->createQueryBuilder('uc')
            ->select('IDENTITY(uc.'.$entityName.')')
            ->andWhere('uc.'.$entityName.' IN (:entities)')
            ->andWhere('uc.user = :user')
            ->setParameter('entities', $entities)
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
}
