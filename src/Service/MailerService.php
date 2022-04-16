<?php

namespace App\Service;

use App\Entity\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twig\Environment;

class MailerService
{
    private PHPMailer $mailer;

    private Environment $twig;

    private array $errors = [];

    public function __construct(Environment $twig, PHPMailer $phpMailer, string $fromEmail, string $password) {
        $this->twig   = $twig;
        $this->mailer = $phpMailer;
        $this
            ->mailer
            ->isSMTP();
        $this
            ->mailer
            ->Mailer = 'smtp';
        $this
            ->mailer
            ->SMTPDebug = 0;
        $this
            ->mailer
            ->SMTPAuth = true;
        $this  
            ->mailer
            ->SMTPSecure = 'tls';
        $this
            ->mailer
            ->Port = 465;
        $this
            ->mailer
            ->Host = 'ssl://smtp.mail.ru';
        $this
            ->mailer
            ->Username = $fromEmail;
        $this
            ->mailer
            ->Password = $password;
        $this
            ->mailer
            ->setFrom($fromEmail, 'Prosto Blog');
        $this
            ->mailer
            ->addReplyTo($fromEmail, 'Prosto Blog');
    }

    private function sendMail(): bool
    {
        try {
            $this
                ->mailer
                ->send();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }

        return true;
    }

    public function sendMailToVerifyUser(string $toAddress, string $fio, string $url): bool
    {
        $this
            ->mailer
            ->isHTML(true);
        $this
            ->mailer
            ->addAddress($toAddress, $fio);
        $this
            ->mailer
            ->Subject = 'Prosto Blog - verify account';
        $content = $this
            ->twig
            ->render('email/email_confirmation.html.twig', ['url' => $url]);
        $this
            ->mailer
            ->msgHTML($content);

        if (! $this->sendMail()) {
            return false;
        }

        return true;
    }

    public function sendMailToRecoveryPassword(string $toAddress, string $fio, string $url): bool
    {
        $this
            ->mailer
            ->isHTML(true);
        $this
            ->mailer
            ->addAddress($toAddress, $fio);
        $this
            ->mailer
            ->Subject = 'Prosto Blog - recovery password';
        $content = $this
            ->twig
            ->render('email/email_recovery.html.twig', ['url' => $url]);
        $this
            ->mailer
            ->msgHTML($content);

        if (! $this->sendMail()) {
            return false;
        }

        return true;
    }

    public function sendMailsToSubscribers(array $toAddresses, User $user, int $postId): bool
    {
        if (empty($toAddresses)) {
            return false;
        }

        $this
            ->mailer
            ->isHTML(true);
        $this
            ->mailer
            ->Subject = 'Новый Пост - Просто Блог';
        $content = $this
            ->twig
            ->render(
                'email/email_to_subscribers.html.twig', 
                [
                    'user'   => $user,
                    'postId' => $postId,
                ]
            );
        $this
            ->mailer
            ->msgHTML($content);

        foreach ($toAddresses as $address) {
            $this
                ->mailer
                ->addAddress($address['email'], 'Subscriber');
        }

        if (! $this->sendMail()) {
            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}