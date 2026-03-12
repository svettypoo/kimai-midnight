<?php

namespace KimaiPlugin\MidnightHRBundle\Form;

use KimaiPlugin\MidnightHRBundle\Entity\Absence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Absence Type',
                'choices' => [
                    'Vacation' => Absence::TYPE_VACATION,
                    'Sick Leave' => Absence::TYPE_SICK,
                    'Public Holiday' => Absence::TYPE_PUBLIC_HOLIDAY,
                    'Unpaid Leave' => Absence::TYPE_UNPAID,
                    'Other' => Absence::TYPE_OTHER,
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endDate', DateType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('halfDay', CheckboxType::class, [
                'label' => 'Half Day',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Optional notes or reason...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Absence::class,
        ]);
    }
}
