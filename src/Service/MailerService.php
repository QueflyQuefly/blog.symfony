<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerService
{
    private MailerInterface $mailer;
    private $errors = [];

    public function __construct(
        MailerInterface $mailer
    ) {
        $this->mailer = $mailer;
    }

    public function sendMail($email): bool
    {
        try {
           $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->errors[] = $e->getMessage();
            throw new \Exception($e->getMessage());
            return false;
        }
        return true;
    }

    public function sendMailsToSubscribers(array $toAddresses, User $user, int $postId)
    {
        if (empty($toAddresses)) {
            return false;
        }
        $email = (new TemplatedEmail())
            ->from('prostobloglocal@gmail.com')
            ->to('drotovmihailo@gmail.com')
            ->subject('New Post - Prosto Blog')
            ->htmlTemplate('emails/toSubscribers.html.twig')
            ->context([
                'user' => $user,
                'postId' => $postId
            ])
        ;
        foreach ($toAddresses as $address) {
            $email->addTo($address['email']);
        }
        if (!$this->sendMail($email)) {
            return false;
        }
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}