<?php

namespace KimaiPlugin\MidnightExpenseBundle\Controller;

use App\Controller\AbstractController;
use KimaiPlugin\MidnightExpenseBundle\Entity\Expense;
use KimaiPlugin\MidnightExpenseBundle\Form\ExpenseType;
use KimaiPlugin\MidnightExpenseBundle\Repository\ExpenseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/expenses')]
#[IsGranted('ROLE_ADMIN')]
class ExpenseController extends AbstractController
{
    public function __construct(
        private readonly ExpenseRepository $expenseRepository,
    ) {
    }

    #[Route('/', name: 'expense_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $expenses = $this->expenseRepository->findBy([], ['expenseDate' => 'DESC']);

        $total = 0.0;
        foreach ($expenses as $expense) {
            $total += (float) $expense->getAmount();
        }

        return $this->render('@MidnightExpense/index.html.twig', [
            'expenses' => $expenses,
            'total' => $total,
        ]);
    }

    #[Route('/create', name: 'expense_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $expense = new Expense();
        $expense->setUser($this->getUser());
        $expense->setExpenseDate(new \DateTime());

        $form = $this->createForm(ExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->expenseRepository->save($expense);
            $this->flashSuccess('Expense created successfully.');

            return $this->redirectToRoute('expense_index');
        }

        return $this->render('@MidnightExpense/edit.html.twig', [
            'form' => $form->createView(),
            'expense' => $expense,
            'is_new' => true,
        ]);
    }

    #[Route('/{id}/edit', name: 'expense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Expense $expense): Response
    {
        $form = $this->createForm(ExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->expenseRepository->save($expense);
            $this->flashSuccess('Expense updated successfully.');

            return $this->redirectToRoute('expense_index');
        }

        return $this->render('@MidnightExpense/edit.html.twig', [
            'form' => $form->createView(),
            'expense' => $expense,
            'is_new' => false,
        ]);
    }

    #[Route('/{id}/delete', name: 'expense_delete', methods: ['POST'])]
    public function delete(Request $request, Expense $expense): Response
    {
        if ($this->isCsrfTokenValid('delete' . $expense->getId(), $request->request->get('_token'))) {
            $this->expenseRepository->remove($expense);
            $this->flashSuccess('Expense deleted successfully.');
        }

        return $this->redirectToRoute('expense_index');
    }
}
