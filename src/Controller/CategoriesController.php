<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoriesController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function index(CategoriesRepository $repo): JsonResponse
    {

        $categories = $repo->findAll();
        $response = array_map(fn($category) => ['id' => $category->getId(), 'name' => $category->getName()], $categories);

    return $this->json($response);
        return $this->render('categories/index.html.twig', [
        ]);
    }
}
