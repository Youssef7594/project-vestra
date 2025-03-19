<?php

namespace App\Controller;

use App\Entity\Projects;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Shares;
use App\Repository\ProjectsRepository;
use App\Repository\SharesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class SharesController extends AbstractController
{
    #[Route('/share/{slug}', name: 'app_share_project')]
public function shareProject(string $slug, EntityManagerInterface $entityManager, Security $security): JsonResponse
{
    $user = $security->getUser();

    if (!$user) {
        return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté.'], 403);
    }

    $project = $entityManager->getRepository(Projects::class)->findOneBy(['slug' => $slug]);

    if (!$project) {
        return new JsonResponse(['success' => false, 'message' => 'Projet non trouvé.'], 404);
    }

    // 🔹 On enlève la vérification pour permettre les partages multiples
    $share = new Shares();
    $share->setUser($user);
    $share->setProject($project);
    $share->setCreatedAt(new \DateTime());

    $entityManager->persist($share);
    $entityManager->flush();


    return new JsonResponse(['success' => true, 'message' => 'Projet partagé avec succès !']);
}

}
