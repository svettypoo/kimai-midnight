<?php

namespace KimaiPlugin\MidnightFieldsBundle\EventSubscriber;

use App\Entity\Activity;
use App\Entity\Customer;
use App\Entity\Project;
use App\Entity\Timesheet;
use App\Event\ActivityMetaDefinitionEvent;
use App\Event\CustomerMetaDefinitionEvent;
use App\Event\ProjectMetaDefinitionEvent;
use App\Event\TimesheetMetaDefinitionEvent;
use KimaiPlugin\MidnightFieldsBundle\Entity\CustomFieldDefinition;
use KimaiPlugin\MidnightFieldsBundle\Entity\CustomFieldValue;
use KimaiPlugin\MidnightFieldsBundle\Repository\CustomFieldDefinitionRepository;
use KimaiPlugin\MidnightFieldsBundle\Repository\CustomFieldValueRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class CustomFieldSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CustomFieldDefinitionRepository $definitionRepository,
        private readonly CustomFieldValueRepository $valueRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TimesheetMetaDefinitionEvent::class => ['onTimesheetMeta', 200],
            CustomerMetaDefinitionEvent::class => ['onCustomerMeta', 200],
            ProjectMetaDefinitionEvent::class => ['onProjectMeta', 200],
            ActivityMetaDefinitionEvent::class => ['onActivityMeta', 200],
        ];
    }

    public function onTimesheetMeta(TimesheetMetaDefinitionEvent $event): void
    {
        $this->addCustomMetaFields($event->getEntity(), 'timesheet');
    }

    public function onCustomerMeta(CustomerMetaDefinitionEvent $event): void
    {
        $this->addCustomMetaFields($event->getEntity(), 'customer');
    }

    public function onProjectMeta(ProjectMetaDefinitionEvent $event): void
    {
        $this->addCustomMetaFields($event->getEntity(), 'project');
    }

    public function onActivityMeta(ActivityMetaDefinitionEvent $event): void
    {
        $this->addCustomMetaFields($event->getEntity(), 'activity');
    }

    private function addCustomMetaFields(Timesheet|Customer|Project|Activity $entity, string $entityType): void
    {
        $definitions = $this->definitionRepository->findVisible($entityType);

        foreach ($definitions as $definition) {
            $metaField = new \App\Entity\TimesheetMeta();

            // Only Timesheet uses TimesheetMeta; others use their own Meta classes
            $metaField = match ($entityType) {
                'timesheet' => new \App\Entity\TimesheetMeta(),
                'customer' => new \App\Entity\CustomerMeta(),
                'project' => new \App\Entity\ProjectMeta(),
                'activity' => new \App\Entity\ActivityMeta(),
            };

            $metaField->setName('midnight_' . $definition->getFieldKey());
            $metaField->setLabel($definition->getName());
            $metaField->setIsRequired($definition->isRequired());
            $metaField->setIsVisible($definition->isVisible());
            $metaField->setOrder($definition->getPosition());
            $metaField->setType($this->mapFieldType($definition));

            if ($definition->getFieldType() === 'dropdown' && $definition->getOptions() !== null) {
                $choices = [];
                foreach ($definition->getOptions() as $option) {
                    $choices[$option] = $option;
                }
                $metaField->setOptions(['choices' => $choices]);
            }

            // Load existing value if entity has an ID
            if ($entity->getId() !== null) {
                $existingValue = $this->valueRepository->findOneByDefinitionAndEntity($definition, $entity->getId());
                if ($existingValue !== null) {
                    $metaField->setValue($existingValue->getValue());
                }
            }

            $entity->setMetaField($metaField);
        }
    }

    private function mapFieldType(CustomFieldDefinition $definition): string
    {
        return match ($definition->getFieldType()) {
            'text' => TextType::class,
            'number' => IntegerType::class,
            'dropdown' => ChoiceType::class,
            'checkbox' => CheckboxType::class,
            'date' => DateType::class,
            'color' => ColorType::class,
            'textarea' => TextareaType::class,
            'email' => EmailType::class,
            'url' => UrlType::class,
            default => TextType::class,
        };
    }

    /**
     * Persist custom field values after form submission.
     * This should be called from a Doctrine lifecycle event or form event.
     */
    public function saveCustomFieldValues(Timesheet|Customer|Project|Activity $entity, string $entityType): void
    {
        if ($entity->getId() === null) {
            return;
        }

        $definitions = $this->definitionRepository->findVisible($entityType);

        foreach ($definitions as $definition) {
            $metaKey = 'midnight_' . $definition->getFieldKey();
            $meta = $entity->getMetaField($metaKey);

            if ($meta === null) {
                continue;
            }

            $value = $meta->getValue();

            $fieldValue = $this->valueRepository->findOneByDefinitionAndEntity($definition, $entity->getId());

            if ($fieldValue === null) {
                $fieldValue = new CustomFieldValue();
                $fieldValue->setDefinition($definition);
                $fieldValue->setEntityId($entity->getId());
            }

            $fieldValue->setValue($value !== null ? (string) $value : null);
            $this->valueRepository->save($fieldValue);
        }
    }
}
