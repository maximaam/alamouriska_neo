<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public const QB_ALIAS = 'u';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findNewest(int $maxResult = 5): array
    {
        return $this->createQueryBuilder(self::QB_ALIAS)
            ->andWhere(self::QB_ALIAS.'.isVerified = true')
            ->orderBy(self::QB_ALIAS.'.createdAt', 'DESC')
            ->setMaxResults($maxResult)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<mixed, mixed>
     */
    public function findEnablePostNotification(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.email')
            ->andWhere('u.enablePostNotification = true')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
