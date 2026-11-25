<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('', name: 'app_notification_index', methods: ['GET'])]
    public function index(NotificationRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('notification/index.html.twig', [
            'notifications' => $repository->findBy(['destinataire' => $user], ['dateCreation' => 'DESC'], 100),
            'unreadCount' => $repository->countUnreadForUser($user),
        ]);
    }

    #[Route('/{id}/lire', name: 'app_notification_read', methods: ['GET'])]
    public function read(Notification $notification, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($notification->getDestinataire()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $notification->marquerLue();
        $em->flush();

        if ($notification->getLienCourrier()) {
            return $this->redirectToRoute('app_courrier_show', ['id' => $notification->getLienCourrier()->getId()]);
        }

        return $this->redirectToRoute('app_notification_index');
    }
}
