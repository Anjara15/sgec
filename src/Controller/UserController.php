<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\JournalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $repository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $repository->findBy([], ['dateCreation' => 'DESC']),
        ]);
    }

    #[Route('/nouveau', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        JournalService $journal,
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['require_password' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $em->persist($user);
            $em->flush();
            $journal->log($this->getUser(), 'Création utilisateur', 'User', $user->getId());
            $this->addFlash('success', 'Utilisateur créé.');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/form.html.twig', ['form' => $form, 'title' => 'Nouvel utilisateur']);
    }

    #[Route('/{id}/modifier', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        JournalService $journal,
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['require_password' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $em->flush();
            $journal->log($this->getUser(), 'Modification utilisateur', 'User', $user->getId());
            $this->addFlash('success', 'Utilisateur mis à jour.');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier '.$user->getNomComplet(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em, JournalService $journal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), (string) $request->request->get('_token'))) {
            $id = $user->getId();
            $em->remove($user);
            $em->flush();
            $journal->log($this->getUser(), 'Suppression utilisateur', 'User', $id);
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('app_user_index');
    }
}
