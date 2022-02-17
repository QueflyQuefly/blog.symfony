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

/**
 * @Route("/user", name="user_", methods={"GET"})
 */
class UserController extends AbstractController
{
    /**
     * @Route("/cabinet", name="show_cabinet", methods={"GET"})
     */
    public function showCabinet(Request $request, SessionInterface $sessionInterface): Response
    {
        if (!$sessionInterface->get('user_id', false))
        {
            return $this->showLogin($sessionInterface);
        }
        $pageDescription = 'Кабинет - Просто Блог';
        if (!empty($request->query->get('user'))) {
            $sessionUserId = (int) $request->query->get('user');
        } else {
            $sessionUserId = (int) $sessionInterface->get('user_id');
        }
        $isSuperuser = true;
        return $this->render('blog_message.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'description' => $pageDescription
        ]);
    }

    /**
     * @Route("/login", name="show_login", methods={"GET"})
     */
    public function showLogin(SessionInterface $sessionInterface): Response
    {
        $sessionUserId = $sessionInterface->get('user_id');
        $isSuperuser = $sessionInterface->get('is_superuser');
        $intMin = random_int(100, 900);
        $intMax =  $intMin + 2;
        $sessionInterface->set('variable_of_captcha', $intMin + 1);

        return $this->render('user/login.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'int_min' => $intMin,
            'int_max' => $intMax
        ]);
    }
    
    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request, UsersRepository $usersRepository, SessionInterface $sessionInterface): Response
    {
        $email = $request->get('email');
        $email = trim(strip_tags($email));
        $password = $request->get('password');
        $sessionUserId = false;
        $isSuperuser = false;
        $description = "Неверно введено кодовое число";

        if ($request->get('variable_of_captcha') == $sessionInterface->get('variable_of_captcha'))
        {
            $description = "Неверный логин или пароль";
            $sessionUser = $usersRepository->findOneByEmail($email);
            if ($sessionUser && password_verify($password, $sessionUser->getPassword()))
            {
                $fio = $sessionUser->getFio();
                $sessionUserId = $sessionUser->getId();
                if ($sessionUser->getRights() === 'superuser')
                {
                    $isSuperuser = true;
                }
                $sessionInterface->set('user_id', $sessionUserId);
                $sessionInterface->set('is_superuser', $isSuperuser);
                $description = "Вы вошли как $fio";
            }
        }
        return $this->render('blog_message.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'description' => $description
        ]);
    }
    
    /**
     * @Route("/reg", name="show_reg", methods={"GET"})
     */
    public function showReg(SessionInterface $sessionInterface): Response
    {
        $sessionUserId = $sessionInterface->get('user_id');
        $isSuperuser = $sessionInterface->get('is_superuser');
        $intMin = random_int(100, 900);
        $intMax = $intMin + 2;
        $sessionInterface->set('variable_of_captcha', $intMin + 1);

        return $this->render('user/reg.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'int_min' => $intMin,
            'int_max' => $intMax
        ]);
    }

    /**
     * @Route("/reg", name="reg", methods={"POST"})
     */
    public function reg(Request $request, ManagerRegistry $doctrine, UsersRepository $usersRepository, SessionInterface $sessionInterface): Response
    {
        $email = $request->get('regemail');
        $email = trim(strip_tags($email));
        $fio = $request->get('regfio');
        $fio = trim(strip_tags($fio));
        $password = $request->get('regpassword');
        $password = password_hash($password, PASSWORD_BCRYPT);
        $rights = 'user';

        if ($request->get('add_admin'))
        {
            $rights = 'superuser';
        }
        $description = 'Неверно введено кодовое число';

        if ($request->get('variable_of_captcha') == $sessionInterface->get('variable_of_captcha'))
        {
            $description = 'Пользователь с таким email уже зарегистрирован';

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
                $description = 'Пользователь создан';
            }
        }

        $sessionUserId = $sessionInterface->get('user_id');
        $isSuperuser = $sessionInterface->get('is_superuser');
        return $this->render('blog_message.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'description' => $description
        ]);
    }
    /* private function isUser(Request $request)
    {
        if ($request->cookies->get('user_id'))
        {
            return true;
        }
        return false;
    } */

    /**
     * @Route("/exit", name="exit", methods={"POST"})
     */
    public function exitUser(SessionInterface $sessionInterface) {
        $sessionInterface->set('user_id', 0);
        $sessionInterface->set('is_superuser', 0);
        return $this->render('blog_message.html.twig', [
            'session_user_id' => false,
            'is_superuser' => false,
            'description' => 'Вы успешно вышли из аккаунта'
        ]);
    }
}