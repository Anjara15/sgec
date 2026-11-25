<?php

namespace App\Tests\Unit;

use App\Entity\Courrier;
use App\Entity\Transmission;
use App\Entity\User;
use App\Enum\CourrierStatut;
use App\Enum\TransmissionEtat;
use App\Service\CourrierWorkflowService;
use App\Service\JournalService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourrierWorkflowServiceTest extends TestCase
{
    public function testApprouverDepuisEnValidation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $notifications = $this->createMock(NotificationService::class);
        $journal = $this->createMock(JournalService::class);
        $journal->expects($this->once())->method('log');

        $service = new CourrierWorkflowService($em, $notifications, $journal);

        $auteur = (new User())->setEmail('a@test.local')->setNom('A')->setPrenom('B');
        $createur = (new User())->setEmail('c@test.local')->setNom('C')->setPrenom('D');

        $courrier = (new Courrier())
            ->setReference('ENT-2026-0099')
            ->setObjet('Test')
            ->setExpediteur('X')
            ->setDestinataire('Y')
            ->setCreateur($createur)
            ->setStatut(CourrierStatut::EnValidation);

        $service->approuver($courrier, $auteur, 'OK');

        $this->assertSame(CourrierStatut::Valide, $courrier->getStatut());
        $this->assertCount(1, $courrier->getHistoriquesStatut());
        $this->assertSame('OK', $courrier->getMotifValidation());
    }

    public function testRejetSansMotifEchoue(): void
    {
        $service = new CourrierWorkflowService(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(NotificationService::class),
            $this->createMock(JournalService::class),
        );

        $courrier = (new Courrier())->setStatut(CourrierStatut::EnValidation);

        $this->expectException(BadRequestHttpException::class);
        $service->rejeter($courrier, new User(), '  ');
    }

    public function testMarquerCommeTraiteDepuisRecu(): void
    {
        $journal = $this->createMock(JournalService::class);
        $journal->expects($this->once())->method('log');
        $service = new CourrierWorkflowService(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(NotificationService::class),
            $journal,
        );
        $courrier = (new Courrier())->setStatut(CourrierStatut::Recu);

        $service->traiter($courrier, new User());

        $this->assertSame(CourrierStatut::Traite, $courrier->getStatut());
    }

    public function testTransmissionDepuisStatutInvalideEchoue(): void
    {
        $service = new CourrierWorkflowService(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(NotificationService::class),
            $this->createMock(JournalService::class),
        );
        $courrier = (new Courrier())->setStatut(CourrierStatut::EnValidation);

        $this->expectException(BadRequestHttpException::class);
        $service->transmettre($courrier, new User(), new User());
    }

    public function testReceptionDuneTransmissionDejaRecueEstRefusee(): void
    {
        $service = new CourrierWorkflowService(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(NotificationService::class),
            $this->createMock(JournalService::class),
        );
        $destinataire = new User();
        $courrier = (new Courrier())->setStatut(CourrierStatut::EnTransmission);
        $transmission = (new Transmission())
            ->setDestinataire($destinataire)
            ->setCourrier($courrier)
            ->setEtat(TransmissionEtat::Recu);

        $this->expectException(BadRequestHttpException::class);
        $service->receptionner($transmission, $destinataire);
    }

    public function testReceptionMetAJourTransmissionEtCourrierEnsemble(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');
        $journal = $this->createMock(JournalService::class);
        $journal->expects($this->once())->method('log');
        $service = new CourrierWorkflowService(
            $em,
            $this->createMock(NotificationService::class),
            $journal,
        );
        $destinataire = new User();
        $courrier = (new Courrier())->setStatut(CourrierStatut::EnTransmission);
        $transmission = (new Transmission())
            ->setDestinataire($destinataire)
            ->setCourrier($courrier)
            ->setEtat(TransmissionEtat::Envoye);

        $service->receptionner($transmission, $destinataire);

        $this->assertSame(TransmissionEtat::Recu, $transmission->getEtat());
        $this->assertNotNull($transmission->getDateReception());
        $this->assertSame(CourrierStatut::Recu, $courrier->getStatut());
    }
}
