<?php

namespace KimaiPlugin\MidnightTaskBundle\Form;

use App\Entity\Project;
use App\Entity\User;
use KimaiPlugin\MidnightTaskBundle\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'required' => true,
                'attr' => ['placeholder' => 'Task title'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'To Do' => Task::STATUS_TODO,
                    'In Progress' => Task::STATUS_IN_PROGRESS,
                    'Done' => Task::STATUS_DONE,
                    'Blocked' => Task::STATUS_BLOCKED,
                ],
                'label' => 'Status',
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Low' => Task::PRIORITY_LOW,
                    'Medium' => Task::PRIORITY_MEDIUM,
                    'High' => Task::PRIORITY_HIGH,
                    'Urgent' => Task::PRIORITY_URGENT,
                ],
                'label' => 'Priority',
            ])
            ->add('assignee', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'displayName',
                'label' => 'Assignee',
                'required' => false,
                'placeholder' => '-- Unassigned --',
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'label' => 'Project',
                'required' => false,
                'placeholder' => '-- No Project --',
            ])
            ->add('estimatedHours', NumberType::class, [
                'label' => 'Estimated Hours',
                'required' => false,
                'scale' => 2,
                'attr' => ['placeholder' => '0.00'],
            ])
            ->add('dueDate', DateTimeType::class, [
                'label' => 'Due Date',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
