<?php

namespace App\Controller\Api;

use App\Entity\Courrier;
use App\Repository\CourrierRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
class ApiCourrierController extends AbstractController
{
    #[Route('/courriers', name: 'api_courrier_index', methods: ['GET'])]
    public function index(Request $request, CourrierRepository $repository): JsonResponse
    {
        $criteria = [
            'reference' => $request->query->get('reference'),
            'objet' => $request->query->get('objet'),
            'type' => $request->query->get('type'),
            'statut' => $request->query->get('statut'),
            'priorite' => $request->query->get('priorite'),
        ];

        $data = array_map(fn (Courrier $c) => $this->serializeCourrier($c), $repository->search($criteria));

        return $this->json(['data' => $data, 'count' => \count($data)]);
    }

    #[Route('/courriers/{id}', name: 'api_courrier_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Courrier $courrier): JsonResponse
    {
        return $this->json(['data' => $this->serializeCourrier($courrier, true)]);
    }

    #[Route('/services', name: 'api_service_index', methods: ['GET'])]
    public function services(ServiceRepository $repository): JsonResponse
    {
        $data = array_map(static fn ($s) => [
            'id' => $s->getId(),
            'nom' => $s->getNom(),
            'statut' => $s->getStatut(),
        ], $repository->findActifs());

        return $this->json(['data' => $data]);
    }

    #[Route('/users', name: 'api_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function users(UserRepository $repository): JsonResponse
    {
        $data = array_map(static fn ($u) => [
            'id' => $u->getId(),
            'nom' => $u->getNom(),
            'prenom' => $u->getPrenom(),
            'email' => $u->getEmail(),
            'roles' => $u->getRoles(),
            'statut' => $u->getStatut()->value,
        ], $repository->findAll());

        return $this->json(['data' => $data]);
    }

    /** @return array<string, mixed> */
    private function serializeCourrier(Courrier $c, bool $detail = false): array
    {
        $data = [
            'id' => $c->getId(),
            'reference' => $c->getReference(),
            'type' => $c->getType()->value,
            'objet' => $c->getObjet(),
            'expediteur' => $c->getExpediteur(),
            'destinataire' => $c->getDestinataire(),
            'priorite' => $c->getPriorite()->value,
            'confidentialite' => $c->getConfidentialite()->value,
            'statut' => $c->getStatut()->value,
            'date_enregistrement' => $c->getDateEnregistrement()->format(DATE_ATOM),
        ];

        if ($detail) {
            $data['description'] = $c->getDescription();
            $data['service_origine'] = $c->getServiceOrigine()?->getNom();
            $data['service_destinataire'] = $c->getServiceDestinataire()?->getNom();
            $data['pieces_jointes'] = array_map(static fn ($p) => [
                'id' => $p->getId(),
                'nom' => $p->getNomOriginal(),
                'taille' => $p->getTaille(),
                'mime' => $p->getTypeMime(),
            ], $c->getPiecesJointes()->toArray());
            $data['transmissions'] = array_map(static fn ($t) => [
                'id' => $t->getId(),
                'etat' => $t->getEtat()->value,
                'date' => $t->getDateTransmission()->format(DATE_ATOM),
                'expediteur' => $t->getExpediteur()?->getNomComplet(),
                'destinataire' => $t->getDestinataire()?->getNomComplet(),
            ], $c->getTransmissions()->toArray());
        }

        return $data;
    }
}
