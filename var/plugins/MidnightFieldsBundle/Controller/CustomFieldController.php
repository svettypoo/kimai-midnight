<?php

namespace KimaiPlugin\MidnightFieldsBundle\Controller;

use App\Controller\AbstractController;
use KimaiPlugin\MidnightFieldsBundle\Entity\CustomFieldDefinition;
use KimaiPlugin\MidnightFieldsBundle\Form\CustomFieldDefinitionType;
use KimaiPlugin\MidnightFieldsBundle\Repository\CustomFieldDefinitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/midnight-fields')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class CustomFieldController extends AbstractController
{
    public function __construct(
        private readonly CustomFieldDefinitionRepository $definitionRepository,
    ) {
    }

    #[Route(path: '', name: 'midnight_fields_index', methods: ['GET'])]
    public function index(): Response
    {
        $grouped = $this->definitionRepository->findAllGroupedByEntityType();

        return $this->render('@MidnightFields/index.html.twig', [
            'grouped' => $grouped,
            'entity_types' => CustomFieldDefinition::ENTITY_TYPES,
        ]);
    }

    #[Route(path: '/create', name: 'midnight_fields_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $definition = new CustomFieldDefinition();

        // Pre-select entity type from query parameter
        if ($request->query->has('entity_type')) {
            $entityType = $request->query->get('entity_type');
            if (in_array($entityType, CustomFieldDefinition::ENTITY_TYPES, true)) {
                $definition->setEntityType($entityType);
            }
        }

        $form = $this->createForm(CustomFieldDefinitionType::class, $definition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->definitionRepository->save($definition);

            $this->flashSuccess('Custom field created successfully.');
            return $this->redirectToRoute('midnight_fields_index');
        }

        return $this->render('@MidnightFields/edit.html.twig', [
            'form' => $form->createView(),
            'definition' => $definition,
            'is_new' => true,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'midnight_fields_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CustomFieldDefinition $definition): Response
    {
        $form = $this->createForm(CustomFieldDefinitionType::class, $definition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->definitionRepository->save($definition);

            $this->flashSuccess('Custom field updated successfully.');
            return $this->redirectToRoute('midnight_fields_index');
        }

        return $this->render('@MidnightFields/edit.html.twig', [
            'form' => $form->createView(),
            'definition' => $definition,
            'is_new' => false,
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'midnight_fields_delete', methods: ['POST'])]
    public function delete(Request $request, CustomFieldDefinition $definition): Response
    {
        if ($this->isCsrfTokenValid('delete_' . $definition->getId(), $request->request->get('_token'))) {
            $this->definitionRepository->remove($definition);
            $this->flashSuccess('Custom field deleted successfully.');
        }

        return $this->redirectToRoute('midnight_fields_index');
    }

    #[Route(path: '/reorder', name: 'midnight_fields_reorder', methods: ['POST'])]
    public function reorder(Request $request): Response
    {
        $positions = $request->request->all('positions');

        if (is_array($positions)) {
            $em = $this->definitionRepository->getEntityManager();
            foreach ($positions as $id => $position) {
                $definition = $this->definitionRepository->find((int) $id);
                if ($definition !== null) {
                    $definition->setPosition((int) $position);
                    $em->persist($definition);
                }
            }
            $em->flush();
        }

        $this->flashSuccess('Field order updated.');
        return $this->redirectToRoute('midnight_fields_index');
    }
}
