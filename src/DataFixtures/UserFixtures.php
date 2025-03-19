<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $user = new Users();
            
            $user->setUsername($faker->userName)
                ->setEmail($faker->email)
                ->setPassword(password_hash($faker->password, PASSWORD_BCRYPT))
                ->setProfilePicture('https://picsum.photos/640/480')
                ->setDescription($faker->paragraph)
                ->setObjectives($faker->paragraph)
                ->setRole($faker->randomElement(['recruteur', 'talent']))
                ->setCertified($faker->boolean)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTime());

            $manager->persist($user);
        }

        $manager->flush();
    }
}
