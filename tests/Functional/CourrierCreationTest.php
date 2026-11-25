<?php

namespace App\Tests\Functional;

use App\Entity\Courrier;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CourrierCreationTest extends WebTestCase
{
    public function testAdminCanCreateCourrier(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        if ($entityManager->getConnection()->createSchemaManager()->tablesExist(['courrier'])) {
            $schemaTool->dropSchema($metadata);
        }
        $schemaTool->createSchema($metadata);

        $service = (new Service())
            ->setNom('Service de test')
            ->setDescription('Service créé pour le test fonctionnel')
            ->setStatut('actif');
        $user = (new User())
            ->setNom('Test')
            ->setPrenom('Administrateur')
            ->setEmail('admin-test@sgec.local')
            ->setRoles(['ROLE_ADMIN'])
            ->setService($service);
        $user->setPassword($container->get(UserPasswordHasherInterface::class)->hashPassword($user, 'test-password'));

        $entityManager->persist($service);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);

        $client->request('GET', '/admin/courriers');
        $this->assertResponseRedirects('/courriers');

        $crawler = $client->request('GET', '/courriers/nouveau');
        $form = $crawler->selectButton('Enregistrer')->form([
            'courrier[type]' => 'entrant',
            'courrier[objet]' => 'Test de création depuis le formulaire',
            'courrier[description]' => 'Courrier de test fonctionnel',
            'courrier[dateDocument]' => '2026-11-04',
            'courrier[expediteur]' => 'Expéditeur fictif',
            'courrier[destinataire]' => 'Destinataire fictif',
            'courrier[serviceOrigine]' => (string) $service->getId(),
            'courrier[serviceDestinataire]' => (string) $service->getId(),
            'courrier[agentResponsable]' => (string) $user->getId(),
            'courrier[priorite]' => 'normale',
            'courrier[confidentialite]' => 'interne',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects();
        $courrier = $entityManager->getRepository(Courrier::class)->findOneBy([
            'objet' => 'Test de création depuis le formulaire',
        ]);
        $this->assertNotNull($courrier);
        $this->assertMatchesRegularExpression('/^ENT-\d{4}-\d{4}$/', $courrier->getReference());
    }
}
