<?php

namespace KimaiPlugin\MidnightExpenseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightExpenseBundle\Entity\Expense;

/**
 * @extends ServiceEntityRepository<Expense>
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    /**
     * @return Expense[]
     */
    public function findByProject(int $projectId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('e.expenseDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Expense[]
     */
    public function findByCustomer(int $customerId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.customer = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('e.expenseDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Expense[]
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.expenseDate >= :start')
            ->andWhere('e.expenseDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.expenseDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the total expense amount for a given project.
     */
    public function getTotalByProject(int $projectId): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as total')
            ->andWhere('e.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function save(Expense $expense): void
    {
        $em = $this->getEntityManager();
        $em->persist($expense);
        $em->flush();
    }

    public function remove(Expense $expense): void
    {
        $em = $this->getEntityManager();
        $em->remove($expense);
        $em->flush();
    }
}
