<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class mailerMailJetService
{
    private MailerInterface $mailer;
    private string $adminEmail;

    public function __construct(
        #[Autowire('%swapifyAdminEmail%')] string $adminEmail,
        MailerInterface $mailer
    ) {
        $this->adminEmail = $adminEmail;
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from($this->adminEmail) 
            ->to($to)
            ->subject($subject)
            ->text($content)
            ->html("<p>{$content}</p>");

        $this->mailer->send($email);
    }
}
