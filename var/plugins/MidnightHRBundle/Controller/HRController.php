<?php

namespace KimaiPlugin\MidnightHRBundle\Controller;

use App\Controller\AbstractController;
use KimaiPlugin\MidnightHRBundle\Repository\AbsenceRepository;
use KimaiPlugin\MidnightHRBundle\Repository\ContractRepository;
use KimaiPlugin\MidnightHRBundle\Repository\PublicHolidayRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/hr')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class HRController extends AbstractController
{
    #[Route(path: '/dashboard', name: 'hr_dashboard', methods: ['GET'])]
    public function dashboard(
        ContractRepository $contractRepository,
        AbsenceRepository $absenceRepository,
        PublicHolidayRepository $holidayRepository
    ): Response {
        $user = $this->getUser();
        $year = (int) date('Y');

        $contract = $contractRepository->findActiveForUser($user);
        $vacationUsed = $absenceRepository->countVacationDaysUsed($user, $year);
        $sickDaysUsed = $absenceRepository->countSickDaysUsed($user, $year);
        $vacationTotal = $contract ? $contract->getVacationDaysPerYear() : 0;
        $vacationRemaining = $vacationTotal - $vacationUsed;

        $pendingAbsences = $absenceRepository->findByStatus('pending');
        $recentAbsences = $absenceRepository->findByUser($user, $year);
        $upcomingHolidays = $holidayRepository->findUpcoming(5);

        // Calendar data: absences for current month
        $monthStart = new \DateTime('first day of this month');
        $monthEnd = new \DateTime('last day of this month');
        $calendarAbsences = $absenceRepository->findByDateRange($monthStart, $monthEnd);

        return $this->render('@MidnightHR/dashboard.html.twig', [
            'contract' => $contract,
            'vacationUsed' => $vacationUsed,
            'vacationTotal' => $vacationTotal,
            'vacationRemaining' => $vacationRemaining,
            'sickDaysUsed' => $sickDaysUsed,
            'pendingAbsences' => $pendingAbsences,
            'recentAbsences' => $recentAbsences,
            'upcomingHolidays' => $upcomingHolidays,
            'calendarAbsences' => $calendarAbsences,
            'year' => $year,
        ]);
    }
}
