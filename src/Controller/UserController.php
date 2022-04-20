<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService;
use App\Service\RedisCacheService;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use App\Form\RecoveryFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    public const MIN_TIME_FOR_BAN = 86400; // 24 hours in seconds (one day)
    
    public const MAX_TIME_FOR_BAN = 259200; // 3 days in seconds

    private AuthenticationUtils $authenticationUtils;

    private UserService $userService;

    private PostService $postService;

    private CommentService $commentService;

    private RedisCacheService $cacheService;

    private FormFactoryInterface $formFactory;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        AuthenticationUtils   $authenticationUtils,
        UserService           $userService,
        PostService           $postService,
        CommentService        $commentService,
        RedisCacheService     $cacheService,
        FormFactoryInterface  $formFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->userService         = $userService;
        $this->postService         = $postService;
        $this->commentService      = $commentService;
        $this->cacheService        = $cacheService;
        $this->formFactory         = $formFactory;
        $this->tokenStorage        = $tokenStorage;
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form
                ->get('email')
                ->getData();
            $fio = $form
                ->get('fio')
                ->getData();
            $password = $form
                ->get('plainPassword')
                ->getData();
            $addModerator = $form
                ->get('addModerator')
                ->getData();
            $addAdmin = $form
                ->get('addAdmin')
                ->getData();
            $rights = ['ROLE_USER'];

            if (! empty($addModerator)) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                $rights = ['ROLE_MODERATOR'];
            }

            if (! empty($addAdmin)) {
                $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
                $rights = ['ROLE_ADMIN'];
            }

            $this
                ->userService
                ->register($email, $fio, $password, $rights);

            return $this->redirectToRoute('user_login');
        }

        return $this->renderForm('user/user_register.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/profile/{id<(?!0)\b[0-9]+>?}', name: 'show_profile')]
    public function showProfile(?int $id): Response
    {
        $canSubscribe = false;
        $isSubscribe  = false;

        if (! empty($id)) {
            $user = $this
                ->cacheService
                ->get(
                    sprintf('user_%s', $id), 
                    60, 
                    User::class, 
                    function () use ($id) {
                        return $this->userService->getUserById($id);
                    }
                );

            if (empty($user)) {
                throw $this->createNotFoundException(sprintf('Пользователь с id = %s не найден', $id));
            }

            /** @var \App\Entity\User $sessionUser */
            $sessionUser = $this->getUser();

            if (! empty($sessionUser) && $sessionUser->getId() !== $id) {
                $canSubscribe = true;
                $isSubscribe  = $this
                    ->userService
                    ->isSubscribe($sessionUser->getId(), $id);
            } 
        } else {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
        }

        $numberOfResults = 5;
        $userId          = $user->getId();

        $posts = $this
            ->cacheService
            ->get(
                sprintf('posts_user_%s', $userId), 
                10, 
                sprintf('%s[]', Post::class),
                function () use ($userId, $numberOfResults) {
                    return $this
                        ->postService
                        ->getPostsByUserId($userId, $numberOfResults);
                }
            );
        $comments = $this
            ->cacheService
            ->get(
                sprintf('comments_user_%s', $userId), 
                10, 
                sprintf('%s[]', Comment::class),
                function () use ($userId, $numberOfResults) {
                    return $this
                        ->commentService
                        ->getCommentsByUserId($userId, $numberOfResults);
                }
            );
        $likedPosts = $this
            ->cacheService
            ->get(
                sprintf('liked_posts_user_%s', $userId), 
                10, 
                sprintf('%s[]', Post::class),
                function () use ($userId, $numberOfResults) {
                    return $this
                        ->postService
                        ->getLikedPostsByUserId($userId, $numberOfResults);
                }
            );
        $likedComments = $this
            ->cacheService
            ->get(
                sprintf('liked_comments_user_%s', $userId), 
                10, 
                sprintf('%s[]', Comment::class),
                function () use ($userId, $numberOfResults) {
                    return $this
                        ->commentService
                        ->getLikedCommentsByUserId($userId, $numberOfResults);
                }
            );

        return $this->render('user/user_profile.html.twig', [
            'user'              => $user,
            'can_subscribe'     => $canSubscribe,
            'is_subscribe'      => $isSubscribe,
            'number_of_results' => $numberOfResults,
            'posts'             => $posts,
            'comments'          => $comments,
            'likedPosts'        => $likedPosts,
            'likedComments'     => $likedComments,
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
            'fio'   => $user->getFio()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user     = $form->getData();
            $password = $form->get('plainPassword')->getData();
            $this
                ->userService
                ->update($user, $password);

            return $this->redirectToRoute('user_show_profile', [
                'id' => $user->getId()
            ]);
        }

        return $this->renderForm('user/user_update.html.twig', [
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

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();

        if (! empty($error)) {
            $error = 'Неверная почта или пароль';
        }

        $form = $this->formFactory->createNamed('', LoginFormType::class);

        return $this->renderForm('user/user_login.html.twig', [
            'form'  => $form,
            'error' => $error
        ]);
    }

    #[Route('/recovery', name: 'show_recovery')]
    public function showRecovery(Request $request): Response
    {
        $error = '';
        $form  = $this->createForm(RecoveryFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form
                ->get('email')
                ->getData();
            $fio = $form
                ->get('fio')
                ->getData();
            $user = $this
                ->userService
                ->isUserExists($email, $fio);

            if (! empty($user)) {
                if ($this->userService->sendMailToRecoveryPassword($user)) {
                    $description = 'Ожидайте письмо по введенному вами e-mail адресу';
                } else {
                    $description = 'Произошла ошибка при отправке письма. Возможно введен неверный e-mail адрес';
                }

                return $this->render('blog_message.html.twig', [
                    'description' => $description
                ]);
            }

            $error = 'Такого пользователя не существует';
        }

        return $this->renderForm('user/user_recovery.html.twig', [
            'form'  => $form,
            'error' => $error
        ]);
    }

    #[Route('/recovery/{secretCipher}', name: 'recovery')]
    public function recovery(string $secretCipher, Request $request): Response
    {
        $user = $this->userService->getUserBySecretCipher($secretCipher);

        if (empty($user)) {
            throw $this->createNotFoundException('Something went wrong');
        }

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'email' => $user->getEmail(),
            'fio'   => $user->getFio()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user     = $form->getData();
            $password = $form
                ->get('plainPassword')
                ->getData();
            $this
                ->userService
                ->update($user, $password);

            return $this->redirectToRoute('user_login');
        }

        return $this->renderForm('user/user_update.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/verify/{secretCipher?}', name: 'verify')]
    public function verify(?string $secretCipher): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user->isVerified()) {
            throw $this->createNotFoundException();
        }

        if (empty($secretCipher)) {
            if ($this->userService->sendMailToVerifyUser($user)) {
                $this->addFlash(
                    'success',
                    'Ожидайте письмо по вашему e-mail адресу'
                );
            } else {
                $this->addFlash(
                    'error',
                    'Произошла ошибка при отправке письма. Возможно e-mail адрес не существует'
                );
            }
        } else {
            $user = $this->userService->getUserBySecretCipher($secretCipher);

            if (empty($user)) {
                throw $this->createNotFoundException('Something went wrong');
            }

            $user->setIsVerified(true);
            $this
                ->userService
                ->update($user);
        }

        return $this->redirectToRoute('user_show_profile');
    }

    #[Route('/ban/{id<(?!0)\b[0-9]+>}', name: 'ban')]
    public function ban(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->render('blog_message.html.twig', [
                'description' => 'Невозможно забанить администратора. <br><br> Обратитесь к суперадмину за помощью'
            ]);
        }

        if ($user->getIsBanned() === 0) {
            $user->setIsBanned(time() + $this::MIN_TIME_FOR_BAN);
            $this->addFlash(
                'success',
                sprintf('Пользователь №%s забанен на %s часа', $user->getId(), $this::MIN_TIME_FOR_BAN / 3600)
            );
        } else {
            $user->setIsBanned(time() + $this::MAX_TIME_FOR_BAN);
            $this->addFlash(
                'success',
                sprintf('Пользователь №%s забанен на %s часа', $user->getId(), $this::MAX_TIME_FOR_BAN / 3600)
            );
        }

        $this
            ->userService
            ->update($user);

        return $this->redirectToRoute('user_show_profile', [
            'id' => $user->getId()
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout()
    {
        throw $this->createNotFoundException('Don\'t forget to activate logout');
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function delete(User $userForDelete, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $userForDeleteEmail = $userForDelete->getEmail();
            $this
                ->userService
                ->delete($userForDelete);
            $this->addFlash(
                'success',
                sprintf('Пользователь №%s удален', $userForDeleteEmail)
            );

            return $this->redirectToRoute('admin_show_users');
        } elseif ($user->getId() === $userForDelete->getId()) {
            $this
                ->userService
                ->delete($userForDelete);
            $request
                ->getSession()
                ->invalidate();
            $this
                ->tokenStorage
                ->setToken();
            
            return $this->redirectToRoute('user_login');
        } else {
            throw $this->createNotFoundException('Something went wrong');
        }
    }
}