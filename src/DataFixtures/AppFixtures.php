<?php

namespace App\DataFixtures;

use App\Entity\Courrier;
use App\Entity\ParametreSysteme;
use App\Entity\Role;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\Confidentialite;
use App\Enum\CourrierStatut;
use App\Enum\CourrierType;
use App\Enum\Priorite;
use App\Enum\UserStatut;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $roleAdmin = (new Role())
            ->setNom('Administrateur métier')
            ->setDescription('Accès complet')
            ->setPermissions(['courrier.view', 'courrier.create', 'courrier.edit', 'courrier.validate', 'courrier.transmit', 'courrier.archive', 'user.manage', 'service.manage', 'param.manage', 'export.run']);

        $roleAgent = (new Role())
            ->setNom('Agent courrier métier')
            ->setDescription('Enregistrement et transmission')
            ->setPermissions(['courrier.view', 'courrier.create', 'courrier.edit', 'courrier.transmit', 'export.run']);

        $manager->persist($roleAdmin);
        $manager->persist($roleAgent);

        $dg = (new Service())->setNom('Direction générale')->setDescription('Pilotage')->setStatut('actif');
        $sec = (new Service())->setNom('Secrétariat')->setDescription('Courrier central')->setStatut('actif');
        $rh = (new Service())->setNom('Ressources humaines')->setDescription('RH')->setStatut('actif');
        $manager->persist($dg);
        $manager->persist($sec);
        $manager->persist($rh);

        $admin = (new User())
            ->setNom('Admin')
            ->setPrenom('SGEC')
            ->setEmail('admin@sgec.local')
            ->setRoles(['ROLE_ADMIN'])
            ->setService($dg)
            ->setStatut(UserStatut::Actif)
            ->addCustomRole($roleAdmin);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin123!'));

        $agent = (new User())
            ->setNom('Rakoto')
            ->setPrenom('Hery')
            ->setEmail('agent@sgec.local')
            ->setRoles(['ROLE_AGENT_COURRIER'])
            ->setService($sec)
            ->setStatut(UserStatut::Actif)
            ->addCustomRole($roleAgent);
        $agent->setPassword($this->hasher->hashPassword($agent, 'Agent123!'));

        $responsable = (new User())
            ->setNom('Rasoanaivo')
            ->setPrenom('Mialy')
            ->setEmail('responsable@sgec.local')
            ->setRoles(['ROLE_RESPONSABLE'])
            ->setService($dg)
            ->setStatut(UserStatut::Actif);
        $responsable->setPassword($this->hasher->hashPassword($responsable, 'Resp123!'));

        $user = (new User())
            ->setNom('Andria')
            ->setPrenom('Soa')
            ->setEmail('user@sgec.local')
            ->setRoles(['ROLE_USER'])
            ->setService($rh)
            ->setStatut(UserStatut::Actif);
        $user->setPassword($this->hasher->hashPassword($user, 'User123!'));

        $manager->persist($admin);
        $manager->persist($agent);
        $manager->persist($responsable);
        $manager->persist($user);

        $dg->setResponsable($responsable);
        $sec->setResponsable($agent);

        $courrier = (new Courrier())
            ->setReference('ENT-2026-0001')
            ->setType(CourrierType::Entrant)
            ->setObjet('Demande administrative')
            ->setDescription('Demande de documents officiels')
            ->setExpediteur('Ministère de tutelle')
            ->setDestinataire('Direction générale')
            ->setServiceOrigine($sec)
            ->setServiceDestinataire($dg)
            ->setPriorite(Priorite::Haute)
            ->setConfidentialite(Confidentialite::Interne)
            ->setStatut(CourrierStatut::EnValidation)
            ->setCreateur($agent)
            ->setAgentResponsable($responsable)
            ->setDateDocument(new \DateTimeImmutable('2026-10-28'));

        $manager->persist($courrier);

        $courriersTest = [
            (new Courrier())
                ->setReference('ENT-2026-0002')
                ->setType(CourrierType::Entrant)
                ->setObjet('TEST - Demande de fournitures')
                ->setDescription('Donnée fictive pour tester la saisie et le suivi.')
                ->setExpediteur('Organisme fictif Alpha')
                ->setDestinataire('Direction générale')
                ->setServiceOrigine($sec)
                ->setServiceDestinataire($dg)
                ->setPriorite(Priorite::Normale)
                ->setConfidentialite(Confidentialite::Interne)
                ->setStatut(CourrierStatut::Enregistre)
                ->setCreateur($admin)
                ->setAgentResponsable($admin)
                ->setDateDocument(new \DateTimeImmutable('2026-11-04')),
            (new Courrier())
                ->setReference('SORT-2026-0001')
                ->setType(CourrierType::Sortant)
                ->setObjet('TEST - Réponse à une demande')
                ->setDescription('Donnée fictive pour tester la saisie et le suivi.')
                ->setExpediteur('Direction générale')
                ->setDestinataire('Organisme fictif Beta')
                ->setServiceOrigine($dg)
                ->setServiceDestinataire($sec)
                ->setPriorite(Priorite::Haute)
                ->setConfidentialite(Confidentialite::Interne)
                ->setStatut(CourrierStatut::Enregistre)
                ->setCreateur($admin)
                ->setAgentResponsable($admin)
                ->setDateDocument(new \DateTimeImmutable('2026-11-04')),
            (new Courrier())
                ->setReference('INT-2026-0001')
                ->setType(CourrierType::Interne)
                ->setObjet('TEST - Réunion de coordination')
                ->setDescription('Donnée fictive pour tester la saisie et le suivi.')
                ->setExpediteur('Direction générale')
                ->setDestinataire('Ressources humaines')
                ->setServiceOrigine($dg)
                ->setServiceDestinataire($rh)
                ->setPriorite(Priorite::Normale)
                ->setConfidentialite(Confidentialite::Interne)
                ->setStatut(CourrierStatut::Enregistre)
                ->setCreateur($admin)
                ->setAgentResponsable($admin)
                ->setDateDocument(new \DateTimeImmutable('2026-11-04')),
        ];
        foreach ($courriersTest as $courrierTest) {
            $manager->persist($courrierTest);
        }

        $params = [
            ['app.nom', 'SGEC', 'Nom de l\'application'],
            ['courrier.prefixe_entrant', 'ENT', 'Préfixe référence entrant'],
            ['courrier.prefixe_sortant', 'SORT', 'Préfixe référence sortant'],
            ['courrier.prefixe_interne', 'INT', 'Préfixe référence interne'],
            ['archivage.delai_jours', '365', 'Délai indicatif avant archivage'],
        ];
        foreach ($params as [$cle, $valeur, $desc]) {
            $manager->persist((new ParametreSysteme())->setCle($cle)->setValeur($valeur)->setDescription($desc));
        }

        $manager->flush();
    }
}
