<?php

namespace App\Controller;

/* use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService; */
use App\Service\MailerService;
/* use App\Service\RedisCacheService;
use Symfony\Component\HttpFoundation\Request; */
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;

#[Route('/test', name: 'test_')]
class TestController extends AbstractController
{
    /* private RedisCacheService $cacheService;

    private UserService $userService;

    private PostService $postService;

    private CommentService $commentService; */

    private MailerService $mailerService;

    private string $env;

    public function __construct(
        /* RedisCacheService $cacheService,
        UserService       $userService,
        PostService       $postService,
        CommentService    $commentService, */
        MailerService     $mailerService,
        KernelInterface   $kernel
    ) {
        /* $this->cacheService = $cacheService;
        $this->userService    = $userService;
        $this->postService    = $postService;
        $this->commentService = $commentService; */
        $this->mailerService  = $mailerService;
        $this->env            = $kernel->getEnvironment();
    }

    #[Route('', name: 'main')]
    public function main(): Response
    {
        if ($this->env !== 'dev') {
            throw $this->createNotFoundException('Something went wrong');
        }

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this
            ->mailerService
            ->sendMailsToSubscribers(
                [
                    0 => ['email' => 'drotovmihailo@gmail.com']
                ], 
                $this->getUser(), 
                1
            );
        
        return $this->render('blog_base.html.twig');
    }

    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $email = (new Email())
            ->from('blogsymfony@mail.ru')
            ->replyTo('blogsymfony@mail.ru')
            ->to('drotovmihailo@gmail.com')
            ->priority(Email::PRIORITY_HIGHEST)
            ->subject('New Post')
            ->text('fffffffff');

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            echo $e->getMessage();
        }

        return $this->render('blog_base.html.twig');
    }
}