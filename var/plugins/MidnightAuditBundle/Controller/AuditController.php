<?php

namespace KimaiPlugin\MidnightAuditBundle\Controller;

use App\Controller\AbstractController;
use KimaiPlugin\MidnightAuditBundle\Repository\AuditLogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/audit')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class AuditController extends AbstractController
{
    #[Route(path: '/', name: 'midnight_audit_index', methods: ['GET'])]
    public function index(Request $request, AuditLogRepository $repository): Response
    {
        $entityType = $request->query->get('entity_type');
        $userId = $request->query->get('user_id');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');
        $limit = min((int) ($request->query->get('limit', 100)), 500);

        $dateFromObj = null;
        $dateToObj = null;

        if (!empty($dateFrom)) {
            try {
                $dateFromObj = new \DateTimeImmutable($dateFrom . ' 00:00:00');
            } catch (\Exception) {
                // ignore invalid date
            }
        }

        if (!empty($dateTo)) {
            try {
                $dateToObj = new \DateTimeImmutable($dateTo . ' 23:59:59');
            } catch (\Exception) {
                // ignore invalid date
            }
        }

        $logs = $repository->findRecent(
            $limit,
            $entityType ?: null,
            $userId ? (int) $userId : null,
            $dateFromObj,
            $dateToObj
        );

        $entityTypes = ['Timesheet', 'Customer', 'Project', 'Activity', 'User'];

        return $this->render('@MidnightAudit/index.html.twig', [
            'logs' => $logs,
            'entity_types' => $entityTypes,
            'filter_entity_type' => $entityType,
            'filter_user_id' => $userId,
            'filter_date_from' => $dateFrom,
            'filter_date_to' => $dateTo,
            'filter_limit' => $limit,
        ]);
    }
}
