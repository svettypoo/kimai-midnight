<?php

namespace KimaiPlugin\MidnightHRBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use KimaiPlugin\MidnightHRBundle\Repository\AbsenceRepository;

#[ORM\Entity(repositoryClass: AbsenceRepository::class)]
#[ORM\Table(name: 'kimai2_hr_absences')]
#[ORM\Index(columns: ['user_id'], name: 'idx_hr_absence_user')]
#[ORM\Index(columns: ['type'], name: 'idx_hr_absence_type')]
#[ORM\Index(columns: ['status'], name: 'idx_hr_absence_status')]
#[ORM\Index(columns: ['start_date', 'end_date'], name: 'idx_hr_absence_dates')]
class Absence
{
    public const TYPE_VACATION = 'vacation';
    public const TYPE_SICK = 'sick';
    public const TYPE_PUBLIC_HOLIDAY = 'public_holiday';
    public const TYPE_UNPAID = 'unpaid';
    public const TYPE_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DENIED = 'denied';

    public const TYPES = [
        self::TYPE_VACATION,
        self::TYPE_SICK,
        self::TYPE_PUBLIC_HOLIDAY,
        self::TYPE_UNPAID,
        self::TYPE_OTHER,
    ];

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_DENIED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 32)]
    private string $type;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $endDate;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $halfDay = false;

    #[ORM\Column(type: 'string', length: 16, options: ['default' => 'pending'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $approvedBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function isHalfDay(): bool
    {
        return $this->halfDay;
    }

    public function setHalfDay(bool $halfDay): self
    {
        $this->halfDay = $halfDay;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): self
    {
        $this->approvedBy = $approvedBy;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Calculate business days for this absence (excluding weekends).
     */
    public function getBusinessDays(): float
    {
        $start = new \DateTime($this->startDate->format('Y-m-d'));
        $end = new \DateTime($this->endDate->format('Y-m-d'));
        $days = 0;

        while ($start <= $end) {
            $dayOfWeek = (int) $start->format('N');
            if ($dayOfWeek <= 5) {
                $days++;
            }
            $start->modify('+1 day');
        }

        if ($this->halfDay && $days > 0) {
            $days -= 0.5;
        }

        return $days;
    }
}
