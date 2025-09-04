<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Name is required.']),
                ],
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Email is required.']),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Phone number is required.']),
                ],
            ])
            ->add('address', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Address is required.']),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'admin',
                    'Chef' => 'chef',
                    'Client' => 'client',
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
