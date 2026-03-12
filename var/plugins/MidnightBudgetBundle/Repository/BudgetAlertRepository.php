<?php

namespace KimaiPlugin\MidnightBudgetBundle\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightBudgetBundle\Entity\BudgetAlert;

/**
 * @extends ServiceEntityRepository<BudgetAlert>
 */
class BudgetAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetAlert::class);
    }

    /**
     * @return BudgetAlert[]
     */
    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.project = :project')
            ->setParameter('project', $project)
            ->orderBy('b.thresholdPercent', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if a threshold alert already exists for a project.
     */
    public function findByProjectAndThreshold(Project $project, int $threshold): ?BudgetAlert
    {
        return $this->createQueryBuilder('b')
            ->where('b.project = :project')
            ->andWhere('b.thresholdPercent = :threshold')
            ->setParameter('project', $project)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return BudgetAlert[]
     */
    public function findUnnotified(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.notified = false')
            ->orderBy('b.triggeredAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BudgetAlert[]
     */
    public function findRecent(int $limit = 50): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.triggeredAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
