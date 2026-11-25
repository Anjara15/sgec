<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Entity\User;
use App\Enum\CourrierType as CourrierTypeEnum;
use App\Form\CourrierSearchType;
use App\Form\CourrierType;
use App\Form\TransmissionType;
use App\Form\ValidationType;
use App\Repository\CourrierRepository;
use App\Service\CourrierWorkflowService;
use App\Service\JournalService;
use App\Service\PieceJointeUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/courriers')]
#[IsGranted('ROLE_USER')]
class CourrierController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CourrierRepository $courrierRepository,
        private readonly CourrierWorkflowService $workflow,
        private readonly PieceJointeUploader $uploader,
        private readonly JournalService $journal,
    ) {
    }

    #[Route('', name: 'app_courrier_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(CourrierSearchType::class);
        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $criteria = [
                'reference' => $data['reference'] ?? null,
                'objet' => $data['objet'] ?? null,
                'type' => isset($data['type']) && $data['type'] ? $data['type']->value : null,
                'expediteur' => $data['expediteur'] ?? null,
                'destinataire' => $data['destinataire'] ?? null,
                'service' => isset($data['service']) && $data['service'] ? $data['service']->getId() : null,
                'statut' => isset($data['statut']) && $data['statut'] ? $data['statut']->value : null,
                'priorite' => isset($data['priorite']) && $data['priorite'] ? $data['priorite']->value : null,
                'date_debut' => isset($data['date_debut']) && $data['date_debut'] ? $data['date_debut']->format('Y-m-d') : null,
                'date_fin' => isset($data['date_fin']) && $data['date_fin'] ? $data['date_fin']->format('Y-m-d') : null,
            ];
        }

        return $this->render('courrier/index.html.twig', [
            'courriers' => $this->courrierRepository->search($criteria),
            'searchForm' => $form,
        ]);
    }

    #[Route('/archives', name: 'app_courrier_archives', methods: ['GET'])]
    public function archives(Request $request): Response
    {
        $form = $this->createForm(CourrierSearchType::class);
        $form->handleRequest($request);
        $criteria = ['archives' => true];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $criteria = array_merge($criteria, [
                'reference' => $data['reference'] ?? null,
                'objet' => $data['objet'] ?? null,
                'type' => isset($data['type']) && $data['type'] ? $data['type']->value : null,
            ]);
        }

        return $this->render('archive/index.html.twig', [
            'courriers' => $this->courrierRepository->search($criteria, true),
            'searchForm' => $form,
        ]);
    }

    #[Route('/nouveau', name: 'app_courrier_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGENT_COURRIER')]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $courrier = (new Courrier())->setCreateur($user)->setAgentResponsable($user);
        $form = $this->createForm(CourrierType::class, $courrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prefix = match ($courrier->getType()) {
                CourrierTypeEnum::Entrant => 'ENT',
                CourrierTypeEnum::Sortant => 'SORT',
                CourrierTypeEnum::Interne => 'INT',
            };
            $courrier->setReference($this->courrierRepository->generateNextReference($prefix));
            $this->handleUploads($form->get('pieces')->getData(), $courrier, $user);

            $this->em->persist($courrier);
            $this->em->flush();
            $this->journal->log($user, 'Création courrier', 'Courrier', $courrier->getId());
            $this->addFlash('success', 'Courrier enregistré: '.$courrier->getReference());

            return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
        }

        return $this->render('courrier/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'app_courrier_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Courrier $courrier): Response
    {
        $validationForm = $this->createForm(ValidationType::class);
        $transmissionForm = $this->createForm(TransmissionType::class);

        return $this->render('courrier/show.html.twig', [
            'courrier' => $courrier,
            'validationForm' => $validationForm,
            'transmissionForm' => $transmissionForm,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_courrier_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGENT_COURRIER')]
    public function edit(Request $request, Courrier $courrier): Response
    {
        if ($courrier->isArchive()) {
            $this->addFlash('danger', 'Un courrier archivé ne peut pas être modifié.');

            return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(CourrierType::class, $courrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courrier->setDateModification(new \DateTimeImmutable());
            $this->handleUploads($form->get('pieces')->getData(), $courrier, $user);
            $this->em->flush();
            $this->journal->log($user, 'Modification courrier', 'Courrier', $courrier->getId());
            $this->addFlash('success', 'Courrier mis à jour.');

            return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
        }

        return $this->render('courrier/edit.html.twig', ['form' => $form, 'courrier' => $courrier]);
    }

    #[Route('/{id}/soumettre', name: 'app_courrier_soumettre', methods: ['POST'])]
    #[IsGranted('ROLE_AGENT_COURRIER')]
    public function soumettre(Request $request, Courrier $courrier): Response
    {
        $this->denyCsrf($request, 'soumettre'.$courrier->getId());
        /** @var User $user */
        $user = $this->getUser();
        try {
            $this->workflow->soumettreValidation($courrier, $user);
            $this->addFlash('success', 'Courrier soumis à validation.');
        } catch (BadRequestHttpException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
    }

    #[Route('/{id}/valider', name: 'app_courrier_valider', methods: ['POST'])]
    #[IsGranted('ROLE_RESPONSABLE')]
    public function valider(Request $request, Courrier $courrier): Response
    {
        $form = $this->createForm(ValidationType::class);
        $form->handleRequest($request);
        /** @var User $user */
        $user = $this->getUser();
        $action = $request->request->get('action');

        if ($form->isSubmitted() && $form->isValid()) {
            $motif = $form->get('motif')->getData();
            try {
                match ($action) {
                    'approuver' => $this->workflow->approuver($courrier, $user, $motif),
                    'rejeter' => $this->workflow->rejeter($courrier, $user, (string) $motif),
                    'corriger' => $this->workflow->demanderCorrection($courrier, $user, (string) $motif),
                    default => throw new BadRequestHttpException('Action inconnue.'),
                };
                $this->addFlash('success', 'Décision enregistrée.');
            } catch (BadRequestHttpException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        } else {
            $this->addFlash('danger', 'Le formulaire de décision est invalide.');
        }

        return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
    }

    #[Route('/{id}/transmettre', name: 'app_courrier_transmettre', methods: ['POST'])]
    #[IsGranted('ROLE_AGENT_COURRIER')]
    public function transmettre(Request $request, Courrier $courrier): Response
    {
        $form = $this->createForm(TransmissionType::class);
        $form->handleRequest($request);
        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->workflow->transmettre(
                    $courrier,
                    $user,
                    $form->get('destinataire')->getData(),
                    $form->get('commentaire')->getData()
                );
                $this->addFlash('success', 'Transmission enregistrée.');
            } catch (BadRequestHttpException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('danger', 'Le formulaire de transmission est invalide.');
        }

        return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
    }

    #[Route('/{id}/traiter', name: 'app_courrier_traiter', methods: ['POST'])]
    #[IsGranted('ROLE_RESPONSABLE')]
    public function traiter(Request $request, Courrier $courrier): Response
    {
        $this->denyCsrf($request, 'traiter'.$courrier->getId());
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->workflow->traiter($courrier, $user);
            $this->addFlash('success', 'Traitement du courrier terminé.');
        } catch (BadRequestHttpException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
    }

    #[Route('/{id}/archiver', name: 'app_courrier_archiver', methods: ['POST'])]
    #[IsGranted('ROLE_RESPONSABLE')]
    public function archiver(Request $request, Courrier $courrier): Response
    {
        $this->denyCsrf($request, 'archiver'.$courrier->getId());
        /** @var User $user */
        $user = $this->getUser();
        try {
            $this->workflow->archiver($courrier, $user);
            $this->addFlash('success', 'Courrier archivé (conservation logique).');
        } catch (BadRequestHttpException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_courrier_show', ['id' => $courrier->getId()]);
    }

    #[Route('/{id}/imprimer', name: 'app_courrier_print', methods: ['GET'])]
    public function print(Courrier $courrier): Response
    {
        return $this->render('export/fiche_courrier.html.twig', ['courrier' => $courrier]);
    }

    #[Route('/piece/{id}/telecharger', name: 'app_piece_download', methods: ['GET'])]
    public function downloadPiece(\App\Entity\PieceJointe $piece): Response
    {
        $path = $this->uploader->getTargetDirectory().'/'.$piece->getNomStockage();
        if (!is_file($path)) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $piece->getNomOriginal());

        return $response;
    }

    /** @param list<UploadedFile>|null $files */
    private function handleUploads(?array $files, Courrier $courrier, User $user): void
    {
        if (!$files) {
            return;
        }
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $courrier->addPieceJointe($this->uploader->upload($file, $courrier, $user));
            }
        }
    }

    private function denyCsrf(Request $request, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
    }
}
