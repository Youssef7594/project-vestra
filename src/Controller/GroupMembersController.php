<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GroupMembersController extends AbstractController
{
    #[Route('/group/members', name: 'app_group_members')]
    public function index(): Response
    {
        return $this->render('group_members/index.html.twig', [
            'controller_name' => 'GroupMembersController',
        ]);
    }
}
