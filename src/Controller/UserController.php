<?php
namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    private $sessionUserId, $isSuperuser;

    public function __construct(SessionInterface $sessionInterface)
    {
        $this->sessionUserId = $sessionInterface->get('user_id', false);
        $this->isSuperuser = $sessionInterface->get('is_superuser', false);
    }

    #[Route('/cabinet', name: 'show_cabinet', methods: ['GET'])]
    public function showCabinet(Request $request): Response
    {
        if (!$this->sessionUserId)
        {
            return $this->redirectToRoute('user_show_login');
        }
        $pageDescription = 'Кабинет - Просто Блог';
        if (!empty($request->query->get('user'))) {
            $sessionUserId = (int) $request->query->get('user');
        }

        return $this->render('blog_message.html.twig', [
            'session_user_id' => $this->sessionUserId,
            'is_superuser' => $this->isSuperuser,
            'description' => $pageDescription
        ]);
    }

    #[Route('/login', name: 'show_login', methods: ['GET'])]
    public function showLogin(): Response
    {
        return $this->render('user/login.html.twig', [
            'session_user_id' => $this->sessionUserId,
            'is_superuser' => $this->isSuperuser
        ]);
    }
    
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UsersRepository $usersRepository, SessionInterface $sessionInterface): Response
    {
        $email = $request->request->get('email');
        $email = trim(strip_tags($email));
        $password = $request->request->get('password');
        $this->sessionUserId = false;
        $this->isSuperuser = false;

        if ($request->get('variable_of_captcha') == $_SESSION['variable_of_captcha'])
        {
            $sessionUser = $usersRepository->findOneByEmail($email);
            if ($sessionUser && password_verify($password, $sessionUser->getPassword()))
            {
                $this->sessionUserId = $sessionUser->getId();
                if ($sessionUser->getRights() === 'superuser')
                {
                    $this->isSuperuser = true;
                }
                $sessionInterface->set('user_id', $this->sessionUserId);
                $sessionInterface->set('is_superuser', $this->isSuperuser);
                $this->addFlash(
                    'success',
                    'Вы вошли в аккаунт'
                );
                return $this->redirectToRoute('post_main');
            } else {
                $this->addFlash(
                    'error',
                    'Неверный логин или пароль'
                );
            }
        } else {
            $this->addFlash(
                'error',
                'Неверно введено кодовое число'
            );
        }
        return $this->redirectToRoute('user_show_login');
    }
    
    #[Route('/reg', name: 'show_reg', methods: ['GET'])]
    public function showReg(): Response
    {
        return $this->render('user/reg.html.twig', [
            'session_user_id' => $this->sessionUserId,
            'is_superuser' => $this->isSuperuser
        ]);
    }

    #[Route('/reg', name: 'reg', methods: ['POST'])]
    public function reg(Request $request, ManagerRegistry $doctrine, UsersRepository $usersRepository, SessionInterface $sessionInterface): Response
    {
        $email = $request->request->get('regemail');
        $email = trim(strip_tags($email));
        $fio = $request->request->get('regfio');
        $fio = trim(strip_tags($fio));
        $password = $request->request->get('regpassword');
        $password = password_hash($password, PASSWORD_BCRYPT);
        $rights = 'user';

        if ($request->get('add_admin'))
        {
            $rights = 'superuser';
        }

        if ($request->get('variable_of_captcha') == $_SESSION['variable_of_captcha'])
        {
            if (!$usersRepository->findOneByEmail($email))
            {
                $entityManager = $doctrine->getManager();
                $user = new Users();
                $user->setEmail($email);
                $user->setFio($fio);
                $user->setPassWord($password);
                $user->setDateTime(time());
                $user->setRights($rights);
                // tell Doctrine you want to (eventually) save the Users (no queries yet)
                $entityManager->persist($user);
                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();

                $sessionUser = $usersRepository->findOneByEmail($email);
                $sessionUserId = $sessionUser->getId();
                $isSuperuser = false;
                if ($sessionUser->getRights() === 'superuser')
                {
                    $isSuperuser = true;
                }
                $sessionInterface->set('user_id', $sessionUserId);
                $sessionInterface->set('is_superuser', $isSuperuser);
                $this->addFlash(
                    'success',
                    'Выполнен вход в аккаунт'
                );
                return $this->redirectToRoute('post_main');
            } else {
                $this->addFlash(
                    'error',
                    'Пользователь с таким email уже зарегистрирован'
                );
            }
        } else {
            $this->addFlash(
                'error',
                'Неверно введено кодовое число'
            );
        }
        return $this->redirectToRoute('user_show_reg');
    }
    /* private function isUser(Request $request)
    {
        if ($request->cookies->get('user_id'))
        {
            return true;
        }
        return false;
    } */

    #[Route('/exit', name: 'exit', methods: ['POST'])]
    public function exitUser(SessionInterface $sessionInterface) {
        $sessionInterface->set('user_id', 0);
        $sessionInterface->set('is_superuser', 0);
        $this->addFlash(
            'success',
            'Вы вышли из аккаунта'
        );
        return $this->redirectToRoute('post_main');
    }
}