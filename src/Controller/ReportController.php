<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Report;
use App\Form\ReportType;
use App\Repository\ProjectsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class ReportController extends AbstractController
{
    #[Route('/projects/{id}/report', name: 'app_report_project', methods: ['GET', 'POST'])]
    public function reportProject(int $id, Request $request, ProjectsRepository $projectsRepo, EntityManagerInterface $manager, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $project = $projectsRepo->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Projet introuvable.');
        }

        $report = new Report();
        $report->setProject($project);
        $report->setUser($user);

        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($report);
            $manager->flush();

            $this->addFlash('success', 'Votre signalement a été envoyé.');
            return $this->redirectToRoute('app_projects_project', ['projects' => $project->getSlug()]);
        }

        return $this->render('report/report_form.html.twig', [
            'form' => $form->createView(),
            'project' => $project
        ]);
    }
}

