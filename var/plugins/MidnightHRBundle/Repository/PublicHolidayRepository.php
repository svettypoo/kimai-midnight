<?php

namespace KimaiPlugin\MidnightHRBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightHRBundle\Entity\PublicHoliday;

/**
 * @extends ServiceEntityRepository<PublicHoliday>
 */
class PublicHolidayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicHoliday::class);
    }

    /**
     * @return PublicHoliday[]
     */
    public function findByYear(int $year, string $country = 'CA'): array
    {
        return $this->createQueryBuilder('h')
            ->where('YEAR(h.date) = :year OR h.recurring = true')
            ->andWhere('h.country = :country')
            ->setParameter('year', $year)
            ->setParameter('country', $country)
            ->orderBy('h.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return PublicHoliday[]
     */
    public function findUpcoming(int $limit = 10, string $country = 'CA'): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.date >= :today')
            ->andWhere('h.country = :country')
            ->setParameter('today', new \DateTime('today'))
            ->setParameter('country', $country)
            ->orderBy('h.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if a date is a public holiday.
     */
    public function isHoliday(\DateTimeInterface $date, string $country = 'CA'): bool
    {
        $result = $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.date = :date')
            ->andWhere('h.country = :country')
            ->setParameter('date', $date)
            ->setParameter('country', $country)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }
}
