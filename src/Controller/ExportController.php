<?php

namespace App\Controller;

use App\Entity\Courrier;
use App\Repository\CourrierRepository;
use App\Service\DashboardStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/export')]
#[IsGranted('ROLE_USER')]
class ExportController extends AbstractController
{
    #[Route('/courrier/{id}/accuse', name: 'app_export_accuse', methods: ['GET'])]
    public function accuse(Courrier $courrier): Response
    {
        return $this->render('export/accuse.html.twig', ['courrier' => $courrier]);
    }

    #[Route('/courrier/{id}/bordereau', name: 'app_export_bordereau', methods: ['GET'])]
    public function bordereau(Courrier $courrier): Response
    {
        return $this->render('export/bordereau.html.twig', ['courrier' => $courrier]);
    }

    #[Route('/rapport', name: 'app_export_rapport', methods: ['GET'])]
    #[IsGranted('ROLE_RESPONSABLE')]
    public function rapport(DashboardStatsService $stats, CourrierRepository $courriers): Response
    {
        return $this->render('export/rapport.html.twig', [
            'stats' => $stats->getStats(),
            'courriers' => $courriers->findRecents(50),
        ]);
    }

    #[Route('/courriers.csv', name: 'app_export_csv', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT_COURRIER')]
    public function csv(CourrierRepository $repository): Response
    {
        $rows = [['Référence', 'Type', 'Objet', 'Expéditeur', 'Destinataire', 'Statut', 'Priorité', 'Date']];
        foreach ($repository->search([]) as $c) {
            $rows[] = [
                $c->getReference(),
                $c->getType()->label(),
                $c->getObjet(),
                $c->getExpediteur(),
                $c->getDestinataire(),
                $c->getStatut()->label(),
                $c->getPriorite()->label(),
                $c->getDateEnregistrement()->format('Y-m-d H:i'),
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= '"'.implode('","', array_map(static fn ($v) => str_replace('"', '""', (string) $v), $row))."\"\n";
        }

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="courriers.csv"',
        ]);
    }
}
