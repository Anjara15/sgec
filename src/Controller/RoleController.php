<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use App\Service\JournalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/roles')]
#[IsGranted('ROLE_ADMIN')]
class RoleController extends AbstractController
{
    #[Route('', name: 'app_role_index', methods: ['GET'])]
    public function index(RoleRepository $repository): Response
    {
        return $this->render('role/index.html.twig', ['roles' => $repository->findAll()]);
    }

    #[Route('/nouveau', name: 'app_role_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, JournalService $journal): Response
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($role);
            $em->flush();
            $journal->log($this->getUser(), 'Création rôle', 'Role', $role->getId());
            $this->addFlash('success', 'Rôle créé.');

            return $this->redirectToRoute('app_role_index');
        }

        return $this->render('role/form.html.twig', ['form' => $form, 'title' => 'Nouveau rôle']);
    }

    #[Route('/{id}/modifier', name: 'app_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Role $role, EntityManagerInterface $em, JournalService $journal): Response
    {
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $journal->log($this->getUser(), 'Modification rôle', 'Role', $role->getId());
            $this->addFlash('success', 'Rôle mis à jour.');

            return $this->redirectToRoute('app_role_index');
        }

        return $this->render('role/form.html.twig', ['form' => $form, 'title' => 'Modifier le rôle', 'role' => $role]);
    }

    #[Route('/{id}/supprimer', name: 'app_role_delete', methods: ['POST'])]
    public function delete(Request $request, Role $role, EntityManagerInterface $em, JournalService $journal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$role->getId(), (string) $request->request->get('_token'))) {
            $id = $role->getId();
            $em->remove($role);
            $em->flush();
            $journal->log($this->getUser(), 'Suppression rôle', 'Role', $id);
            $this->addFlash('success', 'Rôle supprimé.');
        }

        return $this->redirectToRoute('app_role_index');
    }
}
