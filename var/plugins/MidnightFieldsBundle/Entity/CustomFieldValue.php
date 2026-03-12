<?php

namespace KimaiPlugin\MidnightFieldsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use KimaiPlugin\MidnightFieldsBundle\Repository\CustomFieldValueRepository;

#[ORM\Entity(repositoryClass: CustomFieldValueRepository::class)]
#[ORM\Table(name: 'midnight_custom_field_value')]
#[ORM\UniqueConstraint(name: 'unique_field_entity', columns: ['definition_id', 'entity_id'])]
#[ORM\HasLifecycleCallbacks]
class CustomFieldValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CustomFieldDefinition::class)]
    #[ORM\JoinColumn(name: 'definition_id', nullable: false, onDelete: 'CASCADE')]
    private CustomFieldDefinition $definition;

    #[ORM\Column(type: 'integer')]
    private int $entity_id;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDefinition(): CustomFieldDefinition
    {
        return $this->definition;
    }

    public function setDefinition(CustomFieldDefinition $definition): self
    {
        $this->definition = $definition;
        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entity_id;
    }

    public function setEntityId(int $entity_id): self
    {
        $this->entity_id = $entity_id;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }
}
