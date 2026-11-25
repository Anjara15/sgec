<?php

namespace App\Controller;

use App\Entity\ParametreSysteme;
use App\Form\ParametreSystemeType;
use App\Repository\ParametreSystemeRepository;
use App\Service\JournalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/parametres')]
#[IsGranted('ROLE_ADMIN')]
class ParametreController extends AbstractController
{
    #[Route('', name: 'app_parametre_index', methods: ['GET'])]
    public function index(ParametreSystemeRepository $repository): Response
    {
        return $this->render('parametre/index.html.twig', [
            'parametres' => $repository->findBy([], ['cle' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'app_parametre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, JournalService $journal): Response
    {
        $param = new ParametreSysteme();
        $form = $this->createForm(ParametreSystemeType::class, $param);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($param);
            $em->flush();
            $journal->log($this->getUser(), 'Création paramètre', 'ParametreSysteme', $param->getId());
            $this->addFlash('success', 'Paramètre créé.');

            return $this->redirectToRoute('app_parametre_index');
        }

        return $this->render('parametre/form.html.twig', ['form' => $form, 'title' => 'Nouveau paramètre']);
    }

    #[Route('/{id}/modifier', name: 'app_parametre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ParametreSysteme $parametre, EntityManagerInterface $em, JournalService $journal): Response
    {
        $form = $this->createForm(ParametreSystemeType::class, $parametre, ['edit_mode' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $journal->log($this->getUser(), 'Modification paramètre', 'ParametreSysteme', $parametre->getId());
            $this->addFlash('success', 'Paramètre mis à jour.');

            return $this->redirectToRoute('app_parametre_index');
        }

        return $this->render('parametre/form.html.twig', ['form' => $form, 'title' => 'Modifier le paramètre']);
    }
}
