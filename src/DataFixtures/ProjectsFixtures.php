<?php

namespace App\DataFixtures;

use App\Entity\Projects;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProjectsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Utilisation de Faker pour générer des données fictives
        $faker = Factory::create();

        // Boucle pour créer 10 projets fictifs
        for ($i = 0; $i < 10; $i++) {
            // Créer un nouveau projet
            $project = new Projects();
            
            // Générer un titre aléatoire
            $title = $faker->sentence(3);  // Par exemple: "My Awesome Project"
            
            // Créer un slug en remplaçant les espaces par des tirets et en mettant tout en minuscule
            $slug = strtolower(str_replace(' ', '-', $title));  // Exemple de slug: "my-awesome-project"

            // Assigner le titre et le slug au projet
            $project->setTitle($title);
            $project->setSlug($slug);  // Assigner le slug
            
            // Autres informations pour le projet
            $project->setDescription($faker->paragraph(5));  // Description aléatoire de 5 paragraphes
            $project->setCategoryId($faker->numberBetween(1, 5));  // Catégorie aléatoire (id entre 1 et 5)
            $project->setUserId($faker->numberBetween(1, 10));  // Utilisateur aléatoire (id entre 1 et 10)
            $project->setMediaUrl($faker->imageUrl(640, 480, 'business', true));  // URL d'image aléatoire
            $project->setImages([  // Liste d'images
                $faker->imageUrl(640, 480, 'business', true),
                $faker->imageUrl(640, 480, 'business', true)
            ]);
            $project->setCreatedAt(new \DateTimeImmutable());  // Date de création
            $project->setUpdatedAt(new \DateTime());  // Date de mise à jour

            // Persister l'entité dans le gestionnaire
            $manager->persist($project);
        }

        // Appliquer les changements en base de données
        $manager->flush();
    }
}
