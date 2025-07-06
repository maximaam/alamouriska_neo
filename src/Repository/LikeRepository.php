<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository
{
    public const QB_ALIAS = 'pl';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
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

        return $this->createQueryBuilder(self::QB_ALIAS)
            ->select('IDENTITY('.self::QB_ALIAS.'.post)') // returns just the post IDs
            ->andWhere(self::QB_ALIAS.'.post IN (:posts)')
            ->andWhere(self::QB_ALIAS.'.user = :user')
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
