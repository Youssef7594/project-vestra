<?php
namespace App\Form;

use App\Entity\Projects;
use App\Repository\CategoriesRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    private $categoryRepo;

    public function __construct(CategoriesRepository $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer les catégories depuis la base de données
        $categories = $this->categoryRepo->findAll();
        $choices = [];
        foreach ($categories as $category) {
        $choices[$category->getName()] = $category->getId(); // Utilisation de l'ID de la catégorie
    }
    
        $builder
            ->add('title', TextType::class, ['label' => 'Project Title'])
            ->add('description', TextareaType::class, ['label' => 'Project Description'])
            
            // Modifier ce champ pour utiliser un `ChoiceType`
            ->add('category_id', ChoiceType::class, [
            'choices' => $choices,
            'label' => 'Category',
            'placeholder' => 'Choose a category',
            'expanded' => false,
            'multiple' => false,
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projects::class,
            'csrf_protection' => false, 
        ]);
    }
    
}

