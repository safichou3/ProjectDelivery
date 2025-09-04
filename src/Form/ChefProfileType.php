<?php

namespace App\Form;

use App\Entity\ChefProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ChefProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Bio is required.']),
                ],
            ])
            ->add('certification', TextareaType::class, [
                'label' => 'Certification',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Certification is required.']),
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $profile = $event->getData();
            $form = $event->getForm();
            $form->add('image', FileType::class, [
                'label' => 'Profile Image (jpg/png/webp)',
                'mapped' => false,
                'required' => !$profile || !$profile->getImage(),
                'constraints' => !$profile || !$profile->getImage() ? [
                    new NotBlank(['message' => 'Profile image is required.']),
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Image is too large. Maximum allowed size is 2MB.',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG, PNG, WebP)',
                    ]),
                ] : [],
            ]);
            $form->add('licence', FileType::class, [
                'label' => 'Licence Image (jpg/png/webp)',
                'mapped' => false,
                'required' => !$profile || !$profile->getLicence(),
                'constraints' => !$profile || !$profile->getLicence() ? [
                    new NotBlank(['message' => 'Licence image is required.']),
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Image is too large. Maximum allowed size is 2MB.',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG, PNG, WebP)',
                    ]),
                ] : [],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChefProfile::class,
        ]);
    }
}
