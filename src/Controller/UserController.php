<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;


use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private UserService $userService;
    private PostService $postService;
    private CommentService $commentService;
    private CacheInterface $pool;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        UserService $userService,
        PostService $postService,
        CommentService $commentService,
        CacheInterface $pool
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->userService = $userService;
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->pool = $pool;
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $fio = $form->get('fio')->getData();
            $password = $form->get('plainPassword')->getData();
            $rights = ['ROLE_USER'];
            if ($form->get('addAdmin')->getData()) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                $rights = ['ROLE_ADMIN'];
            }
            $this->userService->register($email, $fio, $password, $rights);
            return $this->redirectToRoute('user_login');
        }

        return $this->renderForm('user/register.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/profile/{id<(?!0)\b[0-9]+>?}', name: 'show_profile')]
    public function showProfile(?int $id): Response
    {
        if (!is_null($id)) {
            // $user = $this->pool->get("user_$id", function (ItemInterface $item) use ($id) {
               // $item->expiresAfter(60);
                $user = $this->userService->getUserById($id);

               // return $computedValue;
            // });
            /** @var \App\Entity\User $sessionUser */
            if ($sessionUser = $this->getUser()) {
                $canSubscribe = true;
                $isSubscribe = $this->userService->isSubscribe($sessionUser->getId(), $id);
            }
            if (!$user) {
                throw $this->createNotFoundException(sprintf('Пользователь с id = %s не найден', $id));
            }
        } else {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $canSubscribe = $isSubscribe = false;
        }
        $numberOfResults = 5;
        $userId = $user->getId();

        $posts = $this->pool->get(sprintf('posts_by_user_%s', $userId), 
            function (ItemInterface $item) use ($userId, $numberOfResults) {
                $item->expiresAfter(60);
                $computedValue = $this->postService->getPostsByUserId($userId, $numberOfResults);

                return $computedValue;
        });
        $comments = $this->pool->get(sprintf('comments_by_user_%s', $userId), 
            function (ItemInterface $item) use ($userId, $numberOfResults) {
                $item->expiresAfter(60);
                $computedValue = $this->commentService->getCommentsByUserId($userId, $numberOfResults);

                return $computedValue;
        });
        $likedPosts = $this->pool->get(sprintf('liked_posts_by_user_%s', $userId),  
            function (ItemInterface $item) use ($userId, $numberOfResults) {
                $item->expiresAfter(3600);
                $computedValue = $this->postService->getLikedPostsByUserId($userId, $numberOfResults);

                return $computedValue;
        });
        $likedComments = $this->pool->get(sprintf('liked_comments_by_user_%s', $userId), 
            function (ItemInterface $item) use ($userId, $numberOfResults) {
                $item->expiresAfter(3600);
                $computedValue = $this->commentService->getLikedCommentsByUserId($userId, $numberOfResults);

                return $computedValue;
        });

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'can_subscribe' => $canSubscribe,
            'is_subscribe' => $isSubscribe,
            'number_of_results' => $numberOfResults,
            'posts' => $posts,
            'comments' => $comments,
            'likedPosts' => $likedPosts,
            'likedComments' => $likedComments,
        ]);
    }

    #[Route('/update', name: 'update')]
    public function update(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'email' => $user->getEmail(),
            'fio' => $user->getFio()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $this->userService->update($user);
            return $this->redirectToRoute('user_show_profile', ['id' => $user->getId()]);
        }

        return $this->renderForm('user/update.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    #[Route('/profile/subscribe/{id<(?!0)\b[0-9]+>}', name: 'subscribe')]
    public function subscribe(User $user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var \App\Entity\User $user */
        $userSubscribed = $this->getUser();
        if ($this->userService->subscribe($userSubscribed, $user)) {
            $this->addFlash(
                'success',
                'Вы подписаны'
            );
        } else {
            $this->addFlash(
                'success',
                'Подписка отменена'
            );
        }

        return $this->redirectToRoute('user_show_profile', ['id' => $user->getId()]);
    }

    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new TemplatedEmail())
            ->from('prostobloglocal@gmail.com')
            ->to('drotovmihailo@gmail.com')
            ->subject('New Post')
            ->htmlTemplate('emails/toSubscribers.html.twig')
            ->context([
                'user' => $this->getUser(),
                'postId' => 10
            ]);

        $mailer->send($email);

        return $this->render('blog_base.html.twig');
    }

    #[Route('/login', name: 'login')]
    public function login(FormFactoryInterface $formFactory): Response
    {
        if ($error = $this->authenticationUtils->getLastAuthenticationError()) {
            $error = 'Неверная почта или пароль';
        }
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $form = $formFactory->createNamed('', LoginFormType::class, null, [
            'last_username' => $lastUsername
        ]);

        return $this->renderForm('user/login.html.twig', [
            'form' => $form,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout()
    {
        throw $this->createNotFoundException("Don't forget to activate logout");
    }
}