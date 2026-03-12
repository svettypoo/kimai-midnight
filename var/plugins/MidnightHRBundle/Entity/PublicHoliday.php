<?php

namespace KimaiPlugin\MidnightHRBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use KimaiPlugin\MidnightHRBundle\Repository\PublicHolidayRepository;

#[ORM\Entity(repositoryClass: PublicHolidayRepository::class)]
#[ORM\Table(name: 'kimai2_hr_public_holidays')]
#[ORM\Index(columns: ['date'], name: 'idx_hr_holiday_date')]
#[ORM\Index(columns: ['country'], name: 'idx_hr_holiday_country')]
class PublicHoliday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 128)]
    private string $name;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'string', length: 5, options: ['default' => 'CA'])]
    private string $country = 'CA';

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $recurring = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    public function setRecurring(bool $recurring): self
    {
        $this->recurring = $recurring;
        return $this;
    }
}
