<?php

namespace KimaiPlugin\MidnightExpenseBundle\Form;

use App\Entity\Customer;
use App\Entity\Project;
use KimaiPlugin\MidnightExpenseBundle\Entity\Expense;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'currency' => 'CAD',
                'label' => 'Amount',
                'required' => true,
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => [
                    'CAD' => 'CAD',
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                    'GBP' => 'GBP',
                ],
                'label' => 'Currency',
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Travel' => 'travel',
                    'Meals' => 'meals',
                    'Office Supplies' => 'office_supplies',
                    'Software' => 'software',
                    'Hardware' => 'hardware',
                    'Communication' => 'communication',
                    'Marketing' => 'marketing',
                    'Training' => 'training',
                    'Other' => 'other',
                ],
                'label' => 'Category',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('expenseDate', DateTimeType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'label' => 'Project',
                'required' => false,
                'placeholder' => '-- Select Project --',
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'name',
                'label' => 'Customer',
                'required' => false,
                'placeholder' => '-- Select Customer --',
            ])
            ->add('receiptPath', TextType::class, [
                'label' => 'Receipt Path',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
        ]);
    }
}
