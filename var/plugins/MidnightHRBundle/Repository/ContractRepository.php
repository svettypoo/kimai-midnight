<?php

namespace KimaiPlugin\MidnightHRBundle\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightHRBundle\Entity\Contract;

/**
 * @extends ServiceEntityRepository<Contract>
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    public function findActiveForUser(User $user): ?Contract
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.startDate <= :now')
            ->andWhere('c.endDate IS NULL OR c.endDate >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('today'))
            ->orderBy('c.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Contract[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.startDate <= :now')
            ->andWhere('c.endDate IS NULL OR c.endDate >= :now')
            ->setParameter('now', new \DateTime('today'))
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contract[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
