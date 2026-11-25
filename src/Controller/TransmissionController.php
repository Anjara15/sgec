<?php

namespace App\Controller;

use App\Entity\Transmission;
use App\Entity\User;
use App\Repository\TransmissionRepository;
use App\Service\CourrierWorkflowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/transmissions')]
#[IsGranted('ROLE_USER')]
class TransmissionController extends AbstractController
{
    #[Route('', name: 'app_transmission_index', methods: ['GET'])]
    public function index(TransmissionRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('transmission/index.html.twig', [
            'transmissions' => $repository->findPourUtilisateur($user),
        ]);
    }

    #[Route('/{id}/receptionner', name: 'app_transmission_receptionner', methods: ['POST'])]
    public function receptionner(Request $request, Transmission $transmission, CourrierWorkflowService $workflow): Response
    {
        if (!$this->isCsrfTokenValid('reception'.$transmission->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();
        try {
            $workflow->receptionner($transmission, $user, $request->request->get('commentaire'));
            $this->addFlash('success', 'Réception confirmée.');
        } catch (BadRequestHttpException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_courrier_show', ['id' => $transmission->getCourrier()->getId()]);
    }
}
