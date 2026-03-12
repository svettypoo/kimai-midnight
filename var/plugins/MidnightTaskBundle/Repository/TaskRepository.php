<?php

namespace KimaiPlugin\MidnightTaskBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\MidnightTaskBundle\Entity\Task;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[]
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByAssignee(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.assignee = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.status', 'ASC')
            ->addOrderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByProject(int $projectId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('t.status', 'ASC')
            ->addOrderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findOverdue(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.dueDate < :now')
            ->andWhere('t.status != :done')
            ->setParameter('now', new \DateTime())
            ->setParameter('done', Task::STATUS_DONE)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all tasks grouped by status for kanban view.
     * @return array<string, Task[]>
     */
    public function findAllGroupedByStatus(): array
    {
        $tasks = $this->findBy([], ['priority' => 'DESC', 'dueDate' => 'ASC']);

        $grouped = [];
        foreach (Task::STATUSES as $status) {
            $grouped[$status] = [];
        }
        foreach ($tasks as $task) {
            $grouped[$task->getStatus()][] = $task;
        }

        return $grouped;
    }

    public function save(Task $task): void
    {
        $em = $this->getEntityManager();
        $em->persist($task);
        $em->flush();
    }

    public function remove(Task $task): void
    {
        $em = $this->getEntityManager();
        $em->remove($task);
        $em->flush();
    }
}
