<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserComment;
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
