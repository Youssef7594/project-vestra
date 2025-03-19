<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoriesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste des noms de catégories à insérer
        $categories = [
            'Sport',
            'Science',
            'Art',
            'History',
            'literature',
            'Digital',
            'Nature',
            'Cinema',
            'Fashion',
            'Design',
            'Architecture and urban planning',
            'Cuisine',
            'Travel and adventure',
            'Video games',
            'Astrology',
            'Other',
        ];

        foreach ($categories as $categoryName) {
            $category = new Categories(); // Instancie une nouvelle catégorie
            $category->setName($categoryName); // Définit le nom

            $manager->persist($category); // Prépare l'insertion en base de données
        }

        $manager->flush(); // Envoie toutes les données dans la base
    }
}
