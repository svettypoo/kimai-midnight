<?php

namespace KimaiPlugin\MidnightHRBundle\Controller;

use App\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use KimaiPlugin\MidnightHRBundle\Entity\Absence;
use KimaiPlugin\MidnightHRBundle\Form\AbsenceType;
use KimaiPlugin\MidnightHRBundle\Repository\AbsenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/hr/absences')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class AbsenceController extends AbstractController
{
    #[Route(path: '', name: 'hr_absences', methods: ['GET'])]
    public function index(AbsenceRepository $absenceRepository): Response
    {
        $user = $this->getUser();
        $absences = $absenceRepository->findByUser($user);

        return $this->render('@MidnightHR/absences.html.twig', [
            'absences' => $absences,
        ]);
    }

    #[Route(path: '/new', name: 'hr_absence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $absence = new Absence();
        $absence->setUser($this->getUser());

        $form = $this->createForm(AbsenceType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($absence);
            $em->flush();

            $this->addFlash('success', 'Absence request submitted successfully.');
            return $this->redirectToRoute('hr_absences');
        }

        return $this->render('@MidnightHR/edit.html.twig', [
            'form' => $form->createView(),
            'absence' => $absence,
            'isNew' => true,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'hr_absence_edit', methods: ['GET', 'POST'])]
    public function edit(Absence $absence, Request $request, EntityManagerInterface $em): Response
    {
        // Only allow editing pending absences owned by the current user
        if ($absence->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own absences.');
        }

        if ($absence->getStatus() !== Absence::STATUS_PENDING) {
            $this->addFlash('warning', 'Only pending absences can be edited.');
            return $this->redirectToRoute('hr_absences');
        }

        $form = $this->createForm(AbsenceType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Absence request updated.');
            return $this->redirectToRoute('hr_absences');
        }

        return $this->render('@MidnightHR/edit.html.twig', [
            'form' => $form->createView(),
            'absence' => $absence,
            'isNew' => false,
        ]);
    }

    #[Route(path: '/{id}/approve', name: 'hr_absence_approve', methods: ['POST'])]
    #[IsGranted('ROLE_TEAMLEAD')]
    public function approve(Absence $absence, EntityManagerInterface $em): Response
    {
        $absence->setStatus(Absence::STATUS_APPROVED);
        $absence->setApprovedBy($this->getUser());
        $em->flush();

        $this->addFlash('success', 'Absence approved.');
        return $this->redirectToRoute('hr_dashboard');
    }

    #[Route(path: '/{id}/deny', name: 'hr_absence_deny', methods: ['POST'])]
    #[IsGranted('ROLE_TEAMLEAD')]
    public function deny(Absence $absence, EntityManagerInterface $em): Response
    {
        $absence->setStatus(Absence::STATUS_DENIED);
        $absence->setApprovedBy($this->getUser());
        $em->flush();

        $this->addFlash('warning', 'Absence denied.');
        return $this->redirectToRoute('hr_dashboard');
    }

    #[Route(path: '/{id}/delete', name: 'hr_absence_delete', methods: ['POST'])]
    public function delete(Absence $absence, EntityManagerInterface $em): Response
    {
        if ($absence->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own absences.');
        }

        if ($absence->getStatus() !== Absence::STATUS_PENDING) {
            $this->addFlash('warning', 'Only pending absences can be deleted.');
            return $this->redirectToRoute('hr_absences');
        }

        $em->remove($absence);
        $em->flush();

        $this->addFlash('success', 'Absence request deleted.');
        return $this->redirectToRoute('hr_absences');
    }
}
