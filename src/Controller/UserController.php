<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService;
use App\Service\RegistrationService;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private UserService $userService;
    private PostService $postService;
    private CommentService $commentService;
    private EmailVerifier $emailVerifier;
    private RegistrationService $registrationService;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        UserService $userService,
        PostService $postService,
        CommentService $commentService,
        EmailVerifier $emailVerifier,
        RegistrationService $registrationService
    )
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->userService = $userService;
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->emailVerifier = $emailVerifier;
        $this->registrationService = $registrationService;
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
            if ($form->get('addAdmin')->getData())
            {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                $rights = ['ROLE_ADMIN'];
            }
            $this->registrationService->register($email, $fio, $password, $rights);
            return $this->redirectToRoute('user_login');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/profile/{userId<\b[0-9]+>?}', name: 'show_profile')]
    public function showProfile(?int $userId): Response
    {
        if (!empty($userId)) {
            $user = $this->userService->getUserById($userId);
            /** @var \App\Entity\User $sessionUser */
            if ($sessionUser = $this->getUser())
            {
                $canSubscribe = true;
                $isSubscribe = $this->userService->isSubscribe($sessionUser->getId(), $user->getId());
            }
        } else {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $canSubscribe = $isSubscribe = false;
        }
        $numberOfResults = 10;
        $posts = $this->postService->getPostsByUserId($user->getId(), $numberOfResults);
        $likedPosts = $this->postService->getLikedPostsByUserId($user->getId(), $numberOfResults);
        $comments = $this->commentService->getCommentsByUserId($user->getId(), $numberOfResults);
        $likedComments = $this->commentService->getLikedCommentsByUserId($user->getId(), $numberOfResults);

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

    #[Route('/profile/subscribe/{userId<\b[0-9]+>}', name: 'subscribe')]
    public function subscribe(int $userId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userIdWantSubscribe = $user->getid();

        if ($this->userService->subscribe($userIdWantSubscribe, $userId))
        {
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
        return $this->redirectToRoute('user_show_profile', ['userId' => $userId]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'Произошла ошибка при проверке email');

            return $this->redirectToRoute('user_register');
        }

        $this->addFlash('success', 'Ваш email верифицирован. Войдите');
        return $this->redirectToRoute('user_login');
    }

    #[Route('/login', name: 'login')]
    public function login(FormFactoryInterface $formFactory): Response
    {
        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
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
        // controller can be blank: it will never be called!
        throw new \Exception("Don't forget to activate logout in security.yaml");
    }
}