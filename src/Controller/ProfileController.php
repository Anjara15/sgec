<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Service\JournalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        JournalService $journal,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }

            $em->flush();
            $journal->log($user, 'Modification du profil', 'User', $user->getId());
            $this->addFlash('success', 'Vos informations de profil ont été mises à jour.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form,
            'user' => $user,
            'roleLabel' => $this->isGranted('ROLE_ADMIN') ? 'Administrateur'
                : ($this->isGranted('ROLE_RESPONSABLE') ? 'Responsable de service'
                : ($this->isGranted('ROLE_AGENT_COURRIER') ? 'Agent de courrier' : 'Utilisateur')),
        ]);
    }
}
