<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupère l'option 'is_owner' passée dans le contrôleur
        $isOwner = $options['is_owner'];

        if ($isOwner) {
        $builder
            
            ->add('profile_picture', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'profile-picture-input'],
                'label_attr' => ['class' => 'profile-picture-label'],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                    ])
                ],
                
            ])
            ->add('background_image', FileType::class, [
                'label' => 'Background Image',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'background-image-input'],
                'label_attr' => ['class' => 'background-image-label'],
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                    ])
                ],
                
            ]);
        }
        $builder
            ->add('who_am_i', TextareaType::class, [
                'label' => 'Who i am ?',
                'label_attr' => ['class' => 'who-i-am-label'],
                'attr' =>[
                'class' => 'who-am-i',
                'placeholder' => 'Describe yourself', // Optionnel
                'rows' => 5, 
                'readonly' => !$isOwner, // Si ce n'est pas le propriétaire, on rend en lecture seule
                ],
                
            ])
            ->add('objectives', TextareaType::class, [
                'label' => 'Goals',
                'label_attr' => ['class' => 'goals-label'],
                'attr' =>[
                'class' => 'goals-profile',
                'placeholder' => 'What are your goals', // Optionnel
                'rows' => 5, 
                'readonly' => !$isOwner, // Si ce n'est pas le propriétaire, on rend en lecture seule
            ],
            ])
            ->add('experience', TextareaType::class, [
                'label' => 'Experience',
                'label_attr' => ['class' => 'experience-label'],
                'attr' =>[
                'class' => 'experience-profile',
                'placeholder' => 'Your experience...', // Optionnel
                'rows' => 5, 
                'readonly' => !$isOwner, // Si ce n'est pas le propriétaire, on rend en lecture seule
            ],
            ])
            ->add('qualities', TextareaType::class, [
                'label' => 'Qualities',
                'label_attr' => ['class' => 'qualities-label'],
                'attr' =>[
                'class' => 'qualities-profile',
                'placeholder' => 'What are your qualities?', // Optionnel
                'rows' => 5, 
                'readonly' => !$isOwner, // Si ce n'est pas le propriétaire, on rend en lecture seule
            ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'is_owner' => false,  // Déclare l'option is_owner avec une valeur par défaut (false)
        ]);
    }
}
