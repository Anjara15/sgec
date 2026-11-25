<?php

namespace App\Controller;

use App\Service\DashboardStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(DashboardStatsService $statsService): Response
    {
        $stats = $statsService->getStats();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('dashboard/admin.html.twig', ['stats' => $stats]);
        }

        if ($this->isGranted('ROLE_RESPONSABLE')) {
            return $this->render('dashboard/responsable.html.twig', ['stats' => $stats]);
        }

        if ($this->isGranted('ROLE_AGENT_COURRIER')) {
            return $this->render('dashboard/agent.html.twig', ['stats' => $stats]);
        }

        return $this->render('dashboard/user.html.twig', ['stats' => $stats]);
    }
}
