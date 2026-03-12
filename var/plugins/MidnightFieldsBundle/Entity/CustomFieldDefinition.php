<?php

namespace KimaiPlugin\MidnightFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use KimaiPlugin\MidnightFieldsBundle\Repository\CustomFieldDefinitionRepository;

#[ORM\Entity(repositoryClass: CustomFieldDefinitionRepository::class)]
#[ORM\Table(name: 'midnight_custom_field_definition')]
#[ORM\HasLifecycleCallbacks]
class CustomFieldDefinition
{
    public const ENTITY_TYPES = ['timesheet', 'customer', 'project', 'activity'];

    public const FIELD_TYPES = [
        'text',
        'number',
        'dropdown',
        'checkbox',
        'date',
        'color',
        'textarea',
        'email',
        'url',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $entity_type;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $field_key;

    #[ORM\Column(type: 'string', length: 20)]
    private string $field_type = 'text';

    #[ORM\Column(type: 'boolean')]
    private bool $required = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $options = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $visible = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created_at;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityType(): string
    {
        return $this->entity_type;
    }

    public function setEntityType(string $entity_type): self
    {
        if (!in_array($entity_type, self::ENTITY_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid entity type "%s". Allowed: %s', $entity_type, implode(', ', self::ENTITY_TYPES)));
        }
        $this->entity_type = $entity_type;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getFieldKey(): string
    {
        return $this->field_key;
    }

    public function setFieldKey(string $field_key): self
    {
        $this->field_key = $field_key;
        return $this;
    }

    public function getFieldType(): string
    {
        return $this->field_type;
    }

    public function setFieldType(string $field_type): self
    {
        if (!in_array($field_type, self::FIELD_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid field type "%s". Allowed: %s', $field_type, implode(', ', self::FIELD_TYPES)));
        }
        $this->field_type = $field_type;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }
}
