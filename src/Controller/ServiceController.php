<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use App\Service\JournalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/services')]
#[IsGranted('ROLE_ADMIN')]
class ServiceController extends AbstractController
{
    #[Route('', name: 'app_service_index', methods: ['GET'])]
    public function index(ServiceRepository $repository): Response
    {
        return $this->render('service/index.html.twig', ['services' => $repository->findBy([], ['nom' => 'ASC'])]);
    }

    #[Route('/nouveau', name: 'app_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, JournalService $journal): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($service);
            $em->flush();
            $journal->log($this->getUser(), 'Création service', 'Service', $service->getId());
            $this->addFlash('success', 'Service créé.');

            return $this->redirectToRoute('app_service_index');
        }

        return $this->render('service/form.html.twig', ['form' => $form, 'title' => 'Nouveau service']);
    }

    #[Route('/{id}/modifier', name: 'app_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $em, JournalService $journal): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $journal->log($this->getUser(), 'Modification service', 'Service', $service->getId());
            $this->addFlash('success', 'Service mis à jour.');

            return $this->redirectToRoute('app_service_index');
        }

        return $this->render('service/form.html.twig', ['form' => $form, 'title' => 'Modifier le service', 'service' => $service]);
    }

    #[Route('/{id}/supprimer', name: 'app_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $em, JournalService $journal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), (string) $request->request->get('_token'))) {
            $id = $service->getId();
            $em->remove($service);
            $em->flush();
            $journal->log($this->getUser(), 'Suppression service', 'Service', $id);
            $this->addFlash('success', 'Service supprimé.');
        }

        return $this->redirectToRoute('app_service_index');
    }
}
