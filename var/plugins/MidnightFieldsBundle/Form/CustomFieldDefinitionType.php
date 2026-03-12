<?php

namespace KimaiPlugin\MidnightFieldsBundle\Form;

use KimaiPlugin\MidnightFieldsBundle\Entity\CustomFieldDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CustomFieldDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entity_type', ChoiceType::class, [
                'label' => 'Entity Type',
                'choices' => array_combine(
                    array_map('ucfirst', CustomFieldDefinition::ENTITY_TYPES),
                    CustomFieldDefinition::ENTITY_TYPES
                ),
                'placeholder' => '-- Select --',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('name', TextType::class, [
                'label' => 'Field Label',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ],
                'attr' => ['placeholder' => 'e.g. Invoice Number'],
            ])
            ->add('field_key', TextType::class, [
                'label' => 'Machine Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                    new Assert\Regex([
                        'pattern' => '/^[a-z0-9_]+$/',
                        'message' => 'Only lowercase letters, numbers, and underscores are allowed.',
                    ]),
                ],
                'attr' => ['placeholder' => 'e.g. invoice_number'],
            ])
            ->add('field_type', ChoiceType::class, [
                'label' => 'Field Type',
                'choices' => array_combine(
                    array_map('ucfirst', CustomFieldDefinition::FIELD_TYPES),
                    CustomFieldDefinition::FIELD_TYPES
                ),
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'Required',
                'required' => false,
            ])
            ->add('options', TextareaType::class, [
                'label' => 'Options (JSON)',
                'required' => false,
                'help' => 'For dropdown fields: ["Option A", "Option B", "Option C"]. Leave blank for other field types.',
                'attr' => [
                    'placeholder' => '["Option A", "Option B"]',
                    'rows' => 3,
                ],
                'getter' => function (CustomFieldDefinition $def): ?string {
                    $opts = $def->getOptions();
                    return $opts !== null ? json_encode($opts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null;
                },
                'setter' => function (CustomFieldDefinition $def, ?string $value): void {
                    if ($value === null || trim($value) === '') {
                        $def->setOptions(null);
                    } else {
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \InvalidArgumentException('Invalid JSON in options field.');
                        }
                        $def->setOptions($decoded);
                    }
                },
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Sort Order',
                'required' => false,
                'attr' => ['min' => 0],
            ])
            ->add('visible', CheckboxType::class, [
                'label' => 'Visible',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomFieldDefinition::class,
        ]);
    }
}
