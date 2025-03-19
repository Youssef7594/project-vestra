<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
                'invalid_message' => 'The password fields must match.',
            ])

            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'Recruteur' => 'RECRUTEUR',
                    'Talent' => 'TALENT',
                    
                ],
                'multiple' => false,  // Pour que l'utilisateur puisse choisir plusieurs rôles
                'expanded' => true,   // Affiche les choix sous forme de boutons radio
                'label' => 'Select Role',
                'attr' => ['class' => 'form-check-input'],  // Pour appliquer des classes Bootstrap aux boutons radio
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}