<?php

namespace KimaiPlugin\MidnightFieldsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightFieldsBundle\Entity\CustomFieldDefinition;
use KimaiPlugin\MidnightFieldsBundle\Entity\CustomFieldValue;

/**
 * @extends ServiceEntityRepository<CustomFieldValue>
 */
class CustomFieldValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomFieldValue::class);
    }

    /**
     * Find all field values for a specific entity (e.g. timesheet #42).
     *
     * @return CustomFieldValue[]
     */
    public function findByEntity(string $entityType, int $entityId): array
    {
        return $this->createQueryBuilder('v')
            ->join('v.definition', 'd')
            ->where('d.entity_type = :type')
            ->andWhere('v.entity_id = :entityId')
            ->setParameter('type', $entityType)
            ->setParameter('entityId', $entityId)
            ->orderBy('d.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all values for a specific field definition.
     *
     * @return CustomFieldValue[]
     */
    public function findByDefinition(CustomFieldDefinition $definition): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.definition = :definition')
            ->setParameter('definition', $definition)
            ->orderBy('v.entity_id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a single value by definition + entity ID.
     */
    public function findOneByDefinitionAndEntity(CustomFieldDefinition $definition, int $entityId): ?CustomFieldValue
    {
        return $this->createQueryBuilder('v')
            ->where('v.definition = :definition')
            ->andWhere('v.entity_id = :entityId')
            ->setParameter('definition', $definition)
            ->setParameter('entityId', $entityId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(CustomFieldValue $value, bool $flush = true): void
    {
        $this->getEntityManager()->persist($value);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomFieldValue $value, bool $flush = true): void
    {
        $this->getEntityManager()->remove($value);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
