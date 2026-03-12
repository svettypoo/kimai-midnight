<?php

namespace KimaiPlugin\MidnightHRBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use KimaiPlugin\MidnightHRBundle\Repository\ContractRepository;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
#[ORM\Table(name: 'kimai2_hr_contracts')]
#[ORM\Index(columns: ['user_id'], name: 'idx_hr_contract_user')]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $hoursPerWeek;

    #[ORM\Column(type: 'integer')]
    private int $vacationDaysPerYear;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

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

    public function getHoursPerWeek(): string
    {
        return $this->hoursPerWeek;
    }

    public function setHoursPerWeek(string $hoursPerWeek): self
    {
        $this->hoursPerWeek = $hoursPerWeek;
        return $this;
    }

    public function getVacationDaysPerYear(): int
    {
        return $this->vacationDaysPerYear;
    }

    public function setVacationDaysPerYear(int $vacationDaysPerYear): self
    {
        $this->vacationDaysPerYear = $vacationDaysPerYear;
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

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }
}
