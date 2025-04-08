<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /* Cette page dirige vers la page "First-step" */
    #[Route('/First-step', name: 'app_first-step')]
    public function firstStep(): Response
    {
        return $this->render('home/first-step.html.twig', [
            
        ]);
    }

    /* Cette page dirige vers la page "Help" */
    #[Route('/Help', name: 'app_help-page')]
    public function helpPage(): Response
    {
        return $this->render('home/help.html.twig', [
            
        ]);
    }

     /* Cette page dirige vers la page "Event" */
    #[Route('/Event', name: 'app_event-page')]
    public function eventPage(): Response
    {
        return $this->render('home/event.html.twig', [
        ]);
    }

    /* route Privacy Policy (politique de confidentialiter) */
    #[Route('/rights', name: 'app_rights')]
    public function privacyPolicy(): Response
    {
        return $this->render('home/rights.html.twig', [
            
        ]);
    }

    
}
