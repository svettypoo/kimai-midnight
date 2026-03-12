<?php

namespace KimaiPlugin\MidnightKioskBundle\Controller;

use App\Entity\User;
use App\Repository\TimesheetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/kiosk')]
class KioskController extends AbstractController
{
    #[Route(path: '', name: 'kiosk_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('@MidnightKiosk/kiosk.html.twig');
    }

    #[Route(path: '/verify-pin', name: 'kiosk_verify_pin', methods: ['POST'])]
    public function verifyPin(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pin = $data['pin'] ?? '';

        if (empty($pin) || strlen($pin) < 4) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid PIN'], 400);
        }

        // Look up user by account number (used as PIN).
        // In a real deployment, you would store a dedicated kiosk PIN on the user entity.
        $user = $userRepository->findOneBy(['accountNumber' => $pin]);

        if (!$user || !$user->isEnabled()) {
            return new JsonResponse(['success' => false, 'message' => 'User not found'], 404);
        }

        return new JsonResponse([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getDisplayName(),
                'alias' => $user->getAlias(),
            ],
        ]);
    }

    #[Route(path: '/status/{userId}', name: 'kiosk_status', methods: ['GET'])]
    public function status(int $userId, TimesheetRepository $timesheetRepository, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $activeEntry = $timesheetRepository->findOneBy([
            'user' => $user,
            'end' => null,
        ], ['begin' => 'DESC']);

        if ($activeEntry) {
            return new JsonResponse([
                'clockedIn' => true,
                'since' => $activeEntry->getBegin()->format('H:i'),
                'duration' => (new \DateTime())->getTimestamp() - $activeEntry->getBegin()->getTimestamp(),
            ]);
        }

        return new JsonResponse([
            'clockedIn' => false,
        ]);
    }

    #[Route(path: '/clock-in', name: 'kiosk_clock_in', methods: ['POST'])]
    public function clockIn(
        Request $request,
        UserRepository $userRepository,
        TimesheetRepository $timesheetRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? null;

        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Check if already clocked in
        $activeEntry = $timesheetRepository->findOneBy([
            'user' => $user,
            'end' => null,
        ]);

        if ($activeEntry) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Already clocked in since ' . $activeEntry->getBegin()->format('H:i'),
            ], 409);
        }

        // Create a new timesheet entry
        $timesheet = new \App\Entity\Timesheet();
        $timesheet->setUser($user);
        $timesheet->setBegin(new \DateTime());

        // Use the first available activity/project or leave blank
        $em->persist($timesheet);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Clocked in at ' . (new \DateTime())->format('H:i'),
        ]);
    }

    #[Route(path: '/clock-out', name: 'kiosk_clock_out', methods: ['POST'])]
    public function clockOut(
        Request $request,
        UserRepository $userRepository,
        TimesheetRepository $timesheetRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? null;

        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $activeEntry = $timesheetRepository->findOneBy([
            'user' => $user,
            'end' => null,
        ], ['begin' => 'DESC']);

        if (!$activeEntry) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Not currently clocked in',
            ], 409);
        }

        $activeEntry->setEnd(new \DateTime());
        $duration = $activeEntry->getEnd()->getTimestamp() - $activeEntry->getBegin()->getTimestamp();
        $activeEntry->setDuration($duration);
        $em->flush();

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);

        return new JsonResponse([
            'success' => true,
            'message' => sprintf('Clocked out. Worked %dh %dm', $hours, $minutes),
        ]);
    }
}
