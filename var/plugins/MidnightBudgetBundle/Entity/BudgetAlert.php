<?php

namespace KimaiPlugin\MidnightBudgetBundle\Entity;

use App\Entity\Project;
use Doctrine\ORM\Mapping as ORM;
use KimaiPlugin\MidnightBudgetBundle\Repository\BudgetAlertRepository;

#[ORM\Entity(repositoryClass: BudgetAlertRepository::class)]
#[ORM\Table(name: 'kimai2_budget_alerts')]
#[ORM\Index(columns: ['project_id'], name: 'idx_budget_alert_project')]
#[ORM\Index(columns: ['threshold_percent'], name: 'idx_budget_alert_threshold')]
#[ORM\UniqueConstraint(columns: ['project_id', 'threshold_percent'], name: 'uniq_project_threshold')]
class BudgetAlert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\Column(type: 'integer')]
    private int $thresholdPercent;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $triggeredAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $notified = false;

    public function __construct()
    {
        $this->triggeredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getThresholdPercent(): int
    {
        return $this->thresholdPercent;
    }

    public function setThresholdPercent(int $thresholdPercent): self
    {
        $this->thresholdPercent = $thresholdPercent;
        return $this;
    }

    public function getTriggeredAt(): \DateTimeImmutable
    {
        return $this->triggeredAt;
    }

    public function setTriggeredAt(\DateTimeImmutable $triggeredAt): self
    {
        $this->triggeredAt = $triggeredAt;
        return $this;
    }

    public function isNotified(): bool
    {
        return $this->notified;
    }

    public function setNotified(bool $notified): self
    {
        $this->notified = $notified;
        return $this;
    }
}
