<?php

namespace KimaiPlugin\MidnightTaskBundle\Controller;

use App\Controller\AbstractController;
use KimaiPlugin\MidnightTaskBundle\Entity\Task;
use KimaiPlugin\MidnightTaskBundle\Form\TaskType;
use KimaiPlugin\MidnightTaskBundle\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tasks')]
#[IsGranted('ROLE_USER')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
    ) {
    }

    #[Route('/', name: 'task_index', methods: ['GET'])]
    public function index(): Response
    {
        $grouped = $this->taskRepository->findAllGroupedByStatus();
        $overdue = $this->taskRepository->findOverdue();

        return $this->render('@MidnightTask/index.html.twig', [
            'grouped' => $grouped,
            'overdue_count' => count($overdue),
            'statuses' => Task::STATUSES,
        ]);
    }

    #[Route('/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->save($task);
            $this->flashSuccess('Task created successfully.');

            return $this->redirectToRoute('task_index');
        }

        return $this->render('@MidnightTask/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
            'is_new' => true,
        ]);
    }

    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->save($task);
            $this->flashSuccess('Task updated successfully.');

            return $this->redirectToRoute('task_index');
        }

        return $this->render('@MidnightTask/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
            'is_new' => false,
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'task_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, Task $task, string $status): Response
    {
        if (in_array($status, Task::STATUSES, true)) {
            $task->setStatus($status);
            $this->taskRepository->save($task);
            $this->flashSuccess('Task status updated to "' . $status . '".');
        }

        return $this->redirectToRoute('task_index');
    }

    #[Route('/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $this->taskRepository->remove($task);
            $this->flashSuccess('Task deleted successfully.');
        }

        return $this->redirectToRoute('task_index');
    }
}
