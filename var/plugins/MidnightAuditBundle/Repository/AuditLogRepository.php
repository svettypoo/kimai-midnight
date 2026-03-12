<?php

namespace KimaiPlugin\MidnightAuditBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightAuditBundle\Entity\AuditLog;

/**
 * @extends ServiceEntityRepository<AuditLog>
 */
class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    /**
     * Find audit logs for a specific entity.
     *
     * @return AuditLog[]
     */
    public function findByEntity(string $entityType, int $entityId, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.entityType = :type')
            ->andWhere('a.entityId = :id')
            ->setParameter('type', $entityType)
            ->setParameter('id', $entityId)
            ->orderBy('a.timestamp', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find audit logs by a specific user.
     *
     * @return AuditLog[]
     */
    public function findByUser(int $userId, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.timestamp', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find most recent audit log entries.
     *
     * @return AuditLog[]
     */
    public function findRecent(int $limit = 50, ?string $entityType = null, ?int $userId = null, ?\DateTimeInterface $dateFrom = null, ?\DateTimeInterface $dateTo = null): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($entityType !== null) {
            $qb->andWhere('a.entityType = :type')
               ->setParameter('type', $entityType);
        }

        if ($userId !== null) {
            $qb->andWhere('a.userId = :userId')
               ->setParameter('userId', $userId);
        }

        if ($dateFrom !== null) {
            $qb->andWhere('a.timestamp >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb->andWhere('a.timestamp <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        return $qb->orderBy('a.timestamp', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
