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
        $menu = $options['data'] ?? null;
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'label' => 'Menu Image (jpg/png/webp)',
                'mapped' => false,
                'required' => !$menu || !$menu->getImage(),
                'constraints' => !$menu || !$menu->getImage() ? [
                    new NotBlank(['message' => 'Menu image is required.']),
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Image is too large. Maximum allowed size is 2MB.',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid JPG, PNG, or WEBP image.',
                    ])
                ] : [
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Image is too large. Maximum allowed size is 2MB.',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid JPG, PNG, or WEBP image.',
                    ])
                ],
            ])
            ->add('availableFrom', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Available From'
            ])
            ->add('availableTo', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Available To'
            ])
            ->add('cuisineType', ChoiceType::class, [
                'choices' => [
                    'Italian'   => 'Italian',
                    'French'    => 'French',
                    'Spanish'   => 'Spanish',
                    'Greek'     => 'Greek',
                    'Turkish'   => 'Turkish',
                    'German'    => 'German',
                    'British'   => 'British',
                    'American'  => 'American',
                    'Other'     => 'Other',
                ],
                'placeholder' => 'Choose a Cuisine Type',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Cuisine type is required.',
                    ]),
                ],
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
