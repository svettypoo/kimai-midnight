<?php

namespace KimaiPlugin\MidnightHRBundle\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightHRBundle\Entity\Absence;

/**
 * @extends ServiceEntityRepository<Absence>
 */
class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    /**
     * @return Absence[]
     */
    public function findByUser(User $user, ?int $year = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.startDate', 'DESC');

        if ($year !== null) {
            $qb->andWhere('YEAR(a.startDate) = :year OR YEAR(a.endDate) = :year')
               ->setParameter('year', $year);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Absence[]
     */
    public function findByStatus(string $status, int $limit = 50): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', $status)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Absence[]
     */
    public function findPending(): array
    {
        return $this->findByStatus(Absence::STATUS_PENDING);
    }

    /**
     * Find absences for a user in a date range.
     *
     * @return Absence[]
     */
    public function findByUserAndDateRange(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.startDate <= :end')
            ->andWhere('a.endDate >= :start')
            ->andWhere('a.status != :denied')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('denied', Absence::STATUS_DENIED)
            ->orderBy('a.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count approved vacation days used in a year.
     */
    public function countVacationDaysUsed(User $user, int $year): float
    {
        $absences = $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.type = :type')
            ->andWhere('a.status = :approved')
            ->andWhere('YEAR(a.startDate) = :year OR YEAR(a.endDate) = :year')
            ->setParameter('user', $user)
            ->setParameter('type', Absence::TYPE_VACATION)
            ->setParameter('approved', Absence::STATUS_APPROVED)
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult();

        $total = 0.0;
        foreach ($absences as $absence) {
            $total += $absence->getBusinessDays();
        }

        return $total;
    }

    /**
     * Count sick days used in a year.
     */
    public function countSickDaysUsed(User $user, int $year): float
    {
        $absences = $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.type = :type')
            ->andWhere('a.status != :denied')
            ->andWhere('YEAR(a.startDate) = :year OR YEAR(a.endDate) = :year')
            ->setParameter('user', $user)
            ->setParameter('type', Absence::TYPE_SICK)
            ->setParameter('denied', Absence::STATUS_DENIED)
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult();

        $total = 0.0;
        foreach ($absences as $absence) {
            $total += $absence->getBusinessDays();
        }

        return $total;
    }

    /**
     * Find all absences in a date range (for calendar view).
     *
     * @return Absence[]
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.startDate <= :end')
            ->andWhere('a.endDate >= :start')
            ->andWhere('a.status != :denied')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('denied', Absence::STATUS_DENIED)
            ->orderBy('a.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
