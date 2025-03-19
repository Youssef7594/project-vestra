<?php

// src/Form/ReportType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Report;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'choices' => [
                    'Contenu heurtant la sensibilité' => 'sensitive_content',
                    'Contenu à caractère sexuel' => 'sexual_content',
                    'Maltraitance' => 'abuse',
                    'Vol de contenu' => 'theft',
                    'Information incorrecte' => 'incorrect_info',
                ],
                'label' => 'Motif du signalement',
                
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}
