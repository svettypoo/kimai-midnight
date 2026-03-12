<?php

namespace KimaiPlugin\MidnightBudgetBundle\Controller;

use App\Controller\AbstractController;
use App\Repository\ProjectRepository;
use App\Repository\TimesheetRepository;
use KimaiPlugin\MidnightBudgetBundle\Repository\BudgetAlertRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/budget')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class BudgetController extends AbstractController
{
    #[Route(path: '', name: 'budget_dashboard', methods: ['GET'])]
    public function index(
        ProjectRepository $projectRepository,
        TimesheetRepository $timesheetRepository,
        BudgetAlertRepository $budgetAlertRepository
    ): Response {
        // Get all visible projects with a time budget
        $projects = $projectRepository->createQueryBuilder('p')
            ->where('p.visible = true')
            ->andWhere('p.timeBudget > 0')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        $projectBudgets = [];

        foreach ($projects as $project) {
            $budgetSeconds = $project->getTimeBudget();

            // Sum all durations for this project
            $usedSeconds = (int) ($timesheetRepository->createQueryBuilder('t')
                ->select('SUM(t.duration)')
                ->where('t.project = :project')
                ->andWhere('t.end IS NOT NULL')
                ->setParameter('project', $project)
                ->getQuery()
                ->getSingleScalarResult() ?? 0);

            $percentUsed = $budgetSeconds > 0 ? round(($usedSeconds / $budgetSeconds) * 100, 1) : 0;

            $alerts = $budgetAlertRepository->findByProject($project);

            $projectBudgets[] = [
                'project' => $project,
                'budgetHours' => round($budgetSeconds / 3600, 1),
                'usedHours' => round($usedSeconds / 3600, 1),
                'remainingHours' => round(max(0, $budgetSeconds - $usedSeconds) / 3600, 1),
                'percentUsed' => min($percentUsed, 100),
                'percentRaw' => $percentUsed,
                'overBudget' => $percentUsed > 100,
                'alerts' => $alerts,
            ];
        }

        // Sort: over-budget first, then by percent used descending
        usort($projectBudgets, function ($a, $b) {
            if ($a['overBudget'] !== $b['overBudget']) {
                return $b['overBudget'] <=> $a['overBudget'];
            }
            return $b['percentRaw'] <=> $a['percentRaw'];
        });

        $recentAlerts = $budgetAlertRepository->findRecent(20);

        return $this->render('@MidnightBudget/index.html.twig', [
            'projectBudgets' => $projectBudgets,
            'recentAlerts' => $recentAlerts,
        ]);
    }
}
