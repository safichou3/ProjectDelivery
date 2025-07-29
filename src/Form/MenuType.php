<?php

namespace App\Form;

use App\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;


class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('price', MoneyType::class, [
                'currency' => 'Euro',
            ])
            ->add('image', FileType::class, [
                'label' => 'Menu Image (jpg/png/webp)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Image is required.',
                    ]),
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPG, PNG, or WEBP image.',
                    ])
                ],
            ])
            ->add('availableFrom', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Available From'
            ])
            ->add('availableTo', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Available To'
            ])
            ->add('cuisineType', ChoiceType::class, [
                'choices' => [
                    'Pakistani' => 'Pakistani',
                    'Indian' => 'Indian',
                    'Chinese' => 'Chinese',
                    'Italian' => 'Italian',
                    'Other' => 'Other',
                ],
                'placeholder' => 'Choose a Cuisine Type',
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Activate this menu?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
