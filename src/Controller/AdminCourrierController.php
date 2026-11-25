<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/courriers')]
#[IsGranted('ROLE_ADMIN')]
class AdminCourrierController extends AbstractController
{
    #[Route('', name: 'app_admin_courriers', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_courrier_index');
    }
}
