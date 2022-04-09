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
    private $errors = [];

    public function __construct(Environment $twig) {
        $this->twig = $twig;
        $this->mailer = new PHPMailer();
        $this->mailer->isSMTP();
        $this->mailer->Mailer = 'smtp';
        $this->mailer->SMTPDebug = 0;
        $this->mailer->SMTPAuth = true;
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port = 465;
        $this->mailer->Host = 'ssl://smtp.mail.ru';
        $this->mailer->Username = 'blogsymfony@mail.ru';
        $this->mailer->Password = 'w5rs8YNVmk2NBLTqHyWi';
        $this->mailer->setFrom('blogsymfony@mail.ru', 'Prosto Blog');
        $this->mailer->addReplyTo('blogsymfony@mail.ru', 'Prosto Blog');
    }

    public function sendMail(): bool
    {
        try {
           $this->mailer->send();
        } catch (Exception $e) {
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
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Новый Пост - Просто Блог';
        $content = $this->twig->render('emails/toSubscribers.html.twig', [
            'user'   => $user,
            'postId' => $postId
        ]);
        $this->mailer->msgHTML($content);

        foreach ($toAddresses as $address) {
            $this->mailer->addAddress($address['email'], 'Dear Subscriber');
        }

        if (!$this->sendMail()) {
            return false;
        }
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}