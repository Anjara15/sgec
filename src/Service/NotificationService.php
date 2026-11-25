<?php

namespace App\Service;

use App\Entity\Courrier;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly bool $sendEmail = true,
    ) {
    }

    public function notify(User $destinataire, string $typeEvenement, string $message, ?Courrier $courrier = null, bool $byEmail = true): Notification
    {
        $notification = (new Notification())
            ->setDestinataire($destinataire)
            ->setTypeEvenement($typeEvenement)
            ->setMessage($message)
            ->setLienCourrier($courrier);

        $this->em->persist($notification);
        $this->em->flush();

        if ($byEmail && $this->sendEmail) {
            $this->sendMail($destinataire, $typeEvenement, $message, $courrier);
        }

        return $notification;
    }

    private function sendMail(User $destinataire, string $sujet, string $message, ?Courrier $courrier): void
    {
        try {
            $email = (new Email())
                ->from('noreply@sgec.local')
                ->to($destinataire->getEmail())
                ->subject('[SGEC] '.$sujet)
                ->text($message.($courrier ? "\nRéférence: ".$courrier->getReference() : ''));

            if ($courrier) {
                $lien = $this->urlGenerator->generate(
                    'app_courrier_show',
                    ['id' => $courrier->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $email->html(sprintf('<p>%s</p><p><a href="%s">Voir le courrier</a></p>', htmlspecialchars($message), $lien));
            }

            $this->mailer->send($email);
        } catch (\Throwable) {
            // Ne bloque pas le workflow si le mail échoue (DSN null en local).
        }
    }
}
