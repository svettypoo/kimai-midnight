<?php

namespace KimaiPlugin\MidnightBudgetBundle\EventSubscriber;

use App\Event\TimesheetCreatePostEvent;
use App\Event\TimesheetUpdatePostEvent;
use App\Repository\TimesheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use KimaiPlugin\MidnightBudgetBundle\Entity\BudgetAlert;
use KimaiPlugin\MidnightBudgetBundle\Repository\BudgetAlertRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BudgetSubscriber implements EventSubscriberInterface
{
    private const THRESHOLDS = [50, 75, 90, 100];

    public function __construct(
        private readonly TimesheetRepository $timesheetRepository,
        private readonly BudgetAlertRepository $budgetAlertRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TimesheetCreatePostEvent::class => 'onTimesheetChange',
            TimesheetUpdatePostEvent::class => 'onTimesheetChange',
        ];
    }

    public function onTimesheetChange(TimesheetCreatePostEvent|TimesheetUpdatePostEvent $event): void
    {
        $timesheet = $event->getTimesheet();
        $project = $timesheet->getProject();

        if ($project === null) {
            return;
        }

        $budget = $project->getTimeBudget(); // time budget in seconds
        if ($budget <= 0) {
            return;
        }

        // Calculate total time spent on this project
        $totalSeconds = $this->getTotalProjectTime($project);

        $percentUsed = ($totalSeconds / $budget) * 100;

        foreach (self::THRESHOLDS as $threshold) {
            if ($percentUsed >= $threshold) {
                $this->createAlertIfNotExists($project, $threshold);
            }
        }
    }

    private function getTotalProjectTime($project): int
    {
        $result = $this->timesheetRepository->createQueryBuilder('t')
            ->select('SUM(t.duration)')
            ->where('t.project = :project')
            ->andWhere('t.end IS NOT NULL')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    private function createAlertIfNotExists($project, int $threshold): void
    {
        $existing = $this->budgetAlertRepository->findByProjectAndThreshold($project, $threshold);

        if ($existing !== null) {
            return;
        }

        $alert = new BudgetAlert();
        $alert->setProject($project);
        $alert->setThresholdPercent($threshold);
        $alert->setTriggeredAt(new \DateTimeImmutable());

        $this->em->persist($alert);
        $this->em->flush();
    }
}
