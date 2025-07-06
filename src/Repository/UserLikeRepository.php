<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\UserLike;
use App\Entity\User;
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
     * @param Post[]|Post $posts A single Post or an array of Posts
     *
     * @return int[] Array of liked post IDs
     */
    public function findLikedPostIdsByUser(array|Post $posts, User $user): array
    {
        if ($posts instanceof Post) {
            $posts = [$posts];
        }

        return $this->createQueryBuilder('l')
            ->select('IDENTITY(l.post)') // returns just the post IDs
            ->andWhere('l.post IN (:posts)')
            ->andWhere('l.user = :user')
            ->setParameter('posts', $posts)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult(); // returns array of post IDs
    }

    /*
    public function hasUserLiked(Post $post, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->createQueryBuilder('pl')
            ->select('1')
            ->andWhere('pl.post = :post')
            ->andWhere('pl.user = :user')
            ->setParameter('post', $post)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }
    */
}
