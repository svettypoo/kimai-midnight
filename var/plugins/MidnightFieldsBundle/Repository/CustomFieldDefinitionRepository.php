<?php

namespace KimaiPlugin\MidnightFieldsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightFieldsBundle\Entity\CustomFieldDefinition;

/**
 * @extends ServiceEntityRepository<CustomFieldDefinition>
 */
class CustomFieldDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomFieldDefinition::class);
    }

    /**
     * @return CustomFieldDefinition[]
     */
    public function findByEntityType(string $entityType): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.entity_type = :type')
            ->setParameter('type', $entityType)
            ->orderBy('d.position', 'ASC')
            ->addOrderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CustomFieldDefinition[]
     */
    public function findVisible(?string $entityType = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.visible = :visible')
            ->setParameter('visible', true)
            ->orderBy('d.position', 'ASC')
            ->addOrderBy('d.name', 'ASC');

        if ($entityType !== null) {
            $qb->andWhere('d.entity_type = :type')
               ->setParameter('type', $entityType);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<string, CustomFieldDefinition[]>
     */
    public function findAllGroupedByEntityType(): array
    {
        $all = $this->createQueryBuilder('d')
            ->orderBy('d.entity_type', 'ASC')
            ->addOrderBy('d.position', 'ASC')
            ->addOrderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach (CustomFieldDefinition::ENTITY_TYPES as $type) {
            $grouped[$type] = [];
        }

        foreach ($all as $definition) {
            $grouped[$definition->getEntityType()][] = $definition;
        }

        return $grouped;
    }

    public function save(CustomFieldDefinition $definition, bool $flush = true): void
    {
        $this->getEntityManager()->persist($definition);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomFieldDefinition $definition, bool $flush = true): void
    {
        $this->getEntityManager()->remove($definition);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
