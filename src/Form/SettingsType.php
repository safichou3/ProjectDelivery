<?php
namespace App\Form;

use App\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tax', NumberType::class, [
                'label' => 'Tax Percentage',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Tax is required']),
                    new PositiveOrZero(['message' => 'Tax cannot be negative']),
                ],
            ])
            ->add('discountCode', TextType::class, [
                'label' => 'Discount Code',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter discount code (e.g., SAVE20)',
                    'class' => 'form-control'
                ],
            ])
            ->add('discountPercentage', NumberType::class, [
                'label' => 'Discount Percentage',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter discount percentage (e.g., 20 for 20%)',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 100,
                        'minMessage' => 'Discount percentage must be at least 0%',
                        'maxMessage' => 'Discount percentage cannot exceed 100%'
                    ]),
                ],
            ])
            ->add('discountActive', CheckboxType::class, [
                'label' => 'Activate Discount',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Settings::class]);
    }
}
