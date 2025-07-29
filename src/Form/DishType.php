<?php

namespace App\Form;

use App\Entity\Dish;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Menu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $chef = $options['chef'];
        $builder
            ->add('name', TextType::class, [
                'label' => 'Dish Name',
                'constraints' => [
                    new NotBlank(['message' => 'Dish name is required.']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Dish Image (jpg/png/webp)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Dish image is required.',
                    ]),
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG, PNG, WebP)',
                    ]),
                ],
            ])
            ->add('menu', EntityType::class, [
                'class' => Menu::class,
                'choice_label' => 'title',
                'label' => 'Select Menu',
                'placeholder' => 'Choose a menu',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a menu.']),
                ],
                'query_builder' => function (EntityRepository $er) use ($chef) {
                    return $er->createQueryBuilder('m')
                        ->where('m.chef = :chef')
                        ->setParameter('chef', $chef);
                },
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dish::class,
            'chef' => null,
        ]);

        $resolver->setAllowedTypes('chef', ['null', 'App\Entity\User']);
    }
}
