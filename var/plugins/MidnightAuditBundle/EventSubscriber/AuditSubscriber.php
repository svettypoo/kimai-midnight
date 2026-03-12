<?php

namespace KimaiPlugin\MidnightAuditBundle\EventSubscriber;

use App\Entity\Activity;
use App\Entity\Customer;
use App\Entity\Project;
use App\Entity\Timesheet;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use KimaiPlugin\MidnightAuditBundle\Entity\AuditLog;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class AuditSubscriber
{
    private const AUDITED_ENTITIES = [
        Timesheet::class => 'Timesheet',
        Customer::class  => 'Customer',
        Project::class   => 'Project',
        Activity::class  => 'Activity',
        User::class      => 'User',
    ];

    /** @var array<string, array<string, array{old: mixed, new: mixed}>> */
    private array $changeSets = [];

    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * Capture change sets before the update is flushed,
     * because UnitOfWork change sets are cleared after flush.
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityType = $this->getEntityType($entity);

        if ($entityType === null) {
            return;
        }

        $oid = spl_object_id($entity);
        $this->changeSets[$oid] = [];

        foreach ($args->getEntityChangeSet() as $field => [$oldValue, $newValue]) {
            $this->changeSets[$oid][$field] = [
                'old' => $this->normalizeValue($oldValue),
                'new' => $this->normalizeValue($newValue),
            ];
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityType = $this->getEntityType($entity);

        if ($entityType === null) {
            return;
        }

        $em = $args->getObjectManager();
        $log = $this->createLog($entityType, $this->getEntityId($entity), 'create');
        $em->persist($log);
        $em->flush();
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityType = $this->getEntityType($entity);

        if ($entityType === null) {
            return;
        }

        $em = $args->getObjectManager();
        $entityId = $this->getEntityId($entity);
        $oid = spl_object_id($entity);
        $changes = $this->changeSets[$oid] ?? [];
        unset($this->changeSets[$oid]);

        if (empty($changes)) {
            // No tracked field changes — log a generic update
            $log = $this->createLog($entityType, $entityId, 'update');
            $em->persist($log);
        } else {
            foreach ($changes as $field => $values) {
                $log = $this->createLog($entityType, $entityId, 'update');
                $log->setFieldName($field);
                $log->setOldValue($values['old']);
                $log->setNewValue($values['new']);
                $em->persist($log);
            }
        }

        $em->flush();
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityType = $this->getEntityType($entity);

        if ($entityType === null) {
            return;
        }

        $em = $args->getObjectManager();
        $log = $this->createLog($entityType, $this->getEntityId($entity), 'delete');
        $em->persist($log);
        $em->flush();
    }

    private function getEntityType(object $entity): ?string
    {
        return self::AUDITED_ENTITIES[get_class($entity)] ?? null;
    }

    private function getEntityId(object $entity): int
    {
        if (method_exists($entity, 'getId')) {
            return (int) $entity->getId();
        }

        return 0;
    }

    private function createLog(string $entityType, int $entityId, string $action): AuditLog
    {
        $log = new AuditLog();
        $log->setEntityType($entityType);
        $log->setEntityId($entityId);
        $log->setAction($action);

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $log->setUserId((int) $user->getId());
            $log->setUsername($user->getUserIdentifier());
        }

        return $log;
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value) && method_exists($value, 'getId')) {
            return (string) $value->getId();
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
