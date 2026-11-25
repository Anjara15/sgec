<?php

namespace App\Controller;

use App\Repository\JournalActiviteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/historique')]
#[IsGranted('ROLE_ADMIN')]
class JournalController extends AbstractController
{
    #[Route('', name: 'app_journal_index', methods: ['GET'])]
    public function index(JournalActiviteRepository $repository): Response
    {
        return $this->render('journal/index.html.twig', [
            'activites' => $repository->findBy([], ['date' => 'DESC'], 200),
        ]);
    }
}
